/**
 * productbadges.js
 */

(function ($) {
    'use strict';

    function moveBadgeToProductImage($wrapper) {
        var $miniature = $wrapper.closest('.product-miniature');

        if ($miniature.length) {
            var $thumbnail = $miniature.find('.thumbnail-container').first();

            if ($thumbnail.length) {
                $thumbnail.css('position', 'relative');
                $thumbnail.prepend($wrapper);
                return;
            }
        }

        var $productCover = $('.product-cover').first();

        if ($productCover.length) {
            $productCover.css('position', 'relative');
            $productCover.prepend($wrapper);
            return;
        }

        var $parent = $wrapper.parent();

        if ($parent.css('position') === 'static') {
            $parent.css('position', 'relative');
        }
    }

    $(document).ready(function () {
        $('.productbadges-wrapper').each(function () {
            moveBadgeToProductImage($(this));
        });
    });

}(jQuery));