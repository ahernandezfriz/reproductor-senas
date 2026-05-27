=== Reproductor de Video con Lengua de Señas ===
Contributors:      tunombre
Tags:              video, accesibilidad, lengua de señas, LSC, shortcode
Requires at least: 5.6
Tested up to:      6.7
Requires PHP:      7.4
Stable tag:        1.0.7
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
* Funciona dentro de modales y popups dinámicos (Elementor/Bootstrap)
* Controles protegidos contra interferencias de eventos en popups
* Ventana de señas activada por defecto
* Compatible con móviles y tablets
* Sin dependencias externas (vanilla JS)
* Inicialización automática en contenido inyectado dinámicamente

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

= 1.0.7 =
* Mejora robusta de inicialización en modales y popups dinámicos.
* Corrección de controles que no respondían dentro de ventanas modales.
* Ventana de señas activada por defecto al cargar el reproductor.
* Ajustes de accesibilidad/estado inicial del botón de señas (ARIA).

= 1.0.6 =
* Se corrige la inicialización en contenido clonado por popups (se elimina dependencia de atributo HTML para estado de init).

= 1.0.5 =
* Se agregan `preventDefault` y `stopPropagation` en controles para evitar conflictos con scripts de modal.

= 1.0.4 =
* Se define `type="button"` en los controles para evitar comportamiento `submit` en formularios/modales.

= 1.0.3 =
* Se agrega fallback de inicialización al primer contacto del usuario.

= 1.0.2 =
* Integración con hooks/eventos de Elementor frontend para popups.
* Carga de assets reforzada en frontend para escenarios de popup.

= 1.0.1 =
* Inicialización para contenido dinámico mediante observación del DOM.

= 1.0.0 =
* Versión inicial

== Upgrade Notice ==

= 1.0.7 =
Actualización recomendada: corrige interacción de controles en modales/popups y activa señas por defecto.
