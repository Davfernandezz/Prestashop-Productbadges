/**
 * productbadges.js
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        // Aseguramos que el contenedor padre de las imágenes
        // tenga position:relative para que los badges se posicionen bien.
        // El CSS ya lo hace para .product-thumbnail y .product-cover,
        // pero algunos temas custom pueden necesitar este fallback.
        $('.productbadges-wrapper').each(function () {
            var $parent = $(this).parent();
            if ($parent.css('position') === 'static') {
                $parent.css('position', 'relative');
            }
        });
    });

}(jQuery));
