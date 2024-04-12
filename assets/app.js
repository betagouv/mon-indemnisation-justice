import './bootstrap.js';

/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

const $ = require('jquery');
window.jQuery = $;
window.$ = $;

const routes = require('../public/js/fos_js_routes.json');
import Routing from '../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min';
Routing.setRoutingData(routes);
window.Routing = Routing;

$(document).ready(() => {});
