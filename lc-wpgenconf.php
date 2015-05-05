<?php
/**
 * Plugin Name: lc-wp_genconf
 * Description: A plugin configuration generator. It's only meant to be a dependency for other plugins.
 * Version: 1.0.0
 * Author: Louis Carrese
 * Author URI: http://louiscarrese.com
 * License: GPLv2 or later
 */

require_once('classes/Configuration.php');

/** WP Hooks */
//Register javascript and CSS
add_action('admin_enqueue_scripts', 'lc_wpgenconf_scripts_register');
//Handle the Ajax callback to add a repeater section
add_action('wp_ajax_lc_genconf_add_repeater', 'lc_genconf_add_repeater');
add_action('wp_ajax_lc_genconf_submit_form', 'lc_genconf_submit_form');


/** Actual javascript and CSS registration */
function lc_wpgenconf_scripts_register() {
  wp_enqueue_style('lc_genconf_style', plugins_url('lc-wpgenconf.css', __FILE__));
  wp_enqueue_script('lc_genconf_script', plugins_url('lc-wpgenconf.js', __FILE__));
}

/** Simplified entry point for the users */
function lc_wpgenconf($wp_key) {
  //generate the configuration definition function
  $conf_def_function = $wp_key . '_conf_definition';
  $conf_def_function = str_replace('-', '_', $conf_def_function);

  //Instantiate the configuration
  $configuration = new LcWpGenconf\Configuration($wp_key, $conf_def_function());

  return $configuration->toHTML();
}

/** Ajax callback to save the configuration */
function lc_genconf_submit_form() {
  //Retrieve the configuration key
  $conf_key = $_POST['conf_key'];

  //generate the configuration definition function
  $conf_def_function = $conf_key . '_conf_definition';
  $conf_def_function = str_replace('-', '_', $conf_def_function);

  //Remove the technical keys from the data
  unset($_POST['conf_key']);
  unset($_POST['action']);

  //Instantiate the configuration
  $configuration = new LcWpGenconf\Configuration($conf_key, $conf_def_function());
  //Save it
  $configuration->save($_POST);

  //Exit, the wp way
  wp_die();
}

/** Ajax callback to add a repeater */
function lc_genconf_add_repeater() {
  //Retrieve data from the form
  $new_id = $_POST['lc_genconf_special_repeater_nextid'];
  $conf_key = $_POST['lc_genconf_special_conf_key'];
  
  //Generate the configuration definition function name
  $conf_def_function = $conf_key . '_conf_definition';
  $conf_def_function = str_replace('-', '_', $conf_def_function);

  //Instantiate the configuration
  $configuration = new LcWpGenconf\Configuration($conf_key, $conf_def_function());
  //Generate the new repeater 
  echo $configuration->emptyRepeaterHTML($new_id);

  //Exit, the wp way
  wp_die();
}



?>
