<?php
/*
Plugin Name: ElemTasks Designer Hub (Alpha)
Description: Plugin para gestionar versiones y tareas relacionadas con el desarrollo de páginas en Elementor.
Version: 1.0
Author: Judith Barril Navarro (sitetive.com)
Author URI: https://sitetive.com/
*/

include plugin_dir_path( __FILE__ ) . 'gestor-de-tareas.php';
include plugin_dir_path( __FILE__ ) . 'registros.php';
function etdh_enqueue_styles() {
    wp_register_style('etdh_styles', plugins_url('assets/css/style.css', __FILE__));
    wp_enqueue_style('etdh_styles');
}
add_action('admin_enqueue_scripts', 'etdh_enqueue_styles');


function gv_register_elementask_post_type()
{
    $args = array(
        'public' => true,
        'label' => 'ElemenTask',
        'show_in_menu' => true,
        'show_ui' => true,
        'menu_position' => 2,
        'menu_icon' => 'dashicons-welcome-view-site',


    );
    register_post_type('elementask', $args);
}
add_action('init', 'gv_register_elementask_post_type');

function gv_elementask_submenu_pages()
{
    add_submenu_page(
        'edit.php?post_type=elementask',
        'Gestor de Tareas',
        'Gestor de Tareas',
        'manage_options',
        'gestor_tareas',
        'gv_display_tareas',
    );

    add_submenu_page(
        'edit.php?post_type=elementask',
        'Registros',
        'Registros',
        'manage_options',
        'registros',
        'gv_registros_callback'
    );
}
add_action('admin_menu', 'gv_elementask_submenu_pages');
function gv_remove_default_submenu() {
    remove_submenu_page('edit.php?post_type=elementask', 'edit.php?post_type=elementask');
    remove_submenu_page('edit.php?post_type=elementask', 'post-new.php?post_type=elementask');
}
add_action('admin_menu', 'gv_remove_default_submenu', 11);
