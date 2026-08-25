<?php
/**
 * Plugin Name:       Reproductor de Video Picture in Picture
 * Plugin URI:        https://github.com/ahernandezfriz/reproductor-senas
 * Description:       Video player with an optional picture-in-picture (PiP) mini overlay — ideal for sign language or any secondary synchronized video. Supports self-hosted MP4, Vimeo, and optional WebVTT subtitles.
 * Version:           1.2.0
 * Requires at least: 5.6
 * Requires PHP:      7.4
 * Author:            Ariel Hernández Friz
 * Author URI:        https://arielhf.cl
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       arielhf-videopip-overlay
 *
 * @package ArielhfVideopipOverlay
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AHVPO_VERSION', '1.2.0' );
define( 'AHVPO_DIR', plugin_dir_path( __FILE__ ) );
define( 'AHVPO_URL', plugin_dir_url( __FILE__ ) );

require_once AHVPO_DIR . 'includes/mimes.php';
require_once AHVPO_DIR . 'includes/vimeo.php';
require_once AHVPO_DIR . 'includes/subtitles.php';
require_once AHVPO_DIR . 'includes/assets.php';
require_once AHVPO_DIR . 'includes/shortcode.php';
require_once AHVPO_DIR . 'includes/admin.php';
