<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ensure the VTT URL belongs to the same site.
 *
 * @param string $url VTT URL.
 * @return bool
 */
function ahvpo_is_allowed_vtt_url( $url ) {
	$home   = wp_parse_url( home_url() );
	$target = wp_parse_url( $url );

	if ( empty( $home['host'] ) || empty( $target['host'] ) ) {
		return false;
	}

	return strtolower( $home['host'] ) === strtolower( $target['host'] );
}

/**
 * Resolve a same-site URL to a local path under wp-content.
 *
 * @param string $url VTT URL on this site.
 * @return string Local file path or empty string.
 */
function ahvpo_resolve_local_content_file( $url ) {
	$url = esc_url_raw( $url );
	if ( empty( $url ) ) {
		return '';
	}

	$upload = wp_upload_dir();
	if ( ! empty( $upload['baseurl'] ) && ! empty( $upload['basedir'] ) && strpos( $url, $upload['baseurl'] ) === 0 ) {
		$file = str_replace( $upload['baseurl'], $upload['basedir'], $url );
		$file = urldecode( $file );
		if ( file_exists( $file ) && is_readable( $file ) ) {
			return $file;
		}
	}

	$content_url = content_url();
	if ( strpos( $url, $content_url ) === 0 ) {
		$relative = substr( $url, strlen( $content_url ) );
		$file     = WP_CONTENT_DIR . urldecode( $relative );
		if ( file_exists( $file ) && is_readable( $file ) ) {
			return $file;
		}
	}

	return '';
}

/**
 * Fetch VTT content from a local file or same-domain URL.
 *
 * @param string $url VTT URL.
 * @return string
 */
function ahvpo_fetch_vtt_content( $url ) {
	$url = esc_url_raw( $url );
	if ( empty( $url ) || ! ahvpo_is_allowed_vtt_url( $url ) ) {
		return '';
	}

	$file = ahvpo_resolve_local_content_file( $url );
	if ( $file !== '' ) {
		$content = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local trusted path only.
		return ( $content !== false ) ? $content : '';
	}

	$response = wp_remote_get(
		$url,
		array(
			'timeout'   => 15,
			'sslverify' => true,
		)
	);

	if ( is_wp_error( $response ) ) {
		return '';
	}

	if ( (int) wp_remote_retrieve_response_code( $response ) !== 200 ) {
		return '';
	}

	return (string) wp_remote_retrieve_body( $response );
}

/**
 * AJAX: deliver VTT to the frontend (same origin, no CORS).
 */
function ahvpo_ajax_load_vtt() {
	check_ajax_referer( 'ahvpo_vtt', 'nonce' );

	$url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
	if ( empty( $url ) ) {
		wp_send_json_error( array( 'message' => __( 'Empty subtitles URL.', 'arielhf-videopip-overlay' ) ), 400 );
	}

	$content = ahvpo_fetch_vtt_content( $url );
	if ( $content === '' ) {
		wp_send_json_error( array( 'message' => __( 'Could not read the VTT file.', 'arielhf-videopip-overlay' ) ), 404 );
	}

	wp_send_json_success( array( 'content' => $content ) );
}
add_action( 'wp_ajax_ahvpo_load_vtt', 'ahvpo_ajax_load_vtt' );
add_action( 'wp_ajax_nopriv_ahvpo_load_vtt', 'ahvpo_ajax_load_vtt' );

/**
 * Convert a VTT timestamp to seconds.
 *
 * @param string $time_str VTT timestamp.
 * @return float
 */
function ahvpo_vtt_time_to_seconds( $time_str ) {
	$time_str = trim( str_replace( ',', '.', preg_replace( '/\s+.*$/', '', trim( $time_str ) ) ) );
	$parts    = explode( ':', $time_str );
	$h        = 0;
	$m        = 0;
	$s        = 0;

	if ( count( $parts ) === 3 ) {
		$h = (float) $parts[0];
		$m = (float) $parts[1];
		$s = (float) $parts[2];
	} elseif ( count( $parts ) === 2 ) {
		$m = (float) $parts[0];
		$s = (float) $parts[1];
	}

	return ( $h * 3600 ) + ( $m * 60 ) + $s;
}

/**
 * Parse VTT and return cues for the frontend.
 *
 * @param string $content Raw VTT content.
 * @return array<int, array{start: float, end: float, text: string}>
 */
function ahvpo_parse_vtt_cues( $content ) {
	$cues  = array();
	$lines = preg_split( "/\r\n|\r|\n/", preg_replace( '/^\xEF\xBB\xBF/', '', (string) $content ) );
	$total = count( $lines );
	$i     = 0;

	while ( $i < $total ) {
		while ( $i < $total && trim( $lines[ $i ] ) === '' ) {
			$i++;
		}
		if ( $i >= $total ) {
			break;
		}

		if ( preg_match( '/^(WEBVTT|NOTE|STYLE)/i', trim( $lines[ $i ] ) ) ) {
			$i++;
			continue;
		}

		if ( strpos( $lines[ $i ], '-->' ) === false ) {
			$i++;
		}
		if ( $i >= $total || strpos( $lines[ $i ], '-->' ) === false ) {
			continue;
		}

		if ( ! preg_match( '/(.+?)\s*-->\s*(.+)/', $lines[ $i ], $matches ) ) {
			$i++;
			continue;
		}

		$start = ahvpo_vtt_time_to_seconds( $matches[1] );
		$end   = ahvpo_vtt_time_to_seconds( $matches[2] );
		$i++;

		$text_lines = array();
		while ( $i < $total && trim( $lines[ $i ] ) !== '' ) {
			if ( $i + 1 < $total && strpos( $lines[ $i + 1 ], '-->' ) !== false ) {
				break;
			}
			$text_lines[] = trim( $lines[ $i ] );
			$i++;
		}

		if ( ! empty( $text_lines ) && $end > $start ) {
			$cues[] = array(
				'start' => $start,
				'end'   => $end,
				'text'  => implode( "\n", $text_lines ),
			);
		}
	}

	return $cues;
}
