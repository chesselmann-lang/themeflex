<?php
/**
 * Starter Flavor Bold Dark – Child Theme Functions
 */
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'sf-bold-dark', get_stylesheet_uri(), [ 'starter-flavor-style' ], wp_get_theme()->get('Version') );
    wp_enqueue_style( 'sf-bold-dark-fonts', 'https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;700&display=swap', [], null );
}, 20);

add_filter( 'sf_hero_defaults', function( $defaults ) {
    $defaults['sf_hero_bg_color']   = '#000000';
    $defaults['sf_hero_text_color'] = '#F0F0F0';
    return $defaults;
});

// Force dark mode meta
add_action( 'wp_head', function() {
    echo '<meta name="color-scheme" content="dark">' . "\n";
});
