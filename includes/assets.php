<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register frontend assets.
 */
function vsp_register_assets() {
	$css_path = VSP_DIR . 'assets/reproductor-senas.css';
	$js_path  = VSP_DIR . 'assets/reproductor-senas.js';
	$css_ver  = file_exists( $css_path ) ? (string) filemtime( $css_path ) : VSP_VERSION;
	$js_ver   = file_exists( $js_path ) ? (string) filemtime( $js_path ) : VSP_VERSION;

	wp_register_style(
		'vsp-style',
		VSP_URL . 'assets/reproductor-senas.css',
		array(),
		$css_ver
	);

	wp_register_script(
		'vimeo-player',
		'https://player.vimeo.com/api/player.js',
		array(),
		VSP_VERSION,
		true
	);

	wp_register_script(
		'vsp-script',
		VSP_URL . 'assets/reproductor-senas.js',
		array(),
		$js_ver,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'vsp_register_assets' );

/**
 * Localize script configuration.
 */
function vsp_localize_script_config() {
	wp_localize_script(
		'vsp-script',
		'vspConfig',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'vsp_vtt' ),
			'i18n'    => array(
				'initError'           => __( 'Could not initialize the player. Check the main video URLs.', 'reproductor-senas' ),
				'loadingMain'         => __( 'Loading main video…', 'reproductor-senas' ),
				'loadingVimeo'        => __( 'Connecting to Vimeo…', 'reproductor-senas' ),
				'loadingSigns'        => __( 'Loading sign language video…', 'reproductor-senas' ),
				'loadingSignsVimeo'   => __( 'Connecting sign language (Vimeo)…', 'reproductor-senas' ),
				'loadingSubs'         => __( 'Loading subtitles…', 'reproductor-senas' ),
				'preparing'           => __( 'Preparing player…', 'reproductor-senas' ),
				'durationMismatch'    => __( 'Main and sign language videos have different durations; drift may occur near the end.', 'reproductor-senas' ),
				'subsUnavailable'     => __( 'Subtitles unavailable', 'reproductor-senas' ),
				'subsLoadError'       => __( 'Could not load subtitles.', 'reproductor-senas' ),
				'subsOn'              => __( 'Disable subtitles', 'reproductor-senas' ),
				'subsOff'             => __( 'Enable subtitles', 'reproductor-senas' ),
				'signsOn'             => __( 'Disable sign language', 'reproductor-senas' ),
				'signsOff'            => __( 'Enable sign language', 'reproductor-senas' ),
				'signsDegraded'       => __( 'Could not load the sign language video. The main video remains available.', 'reproductor-senas' ),
				'signsTimeout'        => __( 'The sign language video took too long to load.', 'reproductor-senas' ),
				'signsLoadError'      => __( 'Could not load the sign language video.', 'reproductor-senas' ),
				'mainVimeoError'      => __( 'Could not load the Vimeo video. Check the link and privacy settings.', 'reproductor-senas' ),
				'mainFileError'       => __( 'Could not load the main video. Check the URL or your connection.', 'reproductor-senas' ),
				'play'                => __( 'Play', 'reproductor-senas' ),
				'pause'               => __( 'Pause', 'reproductor-senas' ),
				'mute'                => __( 'Mute', 'reproductor-senas' ),
				'unmute'              => __( 'Unmute', 'reproductor-senas' ),
				'fullscreen'          => __( 'Fullscreen', 'reproductor-senas' ),
				'exitFullscreen'      => __( 'Exit fullscreen', 'reproductor-senas' ),
			),
		)
	);
}

/**
 * Enqueue plugin assets.
 */
function vsp_enqueue_assets() {
	$GLOBALS['vsp_shortcode_used'] = true;

	wp_enqueue_style( 'vsp-style' );

	if ( ! empty( $GLOBALS['vsp_needs_vimeo'] ) ) {
		wp_enqueue_script( 'vimeo-player' );
	}

	wp_enqueue_script( 'vsp-script' );
	vsp_localize_script_config();
}

/**
 * Enqueue when the current post contains the shortcode.
 */
function vsp_maybe_enqueue_for_post_content() {
	if ( is_admin() ) {
		return;
	}

	$post = get_post();
	if ( ! $post instanceof WP_Post ) {
		return;
	}

	if ( has_shortcode( $post->post_content, 'video_senas' ) ) {
		vsp_enqueue_assets();
	}
}
add_action( 'wp_enqueue_scripts', 'vsp_maybe_enqueue_for_post_content', 25 );

/**
 * Fallback for shortcodes rendered late (popups, AJAX, page builders).
 */
function vsp_footer_enqueue() {
	if ( ! empty( $GLOBALS['vsp_shortcode_used'] ) ) {
		wp_enqueue_style( 'vsp-style' );

		if ( ! empty( $GLOBALS['vsp_needs_vimeo'] ) ) {
			wp_enqueue_script( 'vimeo-player' );
		}

		wp_enqueue_script( 'vsp-script' );
		vsp_localize_script_config();
	}
}
add_action( 'wp_footer', 'vsp_footer_enqueue', 1 );
