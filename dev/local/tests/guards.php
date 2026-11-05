<?php
/**
 * Standalone guard checks. Run each scenario in a separate PHP process:
 * php dev/local/tests/guards.php local|disabled|production
 * Small WordPress API doubles test callback behavior; smoke.php tests the real runtime.
 */

$scenario = $argv[1] ?? 'local';
if (!in_array($scenario, ['local', 'disabled', 'production'], true)) {
    throw new RuntimeException('Unknown test scenario.');
}
define('RM_LOCAL_PREVIEW', $scenario !== 'disabled');
$hooks = [];

function wp_get_environment_type() {
    global $scenario;
    return $scenario === 'production' ? 'production' : 'local';
}
function add_filter($name, $callback, $priority = 10, $accepted_args = 1) {
    global $hooks;
    $hooks[$name] = $callback;
}
function add_action($name, $callback, $priority = 10, $accepted_args = 1) {
    add_filter($name, $callback, $priority, $accepted_args);
}
class WP_REST_Response {
    public function __construct(public $data, public $status) {}
}
class WP_Error {
    public function __construct(public $code, public $message) {}
}
function check($condition, $message) {
    if (!$condition) { throw new RuntimeException($message); }
    echo 'PASS: ' . $message . PHP_EOL;
}

require dirname(__DIR__) . '/preview.php';
if ($scenario !== 'local') {
    check($hooks === [], 'No simulator or guards are registered in ' . $scenario . ' mode');
    exit(0);
}

$request = new class {
    public $route = '/custom/v1/contact';
    public $method = 'POST';
    public function get_route() { return $this->route; }
    public function get_method() { return $this->method; }
};
$dispatch = $hooks['rest_pre_dispatch'];
foreach (['success' => 200, 'error' => 503, 'unexpected-setting' => 503, '' => 503] as $mode => $expected_status) {
    putenv('RM_LOCAL_CONTACT_RESULT=' . $mode);
    $response = $dispatch(null, null, $request);
    check($response->status === $expected_status && $response->data['mock'] === true, 'Mock status for ' . ($mode ?: 'missing setting'));
}
$sentinel = new stdClass();
$request->method = 'GET';
check($dispatch($sentinel, null, $request) === $sentinel, 'Non-POST request passes through unchanged');
$request->method = 'POST';
$request->route = '/wp/v2/pages';
check($dispatch($sentinel, null, $request) === $sentinel, 'Unrelated route passes through unchanged');
check($hooks['pre_http_request']()->code === 'rm_local_http_blocked', 'WordPress HTTP is blocked');
check($hooks['pre_wp_mail']() === false, 'WordPress mail is blocked');
check($hooks['pre_option_blog_public']() === '0', 'Local indexing is discouraged');
