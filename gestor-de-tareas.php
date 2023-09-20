<?php
function gv_register_custom_elements()
{
    register_post_type('gv_tarea', [
        'label' => 'Tareas',
        'public' => true,
        'show_in_menu' => false,
        'supports' => ['title'],
        'labels' => [
            'add_new' => 'Añadir tarea',
        ]
    ]);

    register_taxonomy('gv_prioridad', 'gv_tarea', [
        'label' => 'Prioridad',
        'public' => true,
        'show_admin_column' => true,
        'hierarchical' => false,
        'labels' => [
            'add_new_item' => 'Añadir Nueva Prioridad',
        ],
    ]);

    register_taxonomy('gv_equipo', 'gv_tarea', [
        'label' => 'Equipo',
        'public' => true,
        'show_admin_column' => true,
        'hierarchical' => false,
        'labels' => [
            'add_new_item' => 'Añadir Nuevo Equipo',
        ],
    ]);

}
add_action('init', 'gv_register_custom_elements');

function gv_display_tareas()
{
    $args = [
        'post_type' => 'gv_tarea',
        'posts_per_page' => -1
    ];
    $tareas = new WP_Query($args);
    echo '<div class="gv-body">';
    echo '<h2>Gestor de Tareas</h2>';

    echo '<form method="post" class="gv-form">';

    echo '<label for="gv_nombre_tarea">Nombre de la tarea: </label>';
    echo '<input type="text" id="gv_nombre_tarea" name="gv_nombre_tarea" required>';

    echo '<label for="gv_prioridad">Prioridad: </label>';
    echo '<select id="gv_prioridad" name="gv_prioridad">';
    echo '<option value="alta">Alta</option>';
    echo '<option value="media">Media</option>';
    echo '<option value="baja">Baja</option>';
    echo '</select>';

    echo '<label for="gv_equipo">Equipo: </label>';
    echo '<select id="gv_equipo" name="gv_equipo">';
    echo '<option value="diseno">Diseño</option>';
    echo '<option value="desarrollo">Desarrollo</option>';
    echo '<option value="seo">SEO</option>';
    echo '<option value="marketing">Marketing</option>';
    echo '<option value="stakeholder">Stakeholder</option>';
    echo '</select>';

    wp_nonce_field('gv_add_tarea_action', 'gv_add_tarea_nonce');
    echo '<input type="submit" value="Añadir Tarea" class="gv-button">';
    echo '</form>';

    echo '<form method="post">';
    echo '<table class="gv-table" border="1">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Seleccionar</th>';
    echo '<th>Nombre</th>';
    echo '<th>Equipo</th>';
    echo '<th>Prioridad</th>';
    echo '<th>Completado por</th>';
    echo '<th>Acciones</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    if ($tareas->have_posts()) {
        while ($tareas->have_posts()) {
            $tareas->the_post();

            $equipo_terms = wp_get_post_terms(get_the_ID(), 'gv_equipo');
            if (is_wp_error($equipo_terms)) {
                $equipo_terms = [];
            }

            $prioridad_terms = wp_get_post_terms(get_the_ID(), 'gv_prioridad');
            if (is_wp_error($prioridad_terms)) {
                $prioridad_terms = [];
            }
            $completado_por = get_post_meta(get_the_ID(), '_usuario_completado', true);

            echo '<tr>';
            echo '<td><input type="checkbox" class="gv-select-tarea" name="gv_selected_tareas[]" value="' . get_the_ID() . '"></td>';
            echo '<td>' . get_the_title() . '</td>';
            echo '<td>' . (isset($equipo_terms[0]) ? $equipo_terms[0]->name : '') . '</td>';
            echo '<td>' . (isset($prioridad_terms[0]) ? $prioridad_terms[0]->name : '') . '</td>';
            echo '<td>' . esc_html($completado_por) . '</td>';
            echo '<td><button class="gv-completar-tarea gv-btn gv-button-table" data-id="' . get_the_ID() . '">Completar</button></td>';
            echo '</tr>';
        }
    } else {
        echo '<tr>';
        echo '<td colspan="5">No hay tareas disponibles.</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '<input type="submit" name="gv_delete_tareas" class="gv-btn gv-button-delete" value="Borrar Tareas Seleccionadas">';
    echo '</form>';

    echo '</div>'; 
    // Añade aquí la variable nonce para ser usada en JS:
    wp_nonce_field('gv-nonce', 'gv-nonce-field');
}

function gv_handle_add_tarea()
{
    if (isset($_POST['gv_add_tarea_nonce']) && wp_verify_nonce($_POST['gv_add_tarea_nonce'], 'gv_add_tarea_action')) {
        $tarea_nombre = sanitize_text_field($_POST['gv_nombre_tarea']);
        $tarea_prioridad = sanitize_text_field($_POST['gv_prioridad']);
        $tarea_equipo = sanitize_text_field($_POST['gv_equipo']);

        $post_id = wp_insert_post([
            'post_title' => $tarea_nombre,
            'post_type' => 'gv_tarea',
            'post_status' => 'publish',
        ]);

        if ($post_id) {
            wp_set_object_terms($post_id, $tarea_prioridad, 'gv_prioridad');
            wp_set_object_terms($post_id, $tarea_equipo, 'gv_equipo');
        }
    }

    if (isset($_POST['gv_delete_tareas']) && !empty($_POST['gv_selected_tareas'])) {
        foreach ($_POST['gv_selected_tareas'] as $tarea_id) {
            wp_delete_post($tarea_id, true);
        }
    }
}
add_action('admin_init', 'gv_handle_add_tarea');

// Función para manejar la tarea completada mediante AJAX
function gv_completar_tarea_callback()
{
    check_ajax_referer('gv-nonce', 'nonce'); // Esta es la validación del nonce.

    // Aquí tu lógica para completar la tarea...
    // Por ejemplo, actualizar un post meta o cambiar el estado del post.

    if (isset($_POST['post_id'])) {
        $post_id = sanitize_text_field($_POST['post_id']);

        // Obtener el usuario actual
        $current_user = wp_get_current_user();

        if ($current_user && isset($current_user->user_login)) {
            // Actualizar el metadato del post con el nombre de usuario que ha completado la tarea
            update_post_meta($post_id, '_usuario_completado', $current_user->user_login);

            echo 'Tarea completada por ' . $current_user->user_login;
        } else {
            echo 'Error al completar la tarea.';
        }
    } else {
        echo 'No se proporcionó una tarea válida.';
    }

    wp_die(); // Esto es necesario al final de una función de callback AJAX.
}
add_action('wp_ajax_gv_completar_tarea', 'gv_completar_tarea_callback');
function gv_enqueue_admin_scripts()
{
    wp_enqueue_script('gv-admin-js', plugin_dir_url(__FILE__) . 'assets/js/admin.js', [], '1.0.0', true);

    // Pasamos variables a JavaScript
    wp_localize_script('gv-admin-js', 'gv_vars', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('gv-nonce'),
    ]);
}
add_action('admin_enqueue_scripts', 'gv_enqueue_admin_scripts');
