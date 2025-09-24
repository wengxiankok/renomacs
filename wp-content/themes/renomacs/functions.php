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

?>