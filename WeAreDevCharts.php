<?php
/**
* Plugin Name: WeAreDev Charts
* Plugin URI: http://wearedev.io/charts
* Description: The easiest way to create beautiful charts
* Version: 1.0.1
* Author: WeAreDev by Roy Scheeren & Jeroen Branje
* Author URI: http://wearedev.io
*/


/**
 * The main function responsible for returning the one true instance to functions everywhere.
 */
function WeAreDevCharts() {
  // Load the main class
  require_once __DIR__ . '/WeAreDevCharts/WeAreDevCharts.php';
}

// Initialize the plugin
WeAreDevCharts();
