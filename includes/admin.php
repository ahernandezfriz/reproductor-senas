<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Página de ajustes del plugin en el menú de WordPress
 */
function vsp_admin_menu() {
    add_options_page(
        'Reproductor de Señas',
        'Reproductor Señas',
        'manage_options',
        'reproductor-senas',
        'vsp_admin_page'
    );
}
add_action( 'admin_menu', 'vsp_admin_menu' );

function vsp_admin_page() {
    ?>
    <div class="wrap">
        <h1>Reproductor de Video con Lengua de Señas</h1>
        <p>Usa el shortcode <code>[video_senas]</code> en cualquier página o entrada.</p>

        <h2>Parámetros del shortcode</h2>
        <table class="widefat" style="max-width:700px;">
            <thead>
                <tr>
                    <th>Parámetro</th>
                    <th>Requerido</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
                <tr><td><code>principal</code></td><td>✅ Sí</td><td>URL del video principal (.mp4 recomendado)</td></tr>
                <tr><td><code>senas</code></td><td>No</td><td>URL del video de lengua de señas. Si se omite, el botón 🤟 no aparece.</td></tr>
                <tr><td><code>titulo</code></td><td>No</td><td>Título que aparece sobre el reproductor</td></tr>
                <tr><td><code>ancho</code></td><td>No</td><td>Ancho máximo. Ej: <code>800px</code> o <code>100%</code>. Default: <code>100%</code></td></tr>
            </tbody>
        </table>

        <h2 style="margin-top:2rem;">Ejemplo de uso</h2>
        <pre style="background:#f0f0f0;padding:1rem;border-radius:6px;max-width:700px;">[video_senas
  principal="https://tudominio.cl/videos/charla.mp4"
  senas="https://tudominio.cl/videos/charla-senas.mp4"
  titulo="Charla sobre accesibilidad"
  ancho="100%"
]</pre>

        <h2 style="margin-top:2rem;">Instrucciones para subir videos vía FTP</h2>
        <ol style="max-width:600px;line-height:1.8;">
            <li>Sube el video principal y el video de señas a <code>/wp-content/uploads/videos/</code> vía FTP.</li>
            <li>La URL resultante será: <code>https://tudominio.cl/wp-content/uploads/videos/nombre.mp4</code></li>
            <li>Usa esas URLs en los parámetros del shortcode.</li>
        </ol>
    </div>
    <?php
}

/**
 * Agrega enlace de ajustes en la lista de plugins
 */
function vsp_plugin_action_links( $links ) {
    $link = '<a href="' . admin_url( 'options-general.php?page=reproductor-senas' ) . '">Ajustes</a>';
    array_unshift( $links, $link );
    return $links;
}
add_filter( 'plugin_action_links_reproductor-senas/reproductor-senas.php', 'vsp_plugin_action_links' );
