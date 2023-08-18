
window.$ = window.jQuery = require('jquery');
window.popper = require('popper.js').default;
require('bootstrap');

// Custom Element imports
import './elements/Alert';

require('./../scss/global.scss');

// noUiSlider
require('./nouislider.min');