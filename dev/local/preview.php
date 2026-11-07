<?php
/**
 * Plugin Name: RenoMacs local preview only
 * Description: Blocks WordPress HTTP/mail delivery and simulates the enquiry endpoint.
 * Mounted by Compose; never copy this file into a production installation.
 */

if (!defined('RM_LOCAL_PREVIEW') || RM_LOCAL_PREVIEW !== true || wp_get_environment_type() !== 'local') {
    return;
}

// No real enquiry handler, Apps Script request, or local enquiry storage is used.
add_filter('rest_pre_dispatch', function ($result, $server, $request) {
    if ($request->get_route() !== '/custom/v1/contact' || $request->get_method() !== 'POST') {
        return $result;
    }

    if (getenv('RM_LOCAL_CONTACT_RESULT') !== 'success') {
        return new WP_REST_Response([
            'result' => 'error',
            'message' => 'Simulated local failure. Nothing was sent or saved.',
            'mock' => true,
        ], 503);
    }

    return new WP_REST_Response([
        'result' => 'success',
        'message' => 'Simulated local success. Nothing was sent or saved.',
        'mock' => true,
    ], 200);
}, 10, 3);

// Also protect against accidental delivery by other WordPress code in this sandbox.
// This intentionally blocks plugin/update downloads through WordPress too.
add_filter('pre_http_request', function () {
    return new WP_Error('rm_local_http_blocked', 'Outbound WordPress HTTP requests are disabled in local preview.');
}, PHP_INT_MAX);

add_filter('pre_wp_mail', function () {
    return false;
}, PHP_INT_MAX);

add_filter('pre_option_blog_public', function () {
    return '0';
});

add_action('wp_footer', function () {
    $mode = getenv('RM_LOCAL_CONTACT_RESULT') === 'success' ? 'success' : 'error';
    ?>
    <aside role="status" style="position:fixed;bottom:0;left:0;right:0;z-index:10000;padding:6px 12px;background:#fff3cd;color:#332701;text-align:center;font:14px/1.4 sans-serif">
        Local preview &mdash; enquiries simulate <?php echo esc_html($mode); ?>; nothing is sent or saved.
    </aside>
    <script>
        // The production thank-you copy must not imply delivery in a simulated preview.
        const localThankYou = document.querySelector('.thank-you-message');
        if (localThankYou) {
            localThankYou.textContent = 'Local simulation complete. No enquiry was sent or saved.';
        }
    </script>
    <?php
}, 100);
