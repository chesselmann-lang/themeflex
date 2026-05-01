<?php
/**
 * Starter Flavor Luxury – Child Theme Functions
 */
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'sf-luxury', get_stylesheet_uri(), [ 'starter-flavor-style' ], wp_get_theme()->get('Version') );
}, 20);

add_filter( 'sf_hero_defaults', function( $defaults ) {
    $defaults['sf_hero_bg_color']   = '#1a1a1a';
    $defaults['sf_hero_text_color'] = '#F0EDE8';
    $defaults['sf_hero_cta1_text']  = 'Discover More';
    return $defaults;
});

// Luxury: refined screenshot in customizer preview
add_action( 'customize_register', function( $wp_customize ) {
    $wp_customize->get_setting('blogname')->transport = 'postMessage';
});
