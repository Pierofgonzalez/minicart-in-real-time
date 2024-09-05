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

    // Actualizar carrito cuando se agrega un producto
    $(document.body).on('added_to_cart', function() {
        actualizarMiniCarrito(); // Llamar a la función de actualización
    });

    // Actualizar carrito cuando se cambia la cantidad de un producto
    $(document.body).on('wc_fragment_refresh updated_wc_div', function() {
        actualizarMiniCarrito(); // Llamar a la función de actualización
    });

    // Actualizar carrito cuando se elimina un producto
    $(document.body).on('removed_from_cart', function() {
        actualizarMiniCarrito(); // Llamar a la función de actualización
    });

    // Evento personalizado para aumentar la cantidad del producto en el carrito
    $(document).on('click', '.wc-block-components-quantity-selector__button--plus', function() {
        actualizarMiniCarrito();
    });

    // Evento personalizado para reducir la cantidad del producto en el carrito
    $(document).on('click', '.wc-block-components-quantity-selector__button--minus', function() {
        actualizarMiniCarrito();
    });

    // Evento personalizado para eliminar productos del carrito
    $(document).on('click', '.wc-block-cart-item__remove-link', function() {
        actualizarMiniCarrito();
    });

    // Asegurar que el icono del carrito se mantenga junto al icono de búsqueda en móviles
    $('.et_mobile_menu li a i.et-pb-icon').each(function() {
        if ($(this).text() === '') { // Carácter Unicode del icono del carrito
            $(this).closest('a').insertAfter('.et_search_outer');
        }
    });
});
