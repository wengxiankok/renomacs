<?php
// Integration checks against the local WordPress runtime, not production.
if (!defined('RM_LOCAL_PREVIEW') || RM_LOCAL_PREVIEW !== true || wp_get_environment_type() !== 'local') {
    WP_CLI::error('These checks must only run in the Docker local preview.');
}

function rm_local_check($condition, $message) {
    if (!$condition) { WP_CLI::error($message); }
    WP_CLI::log('PASS: ' . $message);
}

rm_local_check(get_stylesheet() === 'renomacs', 'RenoMacs theme is active');
$contact = get_page_by_path('contact-us');
rm_local_check($contact && get_page_template_slug($contact->ID) === 'contact-form.php', 'Contact page uses the enquiry template');
rm_local_check(get_option('blog_public') === '0', 'Local preview discourages indexing');

// Exercise both outcomes without forwarding, storing, or logging an enquiry.
$original_mode = getenv('RM_LOCAL_CONTACT_RESULT');
try {
    foreach (['success' => 200, 'error' => 503] as $mode => $status) {
        putenv('RM_LOCAL_CONTACT_RESULT=' . $mode);
        $request = new WP_REST_Request('POST', '/custom/v1/contact');
        $request->set_body_params(['fullname' => 'Local QA', 'email' => 'qa@example.invalid', 'contact' => '0000000000', 'consent' => 'Yes']);
        $response = rest_do_request($request);
        $data = $response->get_data();
        rm_local_check($response->get_status() === $status && ($data['mock'] ?? false) === true && ($data['result'] ?? '') === $mode, 'Enquiry ' . $mode . ' response is simulated');
    }
} finally {
    putenv($original_mode === false ? 'RM_LOCAL_CONTACT_RESULT' : 'RM_LOCAL_CONTACT_RESULT=' . $original_mode);
}

$blocked = wp_remote_post('https://example.invalid/enquiry-test');
rm_local_check(is_wp_error($blocked) && $blocked->get_error_code() === 'rm_local_http_blocked', 'Outbound WordPress HTTP is blocked before network access');
rm_local_check(wp_mail('qa@example.invalid', 'Local QA', 'Must not be delivered') === false, 'WordPress mail delivery is blocked');
WP_CLI::success('Local preview smoke checks passed. These do not validate the production enquiry handler.');
