<?php
if (!defined('RM_LOCAL_PREVIEW') || RM_LOCAL_PREVIEW !== true || wp_get_environment_type() !== 'local') {
    WP_CLI::error('Local preview only.');
}
$failures = [];
$check = function ($ok, $message) use (&$failures) {
    if (!$ok) { $failures[] = $message; WP_CLI::warning($message); }
    else { WP_CLI::log('PASS: ' . $message); }
};
$check(is_plugin_active('lazy-blocks/lazy-blocks.php'), 'Lazy Blocks is active');
foreach (['home', 'contact-us', 'privacy-policy', 'terms-condition'] as $slug) {
    $page = get_page_by_path($slug);
    $check($page && $page->post_status === 'publish' && get_post_meta($page->ID, '_rm_source_id', true), 'Recovered page: ' . $slug);
}
$home = get_page_by_path('home');
$check($home && (int) get_option('page_on_front') === $home->ID, 'Recovered homepage is assigned');
$locations = get_theme_mod('nav_menu_locations', []);
foreach (['primary-menu', 'footer-menu'] as $location) {
    $check(!empty($locations[$location]) && count(wp_get_nav_menu_items($locations[$location]) ?: []) > 0, 'Recovered menu: ' . $location);
}
$html = apply_filters('the_content', $home->post_content);
foreach (['about-us', 'why-renomacs', 'our-services', 'service-areas'] as $anchor) {
    $check(str_contains($html, 'id="' . $anchor . '"'), 'Homepage section renders: ' . $anchor);
}
$check(!str_contains($html, 'https://renomacs.my/wp-content/uploads/'), 'Homepage media references are local');
$uploads = wp_upload_dir()['basedir'];
$missing = 0; $invalid = 0; $total = 0; $missing_sizes = 0; $sizes = 0;
foreach (get_posts(['post_type' => 'attachment', 'post_status' => 'inherit', 'numberposts' => -1, 'meta_key' => '_rm_source_id']) as $attachment) {
    $total++;
    $file = get_attached_file($attachment->ID);
    if (!$file || !is_file($file)) { $missing++; continue; }
    if (preg_match('/<!doctype html|<html/i', file_get_contents($file, false, null, 0, 512))) { $invalid++; }
    elseif (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'svg' && !@getimagesize($file)) { $invalid++; }
    $metadata = wp_get_attachment_metadata($attachment->ID);
    foreach (($metadata['sizes'] ?? []) as $size) {
        $sizes++;
        if (!is_file(dirname($file) . '/' . $size['file'])) { $missing_sizes++; }
    }
}
WP_CLI::log('Media records: ' . $total . '; missing files: ' . $missing . '; HTML impostors: ' . $invalid);
$check($missing === 0 && $invalid === 0 && $total > 0, 'Recovered media files are present and are not HTML responses');
WP_CLI::log('Registered image sizes: ' . $sizes . '; missing: ' . $missing_sizes);
$check($missing_sizes === 0, 'Registered image variants exist');
preg_match_all('~' . preg_quote(home_url('/wp-content/uploads/'), '~') . '[^\s"\'<>\\)]+~', $html, $matches);
$rendered_missing = 0;
foreach (array_unique($matches[0]) as $url) {
    $relative = rawurldecode(substr(wp_parse_url(html_entity_decode($url), PHP_URL_PATH), strlen('/wp-content/uploads/')));
    if (!is_file($uploads . '/' . $relative)) { $rendered_missing++; }
}
WP_CLI::log('Homepage media URLs: ' . count(array_unique($matches[0])) . '; missing: ' . $rendered_missing);
$check($rendered_missing === 0 && !empty($matches[0]), 'Rendered homepage media files exist');
if ($failures) { WP_CLI::error(count($failures) . ' recovery checks failed. The copy is not complete.'); }
WP_CLI::success('Recovered content checks passed. Visual and interaction tests are still required.');
