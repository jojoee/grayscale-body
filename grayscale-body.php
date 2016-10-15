<?php
/*
Plugin Name: Grayscale Body
Plugin URI: https://wordpress.org/plugins/grayscale-body/
Description: Automatically turn the site to grayscale
Version: 1.1.1
Author: Nathachai Thongniran
Author URI: http://jojoee.com/
Text Domain: gsb
License: GPL2+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

function gsb_enqueue_style() {
  wp_enqueue_style( 'gsb-main-style', plugins_url( 'css/main.css', __FILE__ ) );
  wp_enqueue_script( 'gsb-main-script', plugins_url('js/main.js', __FILE__), array(), '111', true);
}

add_action( 'wp_enqueue_scripts', 'gsb_enqueue_style' );
