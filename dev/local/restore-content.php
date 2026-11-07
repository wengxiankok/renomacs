<?php
// Import the reviewed, allowlisted manifest mounted at /import/content.json.
// Raw production exports stay outside Git. Never execute this on production.
if (!defined('RM_LOCAL_PREVIEW') || RM_LOCAL_PREVIEW !== true || wp_get_environment_type() !== 'local') {
    WP_CLI::error('Local preview only.');
}
$rows = json_decode(file_get_contents('/import/content.json'), true, 512, JSON_THROW_ON_ERROR);
$map = [];
$rewrite = function ($value) use (&$rewrite) {
    if (is_array($value)) { return array_map($rewrite, $value); }
    if (!is_string($value)) { return $value; }
    return str_replace(['https://renomacs.my', 'http://renomacs.my', rawurlencode('https://renomacs.my')],
        [home_url(), home_url(), rawurlencode(home_url())], $value);
};
foreach ($rows as $row) {
    if (!in_array($row['type'], ['page', 'attachment', 'lazyblocks'], true)) { continue; }
    $existing = get_posts(['post_type' => $row['type'], 'post_status' => 'any', 'meta_key' => '_rm_source_id', 'meta_value' => $row['id'], 'numberposts' => 1]);
    $id = $existing ? $existing[0]->ID : 0;
    if (!$id && $row['type'] === 'page') {
        $fixture = get_page_by_path($row['slug']);
        if ($fixture && get_post_meta($fixture->ID, '_rm_local_fixture', true)) { $id = $fixture->ID; }
        elseif ($fixture) { WP_CLI::error('Existing non-fixture page would conflict: ' . $row['slug']); }
    }
    // Imported PHP/JS snippets must never execute; theme files render these blocks.
    foreach ($row['meta'] as $key => $value) {
        if (preg_match('/^lazyblocks_code_.*(?:html|css|js)$/', $key) && trim($value) !== '') {
            WP_CLI::error('Review block code before import: ' . $row['slug']);
        }
    }
    if (preg_match('/<script\b|<iframe\b|\bon\w+\s*=/i', $row['content'])) {
        WP_CLI::error('Review executable content before import: ' . $row['slug']);
    }
    $post = ['ID' => $id, 'post_type' => $row['type'], 'post_status' => $row['type'] === 'attachment' ? 'inherit' : 'publish',
        'post_title' => html_entity_decode($row['title'], ENT_QUOTES, 'UTF-8'), 'post_name' => $row['slug'],
        'post_content' => $rewrite($row['content']), 'post_author' => 1, 'comment_status' => 'closed', 'ping_status' => 'closed'];
    if ($row['type'] === 'attachment') {
        $post['post_mime_type'] = wp_check_filetype($row['url'])['type'] ?: 'application/octet-stream';
        $post['guid'] = $rewrite($row['url']);
    }
    $id = wp_insert_post(wp_slash($post), true);
    if (is_wp_error($id)) { WP_CLI::error($id->get_error_message()); }
    $map[$row['id']] = $id;
    update_post_meta($id, '_rm_source_id', $row['id']);
    delete_post_meta($id, '_rm_local_fixture');
    foreach ($row['meta'] as $key => $value) {
        if (!preg_match('/^(?:_wp_page_template$|_wp_attached_file$|_wp_attachment_metadata$|_wp_attachment_image_alt$|lazyblocks_)/', $key)) { continue; }
        // Reject serialized objects; the reviewed metadata consists of scalar/array values.
        $decoded = is_serialized($value) ? unserialize($value, ['allowed_classes' => false]) : $value;
        update_post_meta($id, $key, wp_slash($rewrite($decoded)));
    }
}
// Lazy Blocks stores image objects inside URL-encoded JSON attributes. URLs alone
// are insufficient: templates also query WordPress metadata by attachment ID.
$remap_images = function ($value) use (&$remap_images, $map) {
    if (is_array($value)) {
        if (isset($value['id'], $value['url']) && str_contains((string) $value['url'], '/wp-content/uploads/') && isset($map[$value['id']])) {
            $value['id'] = $map[$value['id']];
        }
        return array_map($remap_images, $value);
    }
    if (is_string($value) && preg_match('/^%(?:7B|5B)/i', $value)) {
        $decoded = json_decode(rawurldecode($value), true);
        if (is_array($decoded)) { return rawurlencode(wp_json_encode($remap_images($decoded))); }
    }
    return $value;
};
$remap_blocks = function ($blocks) use (&$remap_blocks, $remap_images) {
    foreach ($blocks as &$block) {
        $block['attrs'] = $remap_images($block['attrs']);
        $block['innerBlocks'] = $remap_blocks($block['innerBlocks']);
    }
    return $blocks;
};
foreach ($rows as $row) {
    if ($row['type'] !== 'page') { continue; }
    $content = serialize_blocks($remap_blocks(parse_blocks($rewrite($row['content']))));
    $updated = wp_update_post(wp_slash(['ID' => $map[$row['id']], 'post_content' => $content]), true);
    if (is_wp_error($updated)) { WP_CLI::error($updated->get_error_message()); }
}
$menus = [];
foreach ($rows as $row) {
    if ($row['type'] !== 'nav_menu_item') { continue; }
    foreach ($row['menus'] as $slug) {
        if (!isset($menus[$slug])) {
            $menu = wp_get_nav_menu_object('Recovered ' . $slug);
            $menus[$slug] = $menu ? $menu->term_id : wp_create_nav_menu('Recovered ' . $slug);
        }
        $meta = $row['meta'];
        $existing = get_posts(['post_type' => 'nav_menu_item', 'meta_key' => '_rm_source_id', 'meta_value' => $row['id'], 'numberposts' => 1]);
        $id = wp_update_nav_menu_item($menus[$slug], $existing ? $existing[0]->ID : 0, [
            'menu-item-title' => html_entity_decode($row['title'], ENT_QUOTES, 'UTF-8'),
            'menu-item-type' => $meta['_menu_item_type'], 'menu-item-object' => $meta['_menu_item_object'],
            'menu-item-object-id' => $map[$meta['_menu_item_object_id']] ?? 0,
            'menu-item-url' => $rewrite($meta['_menu_item_url'] ?? ''),
            'menu-item-position' => (int) $row['order'], 'menu-item-status' => 'publish',
        ]);
        if (is_wp_error($id)) { WP_CLI::error($id->get_error_message()); }
        update_post_meta($id, '_rm_source_id', $row['id']);
    }
}
foreach ($menus as $slug => $id) { WP_CLI::log('Recovered menu: ' . $slug . ' ID ' . $id); }
$locations = [];
foreach ($menus as $slug => $id) {
    if (str_contains($slug, 'primary')) { $locations['primary-menu'] = $id; }
    if (str_contains($slug, 'footer')) { $locations['footer-menu'] = $id; }
}
set_theme_mod('nav_menu_locations', $locations);
update_option('page_on_front', $map[2]);
update_option('show_on_front', 'page');
update_option('wp_page_for_privacy_policy', $map[3]);
update_option('blog_public', '0');
update_option('rm_local_content_imported', '1');
update_option('rm_local_seed_complete', '1');
// Retire only the untouched stock sample; keep it recoverable in drafts.
$sample = get_page_by_path('sample-page');
if ($sample && $sample->post_title === 'Sample Page') { wp_update_post(['ID' => $sample->ID, 'post_status' => 'draft']); }
flush_rewrite_rules();
WP_CLI::success('Recovered ' . count($map) . ' content records. Local HTTP/mail guards remain active.');
