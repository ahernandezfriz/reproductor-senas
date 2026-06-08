<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Permite subir archivos WebVTT (.vtt) en la biblioteca de medios de WordPress.
 */
function vsp_allow_vtt_upload_mimes( $mimes ) {
    $mimes['vtt'] = 'text/vtt';
    return $mimes;
}
add_filter( 'upload_mimes', 'vsp_allow_vtt_upload_mimes' );

/**
 * Corrige la detección de tipo en versiones recientes de WordPress.
 */
function vsp_fix_vtt_filetype( $data, $file, $filename, $mimes ) {
    if ( empty( $data['ext'] ) || empty( $data['type'] ) ) {
        $ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
        if ( $ext === 'vtt' ) {
            $data['ext']  = 'vtt';
            $data['type'] = 'text/vtt';
        }
    }
    return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'vsp_fix_vtt_filetype', 10, 4 );
