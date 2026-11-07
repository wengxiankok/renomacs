<?php
// Exercise the real callback, bypassing only the local REST simulator.
if (!defined('RM_LOCAL_PREVIEW') || RM_LOCAL_PREVIEW !== true || wp_get_environment_type() !== 'local') {
    WP_CLI::error('Local preview only.');
}
if (defined('RM_SECRET') || defined('APPS_SCRIPT_URL')) { WP_CLI::error('Unexpected upstream configuration.'); }
$calls = [];
$responses = [];
$intercept = function ($pre, $args, $url) use (&$calls, &$responses) {
    $calls[] = ['url' => $url, 'args' => $args];
    return array_shift($responses) ?? new WP_Error('test_blocked', 'No network permitted');
};
add_filter('pre_http_request', $intercept, PHP_INT_MAX, 3);
$valid = ['fullname' => 'Local QA', 'email' => 'qa@example.invalid', 'contact' => '+60 12-345 6789', 'consent' => 'Yes'];
$invoke = function ($data) {
    $request = new WP_REST_Request('POST', '/custom/v1/contact');
    $request->set_body_params($data);
    return rm_forward_contact_to_apps_script($request);
};
$count = 0;
$check = function ($ok, $label) use (&$count) {
    if (!$ok) { throw new RuntimeException($label); }
    $count++;
    WP_CLI::log('PASS: ' . $label);
};
$reply = function ($code, $body, $headers = []) {
    return ['response' => ['code' => $code], 'body' => $body, 'headers' => $headers];
};
$original_ip = $_SERVER['REMOTE_ADDR'] ?? null;
$_SERVER['REMOTE_ADDR'] = '192.0.2.201';
$key = 'rm_contact_' . hash_hmac('sha256', $_SERVER['REMOTE_ADDR'], wp_salt('auth'));
try {
    $check($invoke($valid)->get_status() === 503 && !$calls, 'Missing configuration fails closed');
    define('RM_SECRET', 'local-test-only');
    define('APPS_SCRIPT_URL', 'https://script.google.com/macros/s/local-test-only/exec');
    foreach ([['fullname' => ''], ['email' => 'bad'], ['contact' => 'abc'], ['consent' => 'No'],
        ['fullname' => ['injected']], ['description' => str_repeat('x', 5001)],
        ['services' => ['Unknown']], ['services' => [['nested']]], ['projecttype' => 'Other'],
        ['startdate' => '2026-02-30']] as $invalid) {
        $check($invoke(array_merge($valid, $invalid))->get_status() === 400 && !$calls, 'Invalid input rejected before forwarding');
    }
    $cases = [
        'direct success' => [[$reply(200, '{"result":"success","private":"hidden"}')], 200],
        'redirect success' => [[$reply(302, '', ['location' => 'https://script.googleusercontent.com/macros/echo?test=1']), $reply(200, '{"result":"success"}')], 200],
        'foreign redirect' => [[$reply(302, '', ['location' => 'https://example.invalid/'])], 502],
        'transport failure' => [[new WP_Error('test', 'private detail')], 502],
        'redirect transport failure' => [[$reply(302, '', ['location' => 'https://script.googleusercontent.com/macros/echo']), new WP_Error('test', 'private detail')], 502],
        'invalid JSON' => [[$reply(200, '<html>Error</html>')], 502],
        'upstream rejection' => [[$reply(200, '{"result":"error"}')], 502],
        'HTTP failure overrides success body' => [[$reply(500, '{"result":"success"}')], 502],
    ];
    foreach ($cases as $label => [$queue, $status]) {
        delete_transient($key);
        $calls = [];
        $responses = $queue;
        $result = $invoke(array_merge($valid, ['secret' => 'untrusted', 'unknown' => 'discard']));
        $check($result->get_status() === $status, $label);
        $check($calls[0]['args']['sslverify'] === true && $calls[0]['args']['redirection'] === 0
            && $calls[0]['args']['body']['secret'] === RM_SECRET && !isset($calls[0]['args']['body']['unknown']), 'TLS and server-only allowlisted payload');
        if (count($calls) === 2) { $check(empty($calls[1]['args']['body']), 'Redirect GET contains no enquiry or secret'); }
        if ($status === 200) { $check($result->get_data() === ['result' => 'success'], 'Only public success contract returned'); }
    }
    set_transient($key, 5, 600);
    $calls = [];
    $check($invoke($valid)->get_status() === 429 && !$calls, 'Rate limit stops forwarding');
    foreach (['http://script.google.com/', 'https://script.google.com.evil.invalid/', 'https://user@script.google.com/', 'https://script.google.com:8443/'] as $url) {
        $check(!rm_contact_url_allowed($url, 'script.google.com'), 'Unsafe upstream URL rejected');
    }
    WP_CLI::success($count . ' real-handler checks passed; all upstream HTTP intercepted.');
} finally {
    delete_transient($key);
    remove_filter('pre_http_request', $intercept, PHP_INT_MAX);
    if ($original_ip === null) { unset($_SERVER['REMOTE_ADDR']); } else { $_SERVER['REMOTE_ADDR'] = $original_ip; }
}
