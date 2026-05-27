<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function vsp_register_assets() {
    wp_register_style(
        'vsp-style',
        VSP_URL . 'assets/reproductor-senas.css',
        [],
        VSP_VERSION
    );
    wp_register_script(
        'vsp-script',
        VSP_URL . 'assets/reproductor-senas.js',
        [],
        VSP_VERSION,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'vsp_register_assets' );

/**
 * Cargar assets en todo el frontend para asegurar funcionamiento
 * dentro de popups/modales que se renderizan por AJAX.
 */
function vsp_enqueue_frontend_assets() {
    wp_enqueue_style( 'vsp-style' );
    wp_enqueue_script( 'vsp-script' );
}
add_action( 'wp_enqueue_scripts', 'vsp_enqueue_frontend_assets', 20 );

/**
 * Marca que el shortcode se usó y encola CSS/JS.
 * En modales/popups el shortcode puede renderizarse tarde; wp_footer lo reintenta.
 */
function vsp_enqueue_assets() {
    $GLOBALS['vsp_shortcode_used'] = true;
    wp_enqueue_style( 'vsp-style' );
    wp_enqueue_script( 'vsp-script' );
}

function vsp_footer_enqueue() {
    if ( ! empty( $GLOBALS['vsp_shortcode_used'] ) ) {
        wp_enqueue_style( 'vsp-style' );
        wp_enqueue_script( 'vsp-script' );
    }
}
add_action( 'wp_footer', 'vsp_footer_enqueue', 1 );
