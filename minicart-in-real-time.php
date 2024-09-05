<?php
/*
Plugin Name: Mini Cart in Real Time
Description: Actualiza el mini carrito en tiempo real y maneja la interacción con el tema Divi.
Version: 1.0
Author: Pierofgonzalez
*/

// Seguridad: evitar el acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Hook para inicializar el plugin
add_action('plugins_loaded', 'mcrt_init_plugin');

function mcrt_init_plugin() {
    // Comprueba si existe un tema hijo
    if (is_child_theme()) {
        // Verifica y crea archivos necesarios
        mcrt_check_and_create_files();
    }

    // Encola los scripts y estilos
    add_action('wp_enqueue_scripts', 'mcrt_enqueue_scripts');
    add_action('wp_ajax_actualizar_carrito', 'mcrt_actualizar_carrito');
    add_action('wp_ajax_nopriv_actualizar_carrito', 'mcrt_actualizar_carrito');
}

function mcrt_check_and_create_files() {
    // Directorio del tema hijo
    $child_theme_dir = get_stylesheet_directory();
    
    // Verifica si existe el archivo functions.php
    if (!file_exists($child_theme_dir . '/functions.php')) {
        file_put_contents($child_theme_dir . '/functions.php', "<?php\n");
    }

    // Verifica si existe la carpeta CSS
    if (!file_exists($child_theme_dir . '/custom.css')) {
        file_put_contents($child_theme_dir . '/custom.css', "/* Custom CSS */\n");
    }

    // Verifica si existe el archivo custom.js
    if (!file_exists($child_theme_dir . '/custom.js')) {
        file_put_contents($child_theme_dir . '/custom.js', "// Custom JS\n");
    }
}

function mcrt_enqueue_scripts() {
    $theme_dir = is_child_theme() ? get_stylesheet_directory_uri() : get_template_directory_uri();

    // Encola el CSS
    wp_enqueue_style('mcrt-custom-css', $theme_dir . '/custom.css');

    // Encola el JS
    wp_enqueue_script('mcrt-custom-js', $theme_dir . '/custom.js', array('jquery'), null, true);

    // Localiza el script para AJAX
    wp_localize_script('mcrt-custom-js', 'carritoAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mcrt_nonce')
    ));
}

function mcrt_actualizar_carrito() {
    check_ajax_referer('mcrt_nonce', 'nonce');

    // Obtener el número de productos en el carrito
    $cart_count = WC()->cart->get_cart_contents_count();

    wp_send_json($cart_count);
}
