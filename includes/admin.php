<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register settings page under Settings.
 */
function ahvpo_admin_menu() {
	add_options_page(
		__( 'Picture-in-Picture Video Player', 'arielhf-videopip-overlay' ),
		__( 'PiP Player', 'arielhf-videopip-overlay' ),
		'manage_options',
		'arielhf-videopip-overlay',
		'ahvpo_admin_page'
	);
}
add_action( 'admin_menu', 'ahvpo_admin_menu' );

/**
 * Render plugin settings/help page.
 */
function ahvpo_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Picture-in-Picture Video Player', 'arielhf-videopip-overlay' ); ?></h1>
		<p>
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %s: shortcode name */
					__( 'Use the %s shortcode in any page or post.', 'arielhf-videopip-overlay' ),
					'<code>[ahvpo_player]</code>'
				)
			);
			?>
		</p>
		<p style="max-width:760px;line-height:1.7;">
			<?php esc_html_e( 'Main video player with an optional in-player picture-in-picture (PiP) mini overlay — ideal for sign language or any secondary synchronized video. This is an in-player overlay, not the browser Picture-in-Picture API.', 'arielhf-videopip-overlay' ); ?>
		</p>

		<h2><?php esc_html_e( 'Shortcode parameters', 'arielhf-videopip-overlay' ); ?></h2>
		<table class="widefat" style="max-width:760px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Parameter', 'arielhf-videopip-overlay' ); ?></th>
					<th><?php esc_html_e( 'Required', 'arielhf-videopip-overlay' ); ?></th>
					<th><?php esc_html_e( 'Description', 'arielhf-videopip-overlay' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><code>principal</code></td>
					<td><?php esc_html_e( 'Yes', 'arielhf-videopip-overlay' ); ?></td>
					<td><?php esc_html_e( 'Vimeo URL or direct video file URL (MP4 on your site/CDN).', 'arielhf-videopip-overlay' ); ?></td>
				</tr>
				<tr>
					<td><code>senas</code></td>
					<td><?php esc_html_e( 'No', 'arielhf-videopip-overlay' ); ?></td>
					<td><?php esc_html_e( 'Optional mini overlay video (Vimeo or file) — typically sign language, or any secondary synchronized video.', 'arielhf-videopip-overlay' ); ?></td>
				</tr>
				<tr>
					<td><code>subtitulos</code></td>
					<td><?php esc_html_e( 'No', 'arielhf-videopip-overlay' ); ?></td>
					<td><?php esc_html_e( 'WebVTT (.vtt) URL from this site’s Media Library/uploads. Leave empty to hide subtitles.', 'arielhf-videopip-overlay' ); ?></td>
				</tr>
				<tr>
					<td><code>titulo</code></td>
					<td><?php esc_html_e( 'No', 'arielhf-videopip-overlay' ); ?></td>
					<td><?php esc_html_e( 'Optional title shown above the player.', 'arielhf-videopip-overlay' ); ?></td>
				</tr>
				<tr>
					<td><code>ancho</code></td>
					<td><?php esc_html_e( 'No', 'arielhf-videopip-overlay' ); ?></td>
					<td><?php esc_html_e( 'Maximum width, e.g. 800px or 100%. Default: 100%.', 'arielhf-videopip-overlay' ); ?></td>
				</tr>
			</tbody>
		</table>

		<h2 style="margin-top:2rem;"><?php esc_html_e( 'Example', 'arielhf-videopip-overlay' ); ?></h2>
		<pre style="background:#f0f0f0;padding:1rem;border-radius:6px;max-width:760px;">[ahvpo_player
  principal="https://vimeo.com/123456789"
  senas="https://example.com/wp-content/uploads/videos/sign-language.mp4"
  subtitulos="https://example.com/wp-content/uploads/subtitles/video.vtt"
  titulo="Accessibility talk"
  ancho="800px"
]</pre>

		<h2 style="margin-top:2rem;"><?php esc_html_e( 'Third-party services', 'arielhf-videopip-overlay' ); ?></h2>
		<p style="max-width:760px;line-height:1.7;">
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %s: Vimeo terms URL link */
					__( 'When you use Vimeo URLs, the player loads Vimeo’s embed and Player API. Please review Vimeo’s terms of use: %s', 'arielhf-videopip-overlay' ),
					'<a href="https://vimeo.com/terms" target="_blank" rel="noopener noreferrer">https://vimeo.com/terms</a>'
				)
			);
			?>
		</p>
		<p style="max-width:760px;line-height:1.7;">
			<?php esc_html_e( 'Subtitle files are read from your own site. No personal data is sent to external analytics services by this plugin.', 'arielhf-videopip-overlay' ); ?>
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
function ahvpo_plugin_action_links( $links ) {
	$link = '<a href="' . esc_url( admin_url( 'options-general.php?page=arielhf-videopip-overlay' ) ) . '">' . esc_html__( 'Settings', 'arielhf-videopip-overlay' ) . '</a>';
	array_unshift( $links, $link );
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( AHVPO_DIR . 'arielhf-videopip-overlay.php' ), 'ahvpo_plugin_action_links' );
