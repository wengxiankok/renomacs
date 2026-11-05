<?php
// Run through WP-CLI after installing an empty local WordPress database.
if (!defined('RM_LOCAL_PREVIEW') || RM_LOCAL_PREVIEW !== true || wp_get_environment_type() !== 'local') {
    WP_CLI::error('Sample content may only be created in the Docker local preview.');
}

if (get_option('rm_local_seed_complete')) {
    WP_CLI::success('Sample content already exists; existing edits were preserved.');
    return;
}

// Do not seed over an imported or manually populated site.
$default_privacy_id = (int) get_option('wp_page_for_privacy_policy');
foreach (get_posts(['post_type' => 'page', 'post_status' => 'any', 'numberposts' => -1]) as $page) {
    $default_privacy_draft = (int) $page->ID === $default_privacy_id && $page->post_name === 'privacy-policy' && $page->post_status === 'draft';
    if ($page->post_name !== 'sample-page' && !$default_privacy_draft && !get_post_meta($page->ID, '_rm_local_fixture', true)) {
        WP_CLI::error('Existing pages detected. Seed only a fresh local database; existing content was preserved.');
    }
}

$fixtures = [
    'home' => ['Local preview', 'tnc-policy.php', '<p>This is sample content for local development, not a copy of the live homepage.</p><p>The database content, uploaded photos, and Lazy Blocks definitions must be recovered to reproduce the live site.</p><p><a href="' . esc_url(home_url('/contact-us/')) . '">Open the enquiry form simulator</a></p>'],
    'contact-us' => ['Contact us', 'contact-form.php', '<h1>Contact us</h1><p>Use invented details only. Enquiries here are simulated, not delivered or stored.</p>'],
    'terms-condition' => ['Terms & Condition', 'tnc-policy.php', '<p>Local placeholder. The actual policy content is not included in this repository.</p>'],
    'privacy-policy' => ['Privacy Policy', 'tnc-policy.php', '<p>Local placeholder. The actual policy content is not included in this repository.</p>'],
];

$ids = [];
foreach ($fixtures as $slug => [$title, $template, $content]) {
    $existing = get_page_by_path($slug);
    if ($existing && get_post_meta($existing->ID, '_rm_local_fixture', true)) {
        $ids[$slug] = $existing->ID;
        continue;
    }
    $id = wp_insert_post([
        // Reuse WordPress's default privacy-policy draft on a fresh install.
        'ID' => $existing ? $existing->ID : 0,
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_name' => $slug,
        'post_title' => $title,
        'post_content' => $content,
        'meta_input' => ['_wp_page_template' => $template, '_rm_local_fixture' => '1'],
    ], true);
    if (is_wp_error($id)) {
        WP_CLI::error($id->get_error_message());
    }
    $ids[$slug] = $id;
}

$locations = get_theme_mod('nav_menu_locations', []);
foreach (['primary-menu' => ['home', 'contact-us'], 'footer-menu' => ['contact-us', 'terms-condition', 'privacy-policy']] as $location => $slugs) {
    $name = 'Local ' . $location;
    $menu = wp_get_nav_menu_object($name);
    $menu_id = $menu ? $menu->term_id : wp_create_nav_menu($name);
    if (is_wp_error($menu_id)) { WP_CLI::error($menu_id->get_error_message()); }
    $items = wp_get_nav_menu_items($menu_id) ?: [];
    $existing_ids = array_map('intval', wp_list_pluck($items, 'object_id'));
    foreach ($slugs as $slug) {
        if (in_array((int) $ids[$slug], $existing_ids, true)) { continue; }
        $item = wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-object-id' => $ids[$slug],
            'menu-item-object' => 'page',
            'menu-item-type' => 'post_type',
            'menu-item-status' => 'publish',
        ]);
        if (is_wp_error($item)) { WP_CLI::error($item->get_error_message()); }
    }
    $locations[$location] = $menu_id;
}
set_theme_mod('nav_menu_locations', $locations);
update_option('show_on_front', 'page');
update_option('page_on_front', $ids['home']);
update_option('blog_public', '0');
update_option('permalink_structure', '/%postname%/');
flush_rewrite_rules(true);
update_option('rm_local_seed_complete', '1');
WP_CLI::success('Created sample pages and menus for local preview.');
