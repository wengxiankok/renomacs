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

function rm_forward_contact_to_apps_script( WP_REST_Request $request ) {
  // Basic server-side validation (adjust fields as needed)
  $data = $request->get_json_params();
  if (empty($data)) {
    $data = $request->get_params();
  }
  
  // Add the secret server-side (never exposed to client)
  $data['secret'] = defined('RM_SECRET') ? RM_SECRET : '';

  // Validate required fields server-side (prevent spam)
  $required = ['fullname', 'email', 'contact'];
  foreach ($required as $field) {
    if (empty($data[$field])) {
      return rest_ensure_response([
        'result' => 'error',
        'message' => "Missing required field: $field"
      ]);
    }
  }

  // Forward to Apps Script
  $apps_url = 'https://script.google.com/macros/s/AKfycbxJwrTCZ4ojG2Fg8IsYgt0MeFWafErBd-oPmFq90yIIp6UYzUp_PMm-2-YWDyUk-3GHcg/exec';

  error_log('wp json encode data sent to apps script: '. wp_json_encode($data));
  error_log('data sent to appls script: '. print_r($data, true));

  $args = [
    'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/json'],
    'body'    => wp_json_encode($data),
    'timeout' => 30,
    'redirection' => 5,
    'sslverify' => false,
  ];

  error_log('raw wp remote post args: ' . print_r($args, true));
  error_log('Sending POST to: ' . $apps_url);

  $resp = wp_remote_post( $apps_url, $args );

  if ( is_wp_error($resp) ) {
    return rest_ensure_response([ 'result' => 'error', 'message' => $resp->get_error_message() ]);
  }

  $code = wp_remote_retrieve_response_code($resp);
  $headers = wp_remote_retrieve_headers($resp);
  if (in_array($code, [301, 302])) {
    $headers = wp_remote_retrieve_headers($resp);
    if (!empty($headers['location'])) {
      $redirected_url = $headers['location'];
      error_log('Following redirect to: ' . $redirected_url);
      $resp = wp_remote_post($redirected_url, $args);
    }
  }

  $body = wp_remote_retrieve_body($resp);
  error_log('Apps Script raw response: ' . $body);

  $decoded = json_decode($body, true);
  error_log('Apps Script decoded: ' . $decoded);

  // Optionally return the Apps Script response (or translate)
  return rest_ensure_response( $decoded ?: [ 'result' => 'error', 'message' => 'No response from Apps Script' ] );
}
?>