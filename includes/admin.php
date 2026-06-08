<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register settings page under Settings.
 */
function vsp_admin_menu() {
	add_options_page(
		__( 'Sign Language Video Player', 'reproductor-senas' ),
		__( 'Sign Language Player', 'reproductor-senas' ),
		'manage_options',
		'reproductor-senas',
		'vsp_admin_page'
	);
}
add_action( 'admin_menu', 'vsp_admin_menu' );

/**
 * Render plugin settings/help page.
 */
function vsp_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Sign Language Video Player', 'reproductor-senas' ); ?></h1>
		<p>
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %s: shortcode name */
					__( 'Use the %s shortcode in any page or post.', 'reproductor-senas' ),
					'<code>[video_senas]</code>'
				)
			);
			?>
		</p>

		<h2><?php esc_html_e( 'Shortcode parameters', 'reproductor-senas' ); ?></h2>
		<table class="widefat" style="max-width:760px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Parameter', 'reproductor-senas' ); ?></th>
					<th><?php esc_html_e( 'Required', 'reproductor-senas' ); ?></th>
					<th><?php esc_html_e( 'Description', 'reproductor-senas' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><code>principal</code></td>
					<td><?php esc_html_e( 'Yes', 'reproductor-senas' ); ?></td>
					<td><?php esc_html_e( 'Vimeo URL or direct video file URL (MP4 on your site/CDN).', 'reproductor-senas' ); ?></td>
				</tr>
				<tr>
					<td><code>senas</code></td>
					<td><?php esc_html_e( 'No', 'reproductor-senas' ); ?></td>
					<td><?php esc_html_e( 'Sign language video URL (Vimeo or file). Can be mixed with the main video source.', 'reproductor-senas' ); ?></td>
				</tr>
				<tr>
					<td><code>subtitulos</code></td>
					<td><?php esc_html_e( 'No', 'reproductor-senas' ); ?></td>
					<td><?php esc_html_e( 'WebVTT (.vtt) URL from this site’s Media Library/uploads. Leave empty to hide subtitles.', 'reproductor-senas' ); ?></td>
				</tr>
				<tr>
					<td><code>titulo</code></td>
					<td><?php esc_html_e( 'No', 'reproductor-senas' ); ?></td>
					<td><?php esc_html_e( 'Optional title shown above the player.', 'reproductor-senas' ); ?></td>
				</tr>
				<tr>
					<td><code>ancho</code></td>
					<td><?php esc_html_e( 'No', 'reproductor-senas' ); ?></td>
					<td><?php esc_html_e( 'Maximum width, e.g. 800px or 100%. Default: 100%.', 'reproductor-senas' ); ?></td>
				</tr>
			</tbody>
		</table>

		<h2 style="margin-top:2rem;"><?php esc_html_e( 'Example', 'reproductor-senas' ); ?></h2>
		<pre style="background:#f0f0f0;padding:1rem;border-radius:6px;max-width:760px;">[video_senas
  principal="https://vimeo.com/123456789"
  senas="https://example.com/wp-content/uploads/videos/sign-language.mp4"
  subtitulos="https://example.com/wp-content/uploads/subtitles/video.vtt"
  titulo="Accessibility talk"
  ancho="800px"
]</pre>

		<h2 style="margin-top:2rem;"><?php esc_html_e( 'Third-party services', 'reproductor-senas' ); ?></h2>
		<p style="max-width:760px;line-height:1.7;">
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %s: Vimeo terms URL link */
					__( 'When you use Vimeo URLs, the player loads Vimeo’s embed and Player API. Please review Vimeo’s terms of use: %s', 'reproductor-senas' ),
					'<a href="https://vimeo.com/terms" target="_blank" rel="noopener noreferrer">https://vimeo.com/terms</a>'
				)
			);
			?>
		</p>
		<p style="max-width:760px;line-height:1.7;">
			<?php esc_html_e( 'Subtitle files are read from your own site. No personal data is sent to external analytics services by this plugin.', 'reproductor-senas' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Add Settings link on the Plugins screen.
 *
 * @param string[] $links Plugin action links.
 * @return string[]
 */
function vsp_plugin_action_links( $links ) {
	$link = '<a href="' . esc_url( admin_url( 'options-general.php?page=reproductor-senas' ) ) . '">' . esc_html__( 'Settings', 'reproductor-senas' ) . '</a>';
	array_unshift( $links, $link );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( VSP_DIR . 'reproductor-senas.php' ), 'vsp_plugin_action_links' );
