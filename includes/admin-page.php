<?php
if ( ! defined( 'ABSPATH' ) ) exit;

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
        <h1>🤟 Reproductor con Lengua de Señas</h1>
        <p>Plugin activo y listo. Usa el shortcode en cualquier página o entrada.</p>

        <div class="card" style="max-width:700px;padding:20px;">
            <h2>Uso del shortcode</h2>
            <pre style="background:#f0f0f1;padding:15px;border-radius:4px;overflow-x:auto;">[video_senas
  principal="https://tudominio.cl/videos/charla.mp4"
  senas="https://tudominio.cl/videos/charla-senas.mp4"
  titulo="Título opcional del video"
  ancho="100%"
]</pre>

            <h3>Parámetros</h3>
            <table class="widefat striped" style="max-width:600px;">
                <thead>
                    <tr>
                        <th>Parámetro</th>
                        <th>Requerido</th>
                        <th>Descripción</th>
                        <th>Ejemplo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>principal</code></td>
                        <td>✅ Sí</td>
                        <td>URL del video principal</td>
                        <td><code>https://dominio.cl/video.mp4</code></td>
                    </tr>
                    <tr>
                        <td><code>senas</code></td>
                        <td>No</td>
                        <td>URL del video de señas. Si se omite, el botón 🤟 no aparece.</td>
                        <td><code>https://dominio.cl/video-senas.mp4</code></td>
                    </tr>
                    <tr>
                        <td><code>titulo</code></td>
                        <td>No</td>
                        <td>Título sobre el reproductor</td>
                        <td><code>Charla sobre accesibilidad</code></td>
                    </tr>
                    <tr>
                        <td><code>ancho</code></td>
                        <td>No</td>
                        <td>Ancho máximo del reproductor</td>
                        <td><code>800px</code> o <code>100%</code></td>
                    </tr>
                </tbody>
            </table>

            <h3>Ejemplo con video de prueba</h3>
            <pre style="background:#f0f0f1;padding:15px;border-radius:4px;">[video_senas
  principal="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4"
  senas="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4"
  titulo="Video de prueba"
]</pre>
        </div>

        <div class="card" style="max-width:700px;padding:20px;margin-top:20px;">
            <h2>Instrucciones para los videos</h2>
            <ol>
                <li>Graba o produce el video principal normalmente.</li>
                <li>Graba al intérprete de señas en un recuadro (fondo neutro o verde).</li>
                <li>Sube ambos archivos <code>.mp4</code> al servidor vía FTP (carpeta <code>/wp-content/uploads/videos/</code> es una buena práctica).</li>
                <li>Copia las URLs de ambos archivos y úsalas en el shortcode.</li>
            </ol>
            <p><strong>Formato recomendado:</strong> MP4 con codec H.264 — compatible con todos los navegadores.</p>
        </div>
    </div>
    <?php
}
