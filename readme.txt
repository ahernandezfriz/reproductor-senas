=== Reproductor de Video Picture in Picture ===
Contributors: arielhf
Tags: video, accessibility, subtitles, sign-language, picture-in-picture
Requires at least: 5.6
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.2.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Reproductor con ventana mini picture-in-picture (PiP) opcional, ideal para lengua de señas u otro video secundario.

== Description ==

Inserta un reproductor accesible con una ventana mini arrastrable sobre el video principal en modo picture-in-picture (PiP), subtítulos WebVTT opcionales y controles personalizados que funcionan en modales y maquetadores de páginas.

**Demo en vivo**

[Probar la demo en vivo](https://arielhf.cl/plugins-video-pip-overlay/)

**Características**

* Video principal desde un archivo (URL directa) o Vimeo
* Video mini opcional (archivo o Vimeo), sincronizado con el principal — normalmente lengua de señas, u otro video secundario
* Subtítulos WebVTT opcionales desde la Biblioteca de medios
* Ventana mini arrastrable (ratón y táctil)
* Re-sincronización automática si se desfasa la reproducción
* Carga coordinada: si fallan las señas o los subtítulos, el video principal sigue disponible
* Varios reproductores en la misma página
* Compatible con popups de Elementor, modales Bootstrap y contenido dinámico
* Sin branding ni seguimiento en el frontend

**Servicios de terceros**

Si usas URLs de Vimeo, el reproductor carga el embed y la API de Vimeo. Revisa sus términos: https://vimeo.com/terms

Los subtítulos se leen desde tu propio sitio. Este plugin no envía datos de visitantes a servicios de analítica externos.

**Desarrollo**

Repositorio de desarrollo: https://github.com/ahernandezfriz/reproductor-senas

== Installation ==

1. Sube la carpeta `arielhf-videopip-overlay` a `/wp-content/plugins/`
2. Activa el plugin en el menú **Plugins** de WordPress
3. Inserta el shortcode en cualquier página o entrada

== Frequently Asked Questions ==

= ¿Puedo mezclar Vimeo y videos alojados en mi sitio? =

Sí. `principal` y `senas` son independientes. Por ejemplo, el principal puede estar en Vimeo y la ventana mini ser un MP4 en tu carpeta de medios.

= ¿Dónde deben estar los subtítulos? =

Sube archivos `.vtt` a la Biblioteca de medios de WordPress. El shortcode debe usar una URL del mismo sitio.

= ¿Funciona en popups de Elementor? =

Sí. El reproductor se inicializa ante cambios del DOM, eventos de popup de Elementor y, como respaldo, en la primera interacción del usuario.

= ¿Soporta YouTube? =

No. Por ahora solo admite archivos de video (URL directa) y Vimeo.

== Screenshots ==

1. Reproductor con ventana mini y controles personalizados
2. Subtítulos mostrados sobre la barra de controles
3. Página de ajustes con documentación del shortcode

== Usage ==

Shortcode básico:

`[ahvpo_player principal="https://example.com/wp-content/uploads/videos/charla.mp4" senas="https://example.com/wp-content/uploads/videos/charla-senas.mp4"]`

Ejemplo completo:

`[ahvpo_player principal="https://vimeo.com/123456789" senas="https://example.com/wp-content/uploads/videos/lengua-senas.mp4" subtitulos="https://example.com/wp-content/uploads/subtitulos/charla.vtt" titulo="Charla de accesibilidad" ancho="800px"]`

**Parámetros**

* `principal` (obligatorio) — URL de Vimeo o archivo de video
* `senas` (opcional) — URL del video de la ventana mini (Vimeo o archivo); normalmente lengua de señas u otro video secundario
* `subtitulos` (opcional) — URL de archivo WebVTT de este sitio. Déjalo vacío para ocultar subtítulos
* `titulo` (opcional) — Título mostrado sobre el reproductor
* `ancho` (opcional) — Ancho máximo, p. ej. `800px` o `100%`. Por defecto: `100%`

Alias legado: `[video_senas]` sigue funcionando con los mismos parámetros.

== Changelog ==

= 1.2.4 =
* Carga de traducciones (`load_plugin_textdomain`) y paquetes es_ES / es_CL.
* Seguridad: rutas locales de subtítulos VTT validadas con realpath bajo uploads/wp-content.
* i18n: cadenas fuente en inglés; la UI en español se carga según el idioma del sitio.
* Prefijo unificado de clases CSS/HTML/JS: `ahvpo-` (antes `vsp-`).

= 1.2.3 =
* Subtítulos: barra más corta (máx. 60% del ancho) y un poco más abajo para no tapar el video secundario (PiP).

= 1.2.2 =
* Corrección: arrastrar el video secundario también en pausa o antes de reproducir (ya no lo tapa el overlay de play).
* Corrección: clic en el secundario ya no inicia/pausa el video principal.
* Corrección: play/pausa con clic en el área del video principal se mantiene al reproducir.
* Barra “Arrastrar para mover” y asa de redimensionar solo visibles al pasar el mouse.
* Alineación del control de volumen con el icono de mute.

= 1.2.1 =
* Corrección: al salir de pantalla completa, el video secundario vuelve a su posición y tamaño por defecto (ya no desaparecía si se había movido en fullscreen).
* Nuevo: redimensionar la ventana secundaria desde la esquina superior izquierda (hasta un 25% más).
* Barra “Arrastrar para mover” reubicada abajo para no interferir con el control de tamaño.
* Textos de los botones de subtítulos y lengua de señas en español.
* Nombre visible actualizado a “Reproductor de Video Picture in Picture”.
* Readme del directorio en español y límite de 5 etiquetas.

= 1.2.0 =
* Renombrado a ArielHF VideoPIP Overlay (nombre y slug distintivos para WordPress.org).
* Prefijos en APIs PHP, handles de scripts, acciones AJAX y text domain (`ahvpo_` / `arielhf-videopip-overlay`).
* Shortcode principal `[ahvpo_player]`; `[video_senas]` se mantiene como alias compatible.
* Documentación del modo picture-in-picture (PiP) con ventana mini sobrepuesta.

= 1.1.0 =
* Preparación para WordPress.org: carga condicional de assets, i18n, endurecimiento de seguridad y documentación.
* Soporte de Vimeo y videos autoalojados en cualquier combinación.
* Subtítulos WebVTT opcionales embebidos en el servidor para mayor fiabilidad.
* Carga coordinada con avisos si fallan las señas o los subtítulos.
* Mejoras de compatibilidad con popups/modales (Elementor, Bootstrap).

= 1.0.22 =
* Integración del reproductor Vimeo para video principal y de señas.
* Soporte de fuentes mixtas (Vimeo + archivos locales).

= 1.0.21 =
* Carga coordinada de assets y estados de carga visibles para el usuario.

= 1.0.20 =
* Corrección del parseo WebVTT y del embebido de cues en el servidor.

= 1.0.19 =
* Estilos del botón de subtítulos y sincronización de versión.

= 1.0.7 =
* Inicialización robusta en modales y popups dinámicos.
* Ventana de señas activada por defecto.
* Mejoras ARIA.

= 1.0.0 =
* Lanzamiento inicial.

== Upgrade Notice ==

= 1.2.4 =
Mejoras de i18n, seguridad en subtítulos VTT y prefijo CSS unificado (ahvpo-).

= 1.2.3 =
Ajusta el tamaño y posición de los subtítulos para que no se superpongan al video secundario.

= 1.2.2 =
Corrige arrastre del PiP en pausa, clics que interferían con play/pausa y la alineación del control de volumen.

= 1.2.1 =
Mejora el video secundario (redimensionar), corrige el fallo al salir de pantalla completa y actualiza textos en español.

= 1.2.0 =
Actualización recomendada: nuevo nombre/slug para WordPress.org, APIs con prefijo y shortcode `[ahvpo_player]` (el alias `[video_senas]` sigue funcionando).
