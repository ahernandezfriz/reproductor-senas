<?php
/**
 * Plugin Name:       Sign Language Video Player
 * Plugin URI:        https://github.com/ahernandezfriz/reproductor-senas
 * Description:       Accessible video player with sign language overlay, optional WebVTT subtitles, and support for self-hosted MP4 or Vimeo URLs.
 * Version:           1.1.0
 * Requires at least: 5.6
 * Requires PHP:      7.4
 * Author:            Ariel Hernández Friz
 * Author URI:        https://arielhf.cl
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       reproductor-senas
 *
 * @package ReproductorSenas
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VSP_VERSION', '1.1.0' );
define( 'VSP_DIR', plugin_dir_path( __FILE__ ) );
define( 'VSP_URL', plugin_dir_url( __FILE__ ) );

require_once VSP_DIR . 'includes/mimes.php';
require_once VSP_DIR . 'includes/vimeo.php';
require_once VSP_DIR . 'includes/subtitles.php';
require_once VSP_DIR . 'includes/assets.php';
require_once VSP_DIR . 'includes/shortcode.php';
require_once VSP_DIR . 'includes/admin.php';
