jQuery(document).ready(function($) {
    // Asegura que el ícono del carrito se mantenga junto al ícono de búsqueda en móviles
    $('.et_mobile_menu li a i.et-pb-icon').each(function() {
        if ($(this).text() === '') { // Carácter Unicode del icono del carrito
            $(this).closest('a').insertAfter('.et_search_outer');
        }
    });
});

jQuery(document).ready(function($) {
    // Función para actualizar el mini carrito
    function actualizarMiniCarrito() {
        $.ajax({
            url: carritoAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'actualizar_carrito',
                nonce: carritoAjax.nonce
            },
            success: function(response) {
                $('#et-secondary-menu .et-cart-info span').text(response + ' elementos');
            }
        });
    }

    // Escuchar el evento de agregado al carrito de WooCommerce
    $(document.body).on('added_to_cart', function() {
        actualizarMiniCarrito(); // Llamar a la función de actualización
    });
});
