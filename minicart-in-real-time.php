<?php
/*
Plugin Name: Mini Cart in Real Time
Description: Actualiza el mini carrito en tiempo real y maneja la interacción con el tema Divi.
Version: 1.0
Author: Tu Nombre
*/

// Seguridad: evitar el acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Hook que se ejecuta al activar el plugin
register_activation_hook(__FILE__, 'mcrt_plugin_activation');

function mcrt_plugin_activation() {
    // Comprueba si existe un tema hijo y selecciona el directorio adecuado
    $theme_dir = is_child_theme() ? get_stylesheet_directory() : get_template_directory();

    // Verifica y crea/actualiza el functions.php necesario
    mcrt_check_and_create_files($theme_dir);
}

function mcrt_check_and_create_files($theme_dir) {
    // Ruta al archivo functions.php
    $functions_php_path = $theme_dir . '/functions.php';
    
    // Si no existe, crea el archivo functions.php con la apertura de PHP
    if (!file_exists($functions_php_path)) {
        file_put_contents($functions_php_path, "<?php\n");
    }

    // Código que se va a insertar en functions.php
    $functions_code = <<<'EOD'

/* Añadir botones "Añadir al carrito" en las páginas de la tienda */
add_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 20);

/* Shortcode para mostrar las migas de pan de WooCommerce */
function woocommerce_breadcrumb_shortcode() {
    ob_start();
    if (function_exists('woocommerce_breadcrumb')) {
        woocommerce_breadcrumb();
    }
    return ob_get_clean();
}
add_shortcode('woocommerce_breadcrumb', 'woocommerce_breadcrumb_shortcode');

/* Función para obtener el número de elementos en el carrito */
function actualizar_numero_elementos_carrito() {
    $numero_elementos = WC()->cart->get_cart_contents_count();
    wp_send_json($numero_elementos);
    wp_die(); // Terminar la ejecución del script
}

/* Registrar la acción AJAX para actualizar el número de elementos en el carrito */
add_action('wp_ajax_actualizar_carrito', 'actualizar_numero_elementos_carrito');
add_action('wp_ajax_nopriv_actualizar_carrito', 'actualizar_numero_elementos_carrito');

/* Encolar el script AJAX para actualizar el carrito en tiempo real */
function encolar_script_actualizar_carrito() {
    wp_enqueue_script('jquery');

    wp_localize_script('jquery', 'carritoAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('actualizar_carrito_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'encolar_script_actualizar_carrito');

EOD;

    // Agregar el código al functions.php si no existe ya
    $existing_code = file_get_contents($functions_php_path);
    if (strpos($existing_code, 'woocommerce_after_shop_loop_item') === false) {
        file_put_contents($functions_php_path, $functions_code, FILE_APPEND);
    }
}

// Encola los scripts y estilos del plugin
add_action('wp_enqueue_scripts', 'mcrt_enqueue_scripts');

function mcrt_enqueue_scripts() {
    // Determina el directorio del tema (hijo o padre)
    $theme_dir = is_child_theme() ? get_stylesheet_directory_uri() : get_template_directory_uri();

    // Encola el CSS
    wp_enqueue_style('mcrt-custom-css', plugin_dir_url(__FILE__) . 'custom.css');

    // Encola el JS
    wp_enqueue_script('mcrt-custom-js', plugin_dir_url(__FILE__) . 'custom.js', array('jquery'), null, true);

    // Localiza el script para AJAX
    wp_localize_script('mcrt-custom-js', 'carritoAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('mcrt_nonce')
    ));
}

function mcrt_actualizar_carrito() {
    // Verifica el nonce de seguridad antes de procesar la solicitud
    check_ajax_referer('mcrt_nonce', 'nonce');

    // Obtener el número de productos en el carrito
    $cart_count = WC()->cart->get_cart_contents_count();

    wp_send_json($cart_count);
}
