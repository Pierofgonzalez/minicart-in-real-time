<?php
/*
Plugin Name: Mini Cart in Real Time
Description: Actualiza el mini carrito en tiempo real y maneja la interacción con el tema Divi.
Version: 1.0
Author:Pierofgonzalez
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

    // AJAX para actualizar carrito
    add_action('wp_ajax_actualizar_carrito', 'mcrt_actualizar_carrito');
    add_action('wp_ajax_nopriv_actualizar_carrito', 'mcrt_actualizar_carrito');
}

function mcrt_check_and_create_files() {
    // Directorio del tema hijo
    $child_theme_dir = get_stylesheet_directory();

    // Verifica si existe el archivo functions.php
    $functions_php_path = $child_theme_dir . '/functions.php';
    if (!file_exists($functions_php_path)) {
        file_put_contents($functions_php_path, "<?php\n");
    }

    // Añadir el código proporcionado al archivo functions.php
    $functions_code = <<<'EOD'

/* Add "Add to Cart" buttons in Divi shop pages */
add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 20 );

function woocommerce_breadcrumb_shortcode() {
    ob_start();
    if ( function_exists('woocommerce_breadcrumb') ) {
        woocommerce_breadcrumb();
    }
    return ob_get_clean();
}
add_shortcode('woocommerce_breadcrumb', 'woocommerce_breadcrumb_shortcode');

/// Función para obtener el número de elementos en el carrito
function actualizar_numero_elementos_carrito() {
    $numero_elementos = WC()->cart->get_cart_contents_count();
    wp_send_json( $numero_elementos );
    wp_die(); // Terminar la ejecución del script
}

// Registrar la acción AJAX para usuarios autenticados y no autenticados
add_action( 'wp_ajax_actualizar_carrito', 'actualizar_numero_elementos_carrito' );
add_action( 'wp_ajax_nopriv_actualizar_carrito', 'actualizar_numero_elementos_carrito' );

// Encolar el script AJAX
function encolar_script_actualizar_carrito() {
    wp_enqueue_script( 'jquery' );

    wp_localize_script( 'jquery', 'carritoAjax', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'actualizar_carrito_nonce' )
    ));
}
add_action('wp_enqueue_scripts', 'encolar_script_actualizar_carrito');
EOD;

    // Agregar el código al functions.php si no existe ya
    $existing_code = file_get_contents($functions_php_path);
    if (strpos($existing_code, 'woocommerce_after_shop_loop_item') === false) {
        file_put_contents($functions_php_path, $functions_code, FILE_APPEND);
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
