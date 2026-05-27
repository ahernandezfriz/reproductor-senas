=== Reproductor de Video con Lengua de Señas ===
Contributors:      tunombre
Tags:              video, accesibilidad, lengua de señas, LSC, shortcode
Requires at least: 5.6
Tested up to:      6.7
Requires PHP:      7.4
Stable tag:        1.0.0
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Reproductor de video accesible con soporte para lengua de señas mediante dos videos sincronizados.

== Descripción ==

Permite insertar un reproductor de video con un recuadro de lengua de señas superpuesto y sincronizado. 
El usuario puede activar o desactivar el recuadro de señas, y moverlo a cualquier posición dentro del video.

**Características:**
* Dos videos sincronizados (principal + señas)
* Recuadro de señas arrastrable (mouse y touch)
* Re-sincronización automática si hay desfase
* Múltiples reproductores por página sin conflictos
* Compatible con móviles y tablets
* Sin dependencias externas (vanilla JS)
* CSS y JS se cargan solo en páginas que usen el shortcode

== Instalación ==

1. Sube la carpeta `reproductor-senas` al directorio `/wp-content/plugins/`
2. Activa el plugin en el menú **Plugins** de WordPress
3. Usa el shortcode en cualquier página o entrada

== Uso ==

= Shortcode básico =
[video_senas principal="https://tudominio.cl/videos/charla.mp4" senas="https://tudominio.cl/videos/charla-senas.mp4"]

= Con todos los parámetros =
[video_senas
  principal="https://tudominio.cl/videos/charla.mp4"
  senas="https://tudominio.cl/videos/charla-senas.mp4"
  titulo="Charla sobre accesibilidad"
  ancho="800px"
]

= Parámetros =

* **principal** _(requerido)_ — URL del video principal (.mp4 recomendado)
* **senas** _(opcional)_ — URL del video de lengua de señas. Si se omite, el botón 🤟 no aparece
* **titulo** _(opcional)_ — Título que aparece sobre el reproductor
* **ancho** _(opcional)_ — Ancho máximo. Ejemplos: `800px`, `100%`. Default: `100%`

= Subir videos vía FTP =

1. Sube los videos a `/wp-content/uploads/videos/` vía FTP
2. La URL quedará como: `https://tudominio.cl/wp-content/uploads/videos/nombre.mp4`
3. Usa esa URL en el shortcode

== Changelog ==

= 1.0.0 =
* Versión inicial

== Upgrade Notice ==

= 1.0.0 =
Primera versión estable.
