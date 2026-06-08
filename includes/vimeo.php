<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Extrae el ID numérico de una URL de Vimeo.
 */
function vsp_vimeo_id_from_url( $url ) {
    $url = trim( (string) $url );
    if ( $url === '' ) {
        return '';
    }

    $patterns = [
        '#player\.vimeo\.com/video/(\d+)#i',
        '#vimeo\.com/video/(\d+)#i',
        '#vimeo\.com/(?:channels/[^/]+/|groups/[^/]+/videos/|album/\d+/video/|)(\d+)#i',
    ];

    foreach ( $patterns as $pattern ) {
        if ( preg_match( $pattern, $url, $matches ) ) {
            return $matches[1];
        }
    }

    return '';
}

/**
 * URL del iframe con API habilitada y controles nativos ocultos (usamos los del plugin).
 */
function vsp_vimeo_player_url( $video_id, $muted = false ) {
    $video_id = preg_replace( '/\D/', '', (string) $video_id );
    if ( $video_id === '' ) {
        return '';
    }

    $args = [
        'api'      => '1',
        'controls' => '0',
        'title'    => '0',
        'byline'   => '0',
        'portrait' => '0',
        'dnt'      => '1',
    ];

    if ( $muted ) {
        $args['muted'] = '1';
    }

    return 'https://player.vimeo.com/video/' . $video_id . '?' . http_build_query( $args, '', '&' );
}

/**
 * Marca que en esta petición hace falta el SDK de Vimeo.
 */
function vsp_mark_vimeo_needed() {
    $GLOBALS['vsp_needs_vimeo'] = true;
}
