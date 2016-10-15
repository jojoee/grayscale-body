<?php
/*
Plugin Name: Grayscale Body
Plugin URI: https://wordpress.org/plugins/grayscale-body/
Description: Automatically turn the site to grayscale
Version: 1.0.1
Author: Nathachai Thongniran
Author URI: http://jojoee.com/
Text Domain: gsb
License: GPL2+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

function gsb_enqueue_style() {
  wp_enqueue_style( 'gsb-main', plugins_url( 'css/main.css', __FILE__ ) );
}

add_action( 'wp_enqueue_scripts', 'gsb_enqueue_style' );
