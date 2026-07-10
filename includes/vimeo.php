<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extract the numeric ID from a Vimeo URL.
 *
 * @param string $url Vimeo URL.
 * @return string
 */
function ahvpo_vimeo_id_from_url( $url ) {
	$url = trim( (string) $url );
	if ( $url === '' ) {
		return '';
	}

	$patterns = array(
		'#player\.vimeo\.com/video/(\d+)#i',
		'#vimeo\.com/video/(\d+)#i',
		'#vimeo\.com/(?:channels/[^/]+/|groups/[^/]+/videos/|album/\d+/video/|)(\d+)#i',
	);

	foreach ( $patterns as $pattern ) {
		if ( preg_match( $pattern, $url, $matches ) ) {
			return $matches[1];
		}
	}

	return '';
}

/**
 * Build the Vimeo iframe URL with API enabled and native controls hidden.
 *
 * @param string $video_id Vimeo video ID.
 * @param bool   $muted    Whether to start muted.
 * @return string
 */
function ahvpo_vimeo_player_url( $video_id, $muted = false ) {
	$video_id = preg_replace( '/\D/', '', (string) $video_id );
	if ( $video_id === '' ) {
		return '';
	}

	$args = array(
		'api'      => '1',
		'controls' => '0',
		'title'    => '0',
		'byline'   => '0',
		'portrait' => '0',
		'dnt'      => '1',
	);

	if ( $muted ) {
		$args['muted'] = '1';
	}

	return 'https://player.vimeo.com/video/' . $video_id . '?' . http_build_query( $args, '', '&' );
}

/**
 * Mark that the Vimeo Player API is needed for this request.
 */
function ahvpo_mark_vimeo_needed() {
	$GLOBALS['ahvpo_needs_vimeo'] = true;
}
