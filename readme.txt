=== Sign Language Video Player ===
Contributors: arielhf
Tags: video, accessibility, shortcode, subtitles, sign-language
Requires at least: 5.6
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Accessible video player with a synchronized sign language overlay, optional WebVTT subtitles, and support for self-hosted MP4 or Vimeo URLs.

== Description ==

Insert an accessible video player with an optional draggable sign language window, optional WebVTT subtitles, and custom controls that work inside modals and page builders.

**Features**

* Main video from a direct file URL or Vimeo
* Optional sign language video (file or Vimeo), synchronized with the main video
* Optional WebVTT subtitles from your site's Media Library
* Draggable sign language window (mouse and touch)
* Automatic re-sync when playback drifts
* Coordinated asset loading with graceful degradation if sign language or subtitles fail
* Multiple players per page
* Works in Elementor popups, Bootstrap modals, and other dynamic content
* No frontend branding or tracking

**Third-party services**

When you use Vimeo URLs, the player loads Vimeo's embed and Player API. Review Vimeo's terms: https://vimeo.com/terms

Subtitle files are read from your own site. This plugin does not send visitor data to analytics services.

**Development**

Source development repository: https://github.com/ahernandezfriz/reproductor-senas

== Installation ==

1. Upload the `reproductor-senas` folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Place the shortcode on any page or post

== Frequently Asked Questions ==

= Can I mix Vimeo and self-hosted videos? =

Yes. `principal` and `senas` are independent. For example, the main video can be on Vimeo while the sign language video is an MP4 in your uploads folder.

= Where should subtitle files live? =

Upload `.vtt` files to your WordPress Media Library. The shortcode must use a URL from the same site.

= Does it work inside Elementor popups? =

Yes. The player initializes on DOM changes, Elementor popup events, and first user interaction as a fallback.

== Screenshots ==

1. Player with sign language overlay and custom controls
2. Subtitles displayed above the control bar
3. Settings page with shortcode documentation

== Usage ==

Basic shortcode:

`[video_senas principal="https://example.com/wp-content/uploads/videos/talk.mp4" senas="https://example.com/wp-content/uploads/videos/talk-signs.mp4"]`

Full example:

`[video_senas principal="https://vimeo.com/123456789" senas="https://example.com/wp-content/uploads/videos/sign-language.mp4" subtitulos="https://example.com/wp-content/uploads/subtitles/talk.vtt" titulo="Accessibility talk" ancho="800px"]`

**Parameters**

* `principal` (required) — Vimeo URL or direct video file URL
* `senas` (optional) — Sign language video URL (Vimeo or file)
* `subtitulos` (optional) — WebVTT file URL from this site. Leave empty to hide subtitles
* `titulo` (optional) — Title shown above the player
* `ancho` (optional) — Maximum width, e.g. `800px` or `100%`. Default: `100%`

== Changelog ==

= 1.1.0 =
* WordPress.org release preparation: conditional asset loading, i18n, security hardening, and documentation updates.
* Support for Vimeo and self-hosted videos in any combination.
* Optional WebVTT subtitles embedded server-side for reliability.
* Coordinated loading with warnings when sign language or subtitles fail.
* Popup/modal compatibility improvements (Elementor, Bootstrap).

= 1.0.22 =
* Vimeo player integration for main and sign language videos.
* Mixed source support (Vimeo + local files).

= 1.0.21 =
* Coordinated asset loading and user-facing load states.

= 1.0.20 =
* Fixed WebVTT parsing and server-side cue embedding.

= 1.0.19 =
* Subtitle button styling and version sync.

= 1.0.7 =
* Robust initialization in dynamic modals and popups.
* Sign language window enabled by default.
* ARIA improvements.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.1.0 =
Recommended update for WordPress.org compatibility, Vimeo support, subtitles, and improved modal behavior.
