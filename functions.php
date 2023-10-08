<?php
/**
 * Add HTML5 theme support.
 */
function wpdocs_after_setup_theme() {
	add_theme_support( 'html5', array( 'search-form' ) );
}
add_action( 'after_setup_theme', 'wpdocs_after_setup_theme' );

function my_theme_enqueue_styles() {
 
    $theme_version = wp_get_theme()->get( 'Version' );

    // This is your main stylesheet file
    wp_enqueue_style( 'id-wizard-theme-css', get_stylesheet_uri(), array(), $theme_version );
 
}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );

add_theme_support('post-thumbnails');

register_nav_menus(
    array(
    'primary-menu' => __( 'Main Menu' ),
    ));
