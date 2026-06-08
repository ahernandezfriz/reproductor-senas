<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comprueba que la URL pertenezca al mismo sitio (seguridad).
 *
 * @param string $url VTT URL.
 * @return bool
 */
function vsp_is_allowed_vtt_url( $url ) {
	$home   = wp_parse_url( home_url() );
	$target = wp_parse_url( $url );

	if ( empty( $home['host'] ) || empty( $target['host'] ) ) {
		return false;
	}

	return strtolower( $home['host'] ) === strtolower( $target['host'] );
}

/**
 * Resuelve una URL del sitio a ruta local dentro de wp-content.
 *
 * @param string $url VTT URL on this site.
 * @return string Local file path or empty string.
 */
function vsp_resolve_local_content_file( $url ) {
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
 * Obtiene el contenido de un archivo VTT local o del mismo dominio.
 *
 * @param string $url VTT URL.
 * @return string
 */
function vsp_fetch_vtt_content( $url ) {
	$url = esc_url_raw( $url );
	if ( empty( $url ) || ! vsp_is_allowed_vtt_url( $url ) ) {
		return '';
	}

	$file = vsp_resolve_local_content_file( $url );
	if ( $file !== '' ) {
		$content = file_get_contents( $file );
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
 * AJAX: entrega VTT al frontend (mismo origen, sin CORS).
 */
function vsp_ajax_load_vtt() {
	check_ajax_referer( 'vsp_vtt', 'nonce' );

	$url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
	if ( empty( $url ) ) {
		wp_send_json_error( array( 'message' => __( 'Empty subtitles URL.', 'reproductor-senas' ) ), 400 );
	}

	$content = vsp_fetch_vtt_content( $url );
	if ( $content === '' ) {
		wp_send_json_error( array( 'message' => __( 'Could not read the VTT file.', 'reproductor-senas' ) ), 404 );
	}

	wp_send_json_success( array( 'content' => $content ) );
}
add_action( 'wp_ajax_vsp_load_vtt', 'vsp_ajax_load_vtt' );
add_action( 'wp_ajax_nopriv_vsp_load_vtt', 'vsp_ajax_load_vtt' );

/**
 * Convierte marca de tiempo VTT a segundos.
 *
 * @param string $time_str VTT timestamp.
 * @return float
 */
function vsp_vtt_time_to_seconds( $time_str ) {
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
 * Parsea VTT y devuelve array de cues para el frontend.
 *
 * @param string $content Raw VTT content.
 * @return array<int, array{start: float, end: float, text: string}>
 */
function vsp_parse_vtt_cues( $content ) {
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

		$start = vsp_vtt_time_to_seconds( $matches[1] );
		$end   = vsp_vtt_time_to_seconds( $matches[2] );
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
