<?php

require_once __DIR__ . '/vendor/autoload.php';
echo '<div class="gv-body">';
function gv_registros_callback() {
    echo '<div class="gv-body">';
    echo '<h2>Registros</h2>';

    // Botón para exportar registros a PDF
    echo '<a href="' . admin_url('admin-post.php?action=generate_pdf') . '">Exportar Control de Versiones a PDF</a>';
    
    // Registro de Cambios
    $args = [
        'post_type' => 'page', 
        'posts_per_page' => -1
    ];
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            
            $versions = get_post_meta(get_the_ID(), '_gv_version');
            if (!empty($versions)) {
                echo '<h3>' . get_the_title() . '</h3>';
                echo '<ul class="gv-ul">';
                foreach ($versions as $version) {
                    echo '<li class="gv-li">';
                    echo 'Actualizado por ' . esc_html($version['user']) . ' el ' . esc_html($version['date']);
                    echo '</li>';
                }
                echo '</ul>';
            }
        }
        wp_reset_postdata();
    } else {
        echo 'No hay cambios registrados.';
    }
    echo '</div>'; 
}

function etdh_generate_pdf() {
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);

    ob_start(); // Empezamos la captura de la salida
    
    gv_registros_callback(); // Llamamos a la función para obtener los registros
    
    $content = ob_get_clean(); // Capturamos la salida en la variable $content

    $pdf->writeHTML($content);
    $pdf->Output('control_versiones.pdf', 'D');
    exit;
}
add_action('admin_post_generate_pdf', 'etdh_generate_pdf');

function gv_track_post_changes($post_id, $post, $update) {
    if (!$update) {
        return;
    }
    
    if ($post->post_type !== 'page') {
        return;
    }

    $user = wp_get_current_user();
    $current_time = current_time('mysql');

    add_post_meta($post_id, '_gv_version', [
        'user' => $user->user_login,
        'date' => $current_time,
    ]);
}
add_action('save_post', 'gv_track_post_changes', 10, 3);

echo '</div>'; 