/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';
import {Collapse} from "bootstrap";
import Routing from 'fos-router';
import {Carousel} from "bootstrap";
import {Modal} from "bootstrap";
import {Chart} from "chart.js";
import {Star} from "bootstrap-star-rating"
import {Toast} from "bootstrap";
import toast from "bootstrap/js/src/toast";
import scrollspy from "bootstrap/js/src/scrollspy";
// start the Stimulus application
import './bootstrap';