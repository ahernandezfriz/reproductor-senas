<?php
/**
 * Plugin Name:       Reproductor de Video con Lengua de Señas
 * Plugin URI:        https://tudominio.cl
 * Description:       Reproductor de video accesible con soporte para lengua de señas mediante dos videos sincronizados. Uso: [video_senas principal="URL" senas="URL" titulo="Título" ancho="100%"]
 * Version:           1.0.7
 * Requires at least: 5.6
 * Requires PHP:      7.4
 * Author:            Tu Nombre
 * License:           GPL v2 or later
 * Text Domain:       reproductor-senas
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'VSP_VERSION', '1.0.7' );
define( 'VSP_DIR',     plugin_dir_path( __FILE__ ) );
define( 'VSP_URL',     plugin_dir_url( __FILE__ ) );

require_once VSP_DIR . 'includes/assets.php';
require_once VSP_DIR . 'includes/shortcode.php';
require_once VSP_DIR . 'includes/admin.php';
