<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normaliza URL de medios: vacío o inválido => cadena vacía.
 *
 * @param string $url Media URL.
 * @return string
 */
function vsp_normalize_media_url( $url ) {
	$url = trim( (string) $url );
	if ( $url === '' ) {
		return '';
	}

	$url = esc_url( $url );
	if ( $url === '' ) {
		return '';
	}

	$parsed = wp_parse_url( $url );
	if ( empty( $parsed['scheme'] ) || ! in_array( $parsed['scheme'], array( 'http', 'https' ), true ) ) {
		return '';
	}

	return $url;
}

/**
 * Sanitize max-width value for inline CSS.
 *
 * @param string $value Width attribute from shortcode.
 * @return string
 */
function vsp_sanitize_width( $value ) {
	$value = trim( (string) $value );
	if ( $value === '' ) {
		return '100%';
	}

	if ( preg_match( '/^\d+(\.\d+)?(px|%|em|rem|vw|vh)$/i', $value ) ) {
		return $value;
	}

	return '100%';
}

/**
 * Shortcode [video_senas].
 *
 * @param array<string, string> $atts Shortcode attributes.
 * @return string
 */
function vsp_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'principal'  => '',
			'senas'      => '',
			'subtitulos' => '',
			'titulo'     => '',
			'ancho'      => '100%',
		),
		$atts,
		'video_senas'
	);

	if ( empty( trim( (string) $atts['principal'] ) ) ) {
		return '<p class="vsp-error">' . wp_kses_post(
			sprintf(
				/* translators: %s: parameter name */
				__( 'Missing required parameter %s with the video URL.', 'reproductor-senas' ),
				'<code>principal</code>'
			)
		) . '</p>';
	}

	$principal_raw   = trim( (string) $atts['principal'] );
	$principal_vimeo = vsp_vimeo_id_from_url( $principal_raw );
	$url_principal   = $principal_vimeo !== '' ? '' : vsp_normalize_media_url( $principal_raw );

	if ( $principal_vimeo === '' && $url_principal === '' ) {
		return '<p class="vsp-error">' . wp_kses_post(
			sprintf(
				/* translators: %s: parameter name */
				__( 'The %s parameter must be a valid Vimeo link or a direct video file URL on the web.', 'reproductor-senas' ),
				'<code>principal</code>'
			)
		) . '</p>';
	}

	$senas_raw      = trim( (string) $atts['senas'] );
	$senas_vimeo    = vsp_vimeo_id_from_url( $senas_raw );
	$url_senas      = $senas_vimeo !== '' ? '' : vsp_normalize_media_url( $senas_raw );
	$main_vimeo     = $principal_vimeo !== '';
	$senas_vimeo_on = $senas_vimeo !== '';

	if ( $main_vimeo || $senas_vimeo_on ) {
		vsp_mark_vimeo_needed();
	}

	$url_subs    = vsp_normalize_media_url( $atts['subtitulos'] );
	$titulo      = sanitize_text_field( $atts['titulo'] );
	$titulo_attr = esc_attr( $titulo );
	$ancho       = esc_attr( vsp_sanitize_width( $atts['ancho'] ) );
	$tiene_senas = $senas_vimeo_on || $url_senas !== '';
	$tiene_subs  = $url_subs !== '';
	$subs_inline = '';
	$subs_cues_b64 = '';

	if ( $tiene_subs ) {
		$vtt_raw = vsp_fetch_vtt_content( $url_subs );
		if ( $vtt_raw !== '' ) {
			$subs_inline = base64_encode( $vtt_raw );
			$cues        = vsp_parse_vtt_cues( $vtt_raw );
			if ( ! empty( $cues ) ) {
				$subs_cues_b64 = base64_encode( wp_json_encode( $cues ) );
			}
		}
	}

	$uid = 'vsp_' . wp_unique_id();

	vsp_enqueue_assets();

	ob_start();
	?>
	<div class="vsp-wrapper" id="<?php echo esc_attr( $uid ); ?>" style="max-width:<?php echo esc_attr( $ancho ); ?>;" data-tiene-senas="<?php echo $tiene_senas ? '1' : '0'; ?>" data-tiene-subtitulos="<?php echo $tiene_subs ? '1' : '0'; ?>" data-main-type="<?php echo $main_vimeo ? 'vimeo' : 'file'; ?>"<?php echo $main_vimeo ? ' data-vimeo-id="' . esc_attr( $principal_vimeo ) . '"' : ''; ?><?php echo $tiene_senas ? ' data-senas-type="' . ( $senas_vimeo_on ? 'vimeo' : 'file' ) . '"' : ''; ?><?php echo $senas_vimeo_on ? ' data-senas-vimeo-id="' . esc_attr( $senas_vimeo ) . '"' : ''; ?><?php echo $tiene_subs ? ' data-subtitulos="' . esc_attr( $url_subs ) . '"' : ''; ?><?php echo $subs_inline !== '' ? ' data-subtitulos-data="' . esc_attr( $subs_inline ) . '"' : ''; ?><?php echo $subs_cues_b64 !== '' ? ' data-subtitulos-cues="' . esc_attr( $subs_cues_b64 ) . '"' : ''; ?>>

		<?php if ( $titulo !== '' ) : ?>
			<p class="vsp-titulo"><?php echo esc_html( $titulo ); ?></p>
		<?php endif; ?>

		<div class="vsp-stage">

			<div class="vsp-loading" aria-live="polite" aria-busy="true">
				<span class="vsp-loading-spinner" aria-hidden="true"></span>
				<p class="vsp-loading-text"><?php esc_html_e( 'Loading player…', 'reproductor-senas' ); ?></p>
			</div>

			<div class="vsp-load-error" role="alert" hidden>
				<p class="vsp-load-error-text"></p>
			</div>

			<div class="vsp-asset-warn" role="status" hidden></div>

			<?php if ( $main_vimeo ) : ?>
			<div class="vsp-main-host">
				<iframe class="vsp-vimeo-embed vsp-main-vimeo"
					src="<?php echo esc_url( vsp_vimeo_player_url( $principal_vimeo ) ); ?>"
					allow="autoplay; fullscreen; picture-in-picture"
					allowfullscreen
					title="<?php echo $titulo_attr !== '' ? esc_attr( $titulo_attr ) : esc_attr__( 'Main video', 'reproductor-senas' ); ?>"></iframe>
			</div>
			<?php else : ?>
			<video class="vsp-main"
				src="<?php echo esc_url( $url_principal ); ?>"
				preload="metadata"
				playsinline></video>
			<?php endif; ?>

			<?php if ( $tiene_senas ) : ?>
			<div class="vsp-signs-wrap" aria-hidden="false" style="display:block;">
				<div class="vsp-signs-dragbar" title="<?php esc_attr_e( 'Drag to move', 'reproductor-senas' ); ?>">
					<span class="vsp-signs-dragbar-grip" aria-hidden="true"></span>
					<span class="vsp-signs-dragbar-label"><?php esc_html_e( 'Drag to move', 'reproductor-senas' ); ?></span>
				</div>
				<?php if ( $senas_vimeo_on ) : ?>
				<iframe class="vsp-vimeo-embed vsp-signs-vimeo"
					src="<?php echo esc_url( vsp_vimeo_player_url( $senas_vimeo, true ) ); ?>"
					allow="autoplay; fullscreen; picture-in-picture"
					allowfullscreen
					title="<?php esc_attr_e( 'Sign language', 'reproductor-senas' ); ?>"></iframe>
				<?php else : ?>
				<video class="vsp-signs"
					src="<?php echo esc_url( $url_senas ); ?>"
					preload="auto"
					playsinline
					muted></video>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<div class="vsp-overlay-play" role="button" tabindex="0" aria-label="<?php esc_attr_e( 'Play video', 'reproductor-senas' ); ?>">
				<div class="vsp-big-play">
					<svg width="28" height="28" viewBox="0 0 24 24" fill="white" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
				</div>
			</div>

			<?php if ( $tiene_subs ) : ?>
			<div class="vsp-subtitles" aria-live="polite" aria-hidden="false">
				<p class="vsp-subtitles-text"></p>
			</div>
			<?php endif; ?>

			<div class="vsp-controls" role="group" aria-label="<?php esc_attr_e( 'Player controls', 'reproductor-senas' ); ?>">

				<div class="vsp-progress-row">
					<span class="vsp-time-cur">0:00</span>
					<div class="vsp-progress-bg" role="slider" aria-label="<?php esc_attr_e( 'Progress', 'reproductor-senas' ); ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" tabindex="0">
						<div class="vsp-progress-fill"></div>
					</div>
					<span class="vsp-time-dur">0:00</span>
				</div>

				<div class="vsp-btn-row">

					<button type="button" class="vsp-btn-play" aria-label="<?php esc_attr_e( 'Play', 'reproductor-senas' ); ?>">
						<svg class="vsp-icon-play" width="22" height="22" viewBox="0 0 24 24" fill="white" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
						<svg class="vsp-icon-pause" width="22" height="22" viewBox="0 0 24 24" fill="white" aria-hidden="true"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
					</button>

					<div class="vsp-vol-group">
						<button type="button" class="vsp-btn-mute" aria-label="<?php esc_attr_e( 'Mute', 'reproductor-senas' ); ?>">
							<svg class="vsp-icon-vol" width="18" height="18" viewBox="0 0 24 24" fill="white" aria-hidden="true"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/></svg>
							<svg class="vsp-icon-muted" width="18" height="18" viewBox="0 0 24 24" fill="white" aria-hidden="true"><path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z"/></svg>
						</button>
						<input class="vsp-vol" type="range" min="0" max="1" step="0.05" value="1" aria-label="<?php esc_attr_e( 'Volume', 'reproductor-senas' ); ?>">
					</div>

					<div class="vsp-spacer"></div>

					<?php if ( $tiene_subs ) : ?>
					<button type="button" class="vsp-btn-subtitles" aria-pressed="true" aria-label="<?php esc_attr_e( 'Disable subtitles', 'reproductor-senas' ); ?>">
						<span class="vsp-subtitles-label"><?php esc_html_e( 'Disable subtitles', 'reproductor-senas' ); ?></span>
					</button>
					<?php endif; ?>

					<?php if ( $tiene_senas ) : ?>
					<button type="button" class="vsp-btn-signs" aria-pressed="true" aria-label="<?php esc_attr_e( 'Disable sign language', 'reproductor-senas' ); ?>">
						<span class="vsp-signs-label"><?php esc_html_e( 'Disable sign language', 'reproductor-senas' ); ?></span>
					</button>
					<?php endif; ?>

					<button type="button" class="vsp-btn-fs" aria-label="<?php esc_attr_e( 'Fullscreen', 'reproductor-senas' ); ?>">
						<svg class="vsp-icon-expand" width="18" height="18" viewBox="0 0 24 24" fill="white" aria-hidden="true"><path d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/></svg>
						<svg class="vsp-icon-compress" width="18" height="18" viewBox="0 0 24 24" fill="white" aria-hidden="true"><path d="M5 16h3v3h2v-5H5v2zm3-8H5v2h5V5H8v3zm6 11h2v-3h3v-2h-5v5zm2-11V5h-2v5h5V8h-3z"/></svg>
					</button>

				</div>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'video_senas', 'vsp_shortcode' );
