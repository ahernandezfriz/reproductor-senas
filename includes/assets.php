<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register frontend assets.
 */
function ahvpo_register_assets() {
	$css_path = AHVPO_DIR . 'assets/arielhf-videopip-overlay.css';
	$js_path  = AHVPO_DIR . 'assets/arielhf-videopip-overlay.js';
	$css_ver  = file_exists( $css_path ) ? (string) filemtime( $css_path ) : AHVPO_VERSION;
	$js_ver   = file_exists( $js_path ) ? (string) filemtime( $js_path ) : AHVPO_VERSION;

	wp_register_style(
		'ahvpo-style',
		AHVPO_URL . 'assets/arielhf-videopip-overlay.css',
		array(),
		$css_ver
	);

	wp_register_script(
		'ahvpo-vimeo-player',
		'https://player.vimeo.com/api/player.js',
		array(),
		AHVPO_VERSION,
		true
	);

	wp_register_script(
		'ahvpo-script',
		AHVPO_URL . 'assets/arielhf-videopip-overlay.js',
		array(),
		$js_ver,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'ahvpo_register_assets' );

/**
 * Localize script configuration.
 */
function ahvpo_localize_script_config() {
	wp_localize_script(
		'ahvpo-script',
		'ahvpoConfig',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ahvpo_vtt' ),
			'i18n'    => array(
				'initError'           => __( 'Could not initialize the player. Check the main video URLs.', 'arielhf-videopip-overlay' ),
				'loadingMain'         => __( 'Loading main video…', 'arielhf-videopip-overlay' ),
				'loadingVimeo'        => __( 'Connecting to Vimeo…', 'arielhf-videopip-overlay' ),
				'loadingSigns'        => __( 'Loading sign language video…', 'arielhf-videopip-overlay' ),
				'loadingSignsVimeo'   => __( 'Connecting sign language (Vimeo)…', 'arielhf-videopip-overlay' ),
				'loadingSubs'         => __( 'Loading subtitles…', 'arielhf-videopip-overlay' ),
				'preparing'           => __( 'Preparing player…', 'arielhf-videopip-overlay' ),
				'durationMismatch'    => __( 'Main and sign language videos have different durations; drift may occur near the end.', 'arielhf-videopip-overlay' ),
				'subsUnavailable'     => __( 'Subtitles unavailable', 'arielhf-videopip-overlay' ),
				'subsLoadError'       => __( 'Could not load subtitles.', 'arielhf-videopip-overlay' ),
				'subsOn'              => __( 'Disable subtitles', 'arielhf-videopip-overlay' ),
				'subsOff'             => __( 'Enable subtitles', 'arielhf-videopip-overlay' ),
				'signsOn'             => __( 'Disable sign language', 'arielhf-videopip-overlay' ),
				'signsOff'            => __( 'Enable sign language', 'arielhf-videopip-overlay' ),
				'signsDegraded'       => __( 'Could not load the sign language video. The main video remains available.', 'arielhf-videopip-overlay' ),
				'signsTimeout'        => __( 'The sign language video took too long to load.', 'arielhf-videopip-overlay' ),
				'signsLoadError'      => __( 'Could not load the sign language video.', 'arielhf-videopip-overlay' ),
				'mainVimeoError'      => __( 'Could not load the Vimeo video. Check the link and privacy settings.', 'arielhf-videopip-overlay' ),
				'mainFileError'       => __( 'Could not load the main video. Check the URL or your connection.', 'arielhf-videopip-overlay' ),
				'play'                => __( 'Play', 'arielhf-videopip-overlay' ),
				'pause'               => __( 'Pause', 'arielhf-videopip-overlay' ),
				'mute'                => __( 'Mute', 'arielhf-videopip-overlay' ),
				'unmute'              => __( 'Unmute', 'arielhf-videopip-overlay' ),
				'fullscreen'          => __( 'Fullscreen', 'arielhf-videopip-overlay' ),
				'exitFullscreen'      => __( 'Exit fullscreen', 'arielhf-videopip-overlay' ),
			),
		)
	);
}

/**
 * Enqueue plugin assets.
 */
function ahvpo_enqueue_assets() {
	$GLOBALS['ahvpo_shortcode_used'] = true;

	wp_enqueue_style( 'ahvpo-style' );

	if ( ! empty( $GLOBALS['ahvpo_needs_vimeo'] ) ) {
		wp_enqueue_script( 'ahvpo-vimeo-player' );
	}

	wp_enqueue_script( 'ahvpo-script' );
	ahvpo_localize_script_config();
}

/**
 * Enqueue when the current post contains a supported shortcode.
 */
function ahvpo_maybe_enqueue_for_post_content() {
	if ( is_admin() ) {
		return;
	}

	$post = get_post();
	if ( ! $post instanceof WP_Post ) {
		return;
	}

	if ( has_shortcode( $post->post_content, 'ahvpo_player' ) || has_shortcode( $post->post_content, 'video_senas' ) ) {
		ahvpo_enqueue_assets();
	}
}
add_action( 'wp_enqueue_scripts', 'ahvpo_maybe_enqueue_for_post_content', 25 );

/**
 * Fallback for shortcodes rendered late (popups, AJAX, page builders).
 */
function ahvpo_footer_enqueue() {
	if ( ! empty( $GLOBALS['ahvpo_shortcode_used'] ) ) {
		wp_enqueue_style( 'ahvpo-style' );

		if ( ! empty( $GLOBALS['ahvpo_needs_vimeo'] ) ) {
			wp_enqueue_script( 'ahvpo-vimeo-player' );
		}

		wp_enqueue_script( 'ahvpo-script' );
		ahvpo_localize_script_config();
	}
}
add_action( 'wp_footer', 'ahvpo_footer_enqueue', 1 );
