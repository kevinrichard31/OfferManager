define([
    'jquery',
    'slick'
], function ($) {
    'use strict';

    return function (config, element) {
        var $slider = $(element);

        $slider.slick({
            dots: false,
            infinite: true,
            speed: 300,
            slidesToShow: 1,
            adaptiveHeight: true
        });

        // Affiche le slider après l'initialisation pour éviter la superposition
        $slider.closest('.offer-banner-container').removeClass('hidden-offer-banner');

    };
});
