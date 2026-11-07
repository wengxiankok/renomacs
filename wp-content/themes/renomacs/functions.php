<?php

/* Register Custom Menus */
function register_my_menus() {
    register_nav_menus(
        array(
            'primary-menu' => __( 'Primary Menu' ),
            'footer-menu'  => __( 'Footer Menu' )
        )
    );
}
add_action( 'init', 'register_my_menus' );

add_action('rest_api_init', function() {
  register_rest_route('custom/v1', '/contact', [
    'methods'             => 'POST',
    'callback'            => 'rm_forward_contact_to_apps_script',
    'permission_callback' => '__return_true', // public — we'll protect otherwise below
  ]);
});

// Return only our public contract; never expose upstream payloads or credentials.
function rm_contact_error($status, $message) {
    return new WP_REST_Response(['result' => 'error', 'message' => $message], $status);
}

function rm_contact_url_allowed($url, $host) {
    $parts = is_string($url) ? wp_parse_url($url) : false;
    return $parts && ($parts['scheme'] ?? '') === 'https'
        && ($parts['host'] ?? '') === $host
        && !isset($parts['user']) && !isset($parts['pass']) && !isset($parts['port']);
}

function rm_forward_contact_to_apps_script(WP_REST_Request $request) {
    if (strlen((string) $request->get_body()) > 20000) {
        return rm_contact_error(413, 'Your enquiry is too long. Please shorten it.');
    }
    $input = $request->get_json_params();
    if ($input === null) { $input = $request->get_body_params(); }
    if (!is_array($input)) { return rm_contact_error(400, 'Invalid enquiry.'); }
    $data = [];
    $limits = ['fullname' => 150, 'company' => 200, 'email' => 254, 'contact' => 30,
        'address' => 1000, 'budget' => 100, 'startdate' => 10, 'description' => 5000,
        'projecttype' => 30, 'consent' => 3];
    foreach ($limits as $field => $limit) {
        $value = $input[$field] ?? '';
        if (!is_string($value) || strlen($value) > $limit) {
            return rm_contact_error(400, 'Please check the enquiry fields and their length.');
        }
        $data[$field] = $field === 'description' ? sanitize_textarea_field($value) : sanitize_text_field($value);
    }
    $digits = preg_replace('/\D/', '', $data['contact']);
    if ($data['fullname'] === '' || !is_email($data['email'])
        || !preg_match('/^\+?[0-9 ()-]+$/', $data['contact'])
        || strlen($digits) < 6 || strlen($digits) > 15 || $data['consent'] !== 'Yes') {
        return rm_contact_error(400, 'Please provide your name, a valid email and phone number, and consent.');
    }
    if (!in_array($data['projecttype'], ['', 'Residential', 'Commercial', 'Industrial'], true)) {
        return rm_contact_error(400, 'Please select a valid project type.');
    }
    if ($data['startdate'] !== '') {
        $date = DateTime::createFromFormat('!Y-m-d', $data['startdate']);
        if (!$date || $date->format('Y-m-d') !== $data['startdate']) {
            return rm_contact_error(400, 'Please select a valid start date.');
        }
    }
    $services = $input['services'] ?? [];
    $allowed = ['Partition Installation', 'Electrical Wiring', 'Water Piping', 'Tiling Works', 'Painting', 'Plaster Ceiling'];
    if (!is_array($services) || count($services) > count($allowed)) {
        return rm_contact_error(400, 'Please select valid services.');
    }
    foreach ($services as $service) {
        if (!is_string($service) || !in_array($service, $allowed, true)) {
            return rm_contact_error(400, 'Please select valid services.');
        }
    }
    $data['services'] = array_values(array_unique($services));
    if (!defined('RM_SECRET') || !is_string(RM_SECRET) || RM_SECRET === ''
        || !defined('APPS_SCRIPT_URL') || !rm_contact_url_allowed(APPS_SCRIPT_URL, 'script.google.com')
        || !preg_match('~^/macros/s/[^/]+/exec$~', wp_parse_url(APPS_SCRIPT_URL, PHP_URL_PATH) ?? '')) {
        return rm_contact_error(503, 'Enquiries are temporarily unavailable. Please contact us directly.');
    }
    // Best-effort abuse control, not an atomic quota or delivery deduplication.
    // Trust only REMOTE_ADDR; hosting proxy configuration must be reviewed before release.
    $key = 'rm_contact_' . hash_hmac('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown', wp_salt('auth'));
    $attempts = (int) get_transient($key);
    if ($attempts >= 5) { return rm_contact_error(429, 'Please wait before sending another enquiry.'); }
    set_transient($key, $attempts + 1, 10 * MINUTE_IN_SECONDS);
    $data['secret'] = RM_SECRET;
    $options = ['timeout' => 20, 'sslverify' => true, 'redirection' => 0, 'limit_response_size' => 65536];
    $response = wp_safe_remote_post(APPS_SCRIPT_URL, array_merge($options, ['body' => $data]));
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 302) {
        $location = wp_remote_retrieve_header($response, 'location');
        if (!rm_contact_url_allowed($location, 'script.googleusercontent.com')) {
            return rm_contact_error(502, 'We could not confirm delivery. Please contact us directly before retrying.');
        }
        // Content Service redirects to a one-time read URL. Never send the secret on this GET.
        $response = wp_safe_remote_get($location, $options);
    }
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) < 200
        || wp_remote_retrieve_response_code($response) >= 300) {
        return rm_contact_error(502, 'We could not confirm delivery. Please contact us directly before retrying.');
    }
    $decoded = json_decode(wp_remote_retrieve_body($response), true);
    if (!is_array($decoded) || ($decoded['result'] ?? '') !== 'success') {
        return rm_contact_error(502, 'We could not confirm delivery. Please contact us directly before retrying.');
    }
    return new WP_REST_Response(['result' => 'success'], 200);
}
