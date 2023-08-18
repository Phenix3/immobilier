/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */
require('./jquery-migrate-3.0.0');
require('./plugins');
require('./classy-nav.min');
require('./jquery-ui.min');

// import {Flipper} from 'flip-toolkit';

const noUiSlider = require('./nouislider.min');
// require('./wow.min');
// require('./map-active');
require('./active');
require('../scss/app.scss');

import AjaxFilter from './modules/AjaxFilter';

new AjaxFilter(document.querySelector('.js-filter'));


const $slider = document.querySelector('.price-slider');

if ($slider)
{
    const minVal = Math.floor(parseInt($slider.dataset.min, 10) / 10) * 10;
    const maxVal = Math.ceil(parseInt($slider.dataset.max, 10) / 10) * 10;
    const min = document.querySelector('#minPrice');
    const max = document.querySelector('#maxPrice');

    const range = noUiSlider.create($slider, {
        start: [min.value || minVal, max.value || maxVal],
        connect: true,
        range: {
            min: minVal,
            max: maxVal
        }
    });

    range.on('slide', (values, handle) => {
        if (handle === 0) {
            min.value = parseInt(Math.round(values[0]));
        }
        if (handle === 1) {
            max.value = parseInt(Math.round(values[1]));
        }

        
    });

    range.on('end', (values, handle) => {
        min.dispatchEvent(new Event('change'));
    });
}


