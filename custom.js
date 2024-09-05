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
                // Actualiza el número de elementos en el carrito (asumiendo que el carrito tiene un span en #et-secondary-menu)
                $('#et-secondary-menu .et-cart-info span').text(response + ' elementos');
            },
            error: function(xhr, status, error) {
                console.error('Error al actualizar el carrito:', error);
            }
        });
    }

    // Evento: Cuando se agrega un producto al carrito
    $(document.body).on('added_to_cart', function() {
        actualizarMiniCarrito(); // Llamar a la función de actualización
    });

    // Evento: Cuando se cambia la cantidad de un producto (botón + y -)
    $(document).on('click', '.wc-block-components-quantity-selector__button--plus, .wc-block-components-quantity-selector__button--minus', function() {
        setTimeout(function() {
            actualizarMiniCarrito();
        }, 500); // Retraso para que la actualización de cantidad se procese primero
    });

    // Evento: Cuando se elimina un producto del carrito
    $(document).on('click', '.wc-block-cart-item__remove-link', function(e) {
        e.preventDefault(); // Previene el comportamiento por defecto
        var $removeBtn = $(this);
        
        // Lógica para eliminar el producto
        $.ajax({
            url: $removeBtn.attr('href'),
            type: 'POST',
            success: function() {
                actualizarMiniCarrito(); // Actualizar el carrito después de eliminar el producto
            },
            error: function(xhr, status, error) {
                console.error('Error al eliminar el producto del carrito:', error);
            }
        });
    });

    // Evento: Asegurar que el icono del carrito se mantenga junto al icono de búsqueda en móviles
    $('.et_mobile_menu li a i.et-pb-icon').each(function() {
        if ($(this).text() === '') { // Carácter Unicode del icono del carrito
            $(this).closest('a').insertAfter('.et_search_outer');
        }
    });
});
