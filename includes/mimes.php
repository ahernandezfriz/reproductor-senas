<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allow WebVTT (.vtt) uploads in the Media Library.
 *
 * @param array<string, string> $mimes Mime types.
 * @return array<string, string>
 */
function ahvpo_allow_vtt_upload_mimes( $mimes ) {
	$mimes['vtt'] = 'text/vtt';
	return $mimes;
}
add_filter( 'upload_mimes', 'ahvpo_allow_vtt_upload_mimes' );

/**
 * Fix VTT filetype detection on recent WordPress versions.
 *
 * @param array<string, mixed> $data     File data.
 * @param string               $file     Full path.
 * @param string               $filename File name.
 * @param array<string, string>|null $mimes Mime types.
 * @return array<string, mixed>
 */
function ahvpo_fix_vtt_filetype( $data, $file, $filename, $mimes ) {
	if ( empty( $data['ext'] ) || empty( $data['type'] ) ) {
		$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
		if ( $ext === 'vtt' ) {
			$data['ext']  = 'vtt';
			$data['type'] = 'text/vtt';
		}
	}
	return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'ahvpo_fix_vtt_filetype', 10, 4 );
