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

// function rm_forward_contact_to_apps_script( WP_REST_Request $request ) {
//   // Basic server-side validation (adjust fields as needed)
//   $data = $request->get_json_params();
//   if (empty($data)) {
//     $data = $request->get_params();
//   }
  
//   // Add the secret server-side (never exposed to client)
//   $data['secret'] = defined('RM_SECRET') ? RM_SECRET : '';

//   // Validate required fields server-side (prevent spam)
//   $required = ['fullname', 'email', 'contact'];
//   foreach ($required as $field) {
//     if (empty($data[$field])) {
//       return rest_ensure_response([
//         'result' => 'error',
//         'message' => "Missing required field: $field"
//       ]);
//     }
//   }

//   // Forward to Apps Script
//   $apps_url = APPS_SCRIPT_URL;

//   error_log('wp json encode data sent to apps script: '. wp_json_encode($data));
//   error_log('data sent to appls script: '. print_r($data, true));

//   // $args = [
//   //   'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/json'],
//   //   'body'    => wp_json_encode($data),
//   //   'timeout' => 30,
//   //   'redirection' => 5,
//   //   'sslverify' => false,
//   // ];
//   $args = [
//     'body'    => $data, // form-encoded
//     'timeout' => 30,
//     'redirection' => 5,
//     'sslverify' => false,
//   ];

//   error_log('raw wp remote post args: ' . print_r($args, true));
//   error_log('Sending POST to: ' . $apps_url);

//   $args['body'] = wp_json_encode($data);
//   $args['headers']['Content-Type'] = 'application/json';
//   $resp = wp_remote_post( $apps_url, $args );

//   if ( is_wp_error($resp) ) {
//     return rest_ensure_response([ 'result' => 'error', 'message' => $resp->get_error_message() ]);
//   }

//   $code = wp_remote_retrieve_response_code($resp);
//   $headers = wp_remote_retrieve_headers($resp);
//   if (in_array($code, [301, 302])) {
//     $headers = wp_remote_retrieve_headers($resp);
//     if (!empty($headers['location'])) {
//       $redirected_url = $headers['location'];
//       error_log('Following redirect to: ' . $redirected_url);
//       $resp = wp_remote_post($redirected_url, $args);
//     }
//   }

//   $body = wp_remote_retrieve_body($resp);
//   error_log('Apps Script raw response: ' . $body);

// //   $decoded = json_decode($body, true);
// //   error_log('Apps Script decoded: ' . print_r($decoded, true));

// //   // Optionally return the Apps Script response (or translate)
// //   return rest_ensure_response( $decoded ?: [ 'result' => 'error', 'message' => 'No response from Apps Script' ] );
// // }
//   $decoded = json_decode($body, true);
//   error_log('Apps Script decoded: ' . print_r($decoded, true));

//   // If Apps Script returned *HTML*, treat it as success if HTTP code is 200
//   if (!$decoded && $code === 200 && !empty($body)) {
//       return rest_ensure_response([
//           'result' => 'success',
//           'message' => 'Apps Script returned non-JSON but HTTP 200'
//       ]);
//   }

//   return rest_ensure_response($decoded);
// }
function rm_forward_contact_to_apps_script( WP_REST_Request $request ) {

    // Parse input
    $data = $request->get_json_params();
    if (empty($data)) {
        $data = $request->get_params();
    }

    // Secret (server)
    $data['secret'] = defined('RM_SECRET') ? RM_SECRET : '';

    // Validation
    foreach (['fullname','email','contact'] as $field) {
        if (empty($data[$field])) {
            return rest_ensure_response([
                'result' => 'error',
                'message' => "Missing required field: $field"
            ]);
        }
    }

    error_log("WP → Apps Script FORM DATA: " . print_r($data, true));

    // FIRST request: POST (Apps Script will return 302)
    $response = wp_remote_post(APPS_SCRIPT_URL, [
        'body'       => $data,          // ← form-encoded
        'timeout'    => 20,
        'sslverify'  => false,
        'redirection'=> 0               // ← DO NOT follow automatically
    ]);

    if (is_wp_error($response)) {
        return rest_ensure_response([
            'result' => 'error',
            'message' => $response->get_error_message()
        ]);
    }

    $code    = wp_remote_retrieve_response_code($response);
    $headers = wp_remote_retrieve_headers($response);

    // Handle redirect manually
    if ($code === 302 && !empty($headers['location'])) {

        $redirect_url = $headers['location'];
        error_log("Following redirect → $redirect_url");

        // SECOND request: GET the redirected JSON
        $response = wp_remote_get($redirect_url, [
            'timeout'   => 20,
            'sslverify' => false
        ]);
    }

    // Extract final body
    $body = wp_remote_retrieve_body($response);
    error_log("Apps Script FINAL BODY: $body");

    // Parse JSON
    $decoded = json_decode($body, true);

    if (!$decoded) {
        return rest_ensure_response([
            'result' => 'error',
            'message' => 'Invalid JSON from Apps Script'
        ]);
    }

    // SUCCESS
    return rest_ensure_response($decoded);
}
?>