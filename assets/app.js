/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// start the Stimulus application
import './bootstrap';

// Font Awesome
import '@fortawesome/fontawesome-free/css/all.css';
import '@fortawesome/fontawesome-free/js/all.js';

// require jQuery normally
const $ = require('jquery');
global.$ = global.jQuery = $;

//
import './js/search';

//
import './styles/sections.css';
import './styles/items.css';
import './styles/progress_bar.css';
import './styles/search.css';

// by page type
import './styles/volume.css';
