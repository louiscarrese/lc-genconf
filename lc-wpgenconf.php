<?php
/**
 * Plugin Name: lc-wp_genconf
 * Description: A plugin configuration generator. It's only meant to be a dependency for other plugins.
 * Version: 1.0.0
 * Author: Louis Carrese
 * Author URI: http://louiscarrese.com
 * License: GPLv2 or later
 */
/** WP Hooks */
//Register javascript and CSS
add_action('admin_enqueue_scripts', 'lc_wpgenconf_scripts_register');
//Handle the Ajax callback to add a repeater section
add_action('wp_ajax_lc_genconf_add_repeater', 'lc_genconf_add_repeater');

/** Handle a form submission */
if(isset($_POST['lc_genconf_special_submit_form'])) {
  $filtered_values = lc_genconf_filter_actual_values($_POST);
  $filtered_values = lc_genconf_explode_repeated_values($filtered_values);
  lc_genconf_store_values($_POST['lc_genconf_special_conf_key'], $filtered_values);
}

/** Actual javascript and CSS registration */
function lc_wpgenconf_scripts_register() {
  wp_enqueue_style('lc_genconf_style', plugins_url('lc-wpgenconf.css', __FILE__));
  wp_enqueue_script('lc_genconf_script', plugins_url('lc-wpgenconf.js', __FILE__));
}

/** Ajax callback to add a repeater */
function lc_genconf_add_repeater() {
  $new_id = $_POST['lc_genconf_special_repeater_nextid'];
  $conf_key = $_POST['lc_genconf_special_conf_key'];
  
  $conf_def_function = $conf_key . '_conf_definition';
  $conf_def_function = str_replace('-', '_', $conf_def_function);
  
  echo lc_generate_configuration($conf_key, $conf_def_function(), $new_id);
  wp_die();
}


/**
 * Main function, generates HTML code, based on the configuration array
 */
function lc_wpgenconf($key, $conf_array, $echo = false) {
  //Get data in DB if any
  $stored_values = lc_genconf_retrieve_values($key);
  
  $html = '';
  //Start the configuration
  $html .= lc_genconf_start($key);
  
  //Title
  $html .= lc_genconf_title($conf_array);
  
  $first_free_id = 0;
  //If there are no stored values, display an empty one
  if(count($stored_values) == 0) {
    $html .= lc_generate_configuration($key, $conf_array, 0);
    $first_free_id = 1;
  } else {
    //Generate the configuration as stored
    foreach($stored_values as $id => $stored_value) {
      $html .= lc_generate_configuration($key, $conf_array, $id, $stored_value);
    }
    //Calculate the next id, for the repeater
    while(isset($stored_values[$first_free_id])){
      $first_free_id += 1;
    }
  }
  
  //End the configuration
  if($conf_array['repeater'] === true) {
    $html .= lc_genconf_end($first_free_id);
  } else {
    $html .= lc_genconf_end(null);
    
  }
  
  //Echo if needed
  if($echo) {
    echo $html;
  }

  return $html;
}

/**
 * Generate HTML for one repeater configuration.
 * TODO: $key might be useless
 */
function lc_generate_configuration($key, $conf_array, $id, $values_array = array()) {
  $html = '';

  //Start the repeater
  $html .= lc_genconf_start_repeater($id);
  
  //Iterate over sections
  foreach($conf_array['sections'] as $section_id => $section_conf) {
    $condition_value = null;
    
    //Start a section
    $html .= lc_genconf_start_section($section_id, $section_conf);
    
    //If there is a condition field, put it first
    //TODO: factorize with the general fields ?
    if(isset($section_conf['condition']) && is_array($section_conf['condition'])) {
      //Calculate the key that will be used in DB
      $db_key = $section_id . '_condition';
      //Compute the value based on the default value and the retrieved data
      $condition_value = lc_genconf_compute_value($db_key, $values_array, $section_conf['condition']);
      //Generate HTML
      $html .= lc_genconf_field($id . '_' . $db_key, $section_conf['condition'], $condition_value, null, true);
    }

    //Iterate over fields
    foreach($section_conf['fields'] as $field_id => $field_conf) {
      //Calculate the key that will be used in DB
      $db_key = $section_id . '_' . $field_id;
      //Compute the value based on the default value and the retrieved data
      $value = lc_genconf_compute_value($db_key, $values_array, $field_conf);
      //Generate HTML
      $html .= lc_genconf_field($id . '_' . $db_key, $field_conf, $value, $condition_value);
    }
    
    //End a section
    $html .= lc_genconf_end_section();
  }
  
  //End a repeater
  $html .= lc_genconf_end_repeater($conf_array['repeater']);
  
  return $html;
}

/** Compute the value of a field, based on the data retrieved from DB and the default value in conf */
function lc_genconf_compute_value($key, $values_array, $field_conf) {
  $value = null;
  if(isset($values_array[$key]) && $values_array[$key] != null) {
    $value = $values_array[$key];
  } else if(isset($field_conf['default_value'])) {
    $value = $field_conf['default_value'];
  }
  return $value;
}

/** Get the values in DB */
function lc_genconf_retrieve_values($key) {
  $array = json_decode(get_option($key), true);
  if(!is_array($array))
    $array = array();
  return $array;
}

/** Set the values in DB */
function lc_genconf_store_values($key, $array) {
  $out_array = array();
  
  foreach($array as $id => $values) {
    $out_array[$id] = lc_genconf_convert_booleans($values);
  }
  update_option($key, json_encode($out_array, JSON_NUMERIC_CHECK | JSON_FORCE_OBJECT));
}

/** Separate configuration values from technical values in the submitted form */
function lc_genconf_filter_actual_values($array) {
  $ret = array();
  foreach($array as $key => $value) {
    if(strpos($key, 'lc_genconf_special_') !== 0) {
      $ret[$key] = $value;
    }
  }
  return $ret;
}

/** Extract repeater informations from the submitted form */
function lc_genconf_explode_repeated_values($values) {
  $ret = array();
  foreach($values as $key => $value) {
    $id = substr($key, 0, strpos($key, '_'));
    $actual_key = substr($key, strpos($key, '_') + 1);
    if(!isset($ret[$id]) || !is_array($ret[$id])) {
      $ret[$id] = array();
    }
    $ret[$id][$actual_key] = $value;
  }
  return $ret;
}

/** Convert boolean values to string because */
function lc_genconf_convert_booleans($input_array) {
  $output_array = [];
  foreach($input_array as $input_key => $input_value) {
    $temp_value = $input_value;
    $temp_value = strtoupper($temp_value);
    if($temp_value === "TRUE") {
      $output_array[$input_key] = 'true';
    } else if($temp_value === "FALSE") {
      $output_array[$input_key] = 'false';
    } else {
      $output_array[$input_key] = $input_value;
    }
  }
  return $output_array;
}

/****************************************
 * Fonctions de génération du code HTML *
 ****************************************/
function lc_genconf_repeater($next_id) {
  $html = '';
  $html .= '<input type="hidden" id="lc_genconf_add_repeater_nextid" value="' . $next_id . '" />';
  $html .= '<input id="lc_genconf_special_addrepeater" class="button button-primary" type="submit" value="Ajouter" name="lc_genconf_special_addrepeater" onclick="lcAddRepeaterSection(); return false;" />';
  
  return $html;
}

function lc_genconf_start($key) {
  $html = '';
  $html .= '<div class="lc_genconf_wrap">';
  $html .= '<form id="lc_genconf_form" method="post" action="' . $_SERVER['REQUEST_URI'] .'">';
  //Sera utilisé pour savoir si on doit stocker des valeurs
  $html .= '<input type="hidden" id="lc_genconf_special_submit_form" name="lc_genconf_special_submit_form" value="true" />'; 
  //La clé sous laquelle on doit stocker la conf dans WP
  $html .= '<input type="hidden" id="lc_genconf_special_conf_key" name="lc_genconf_special_conf_key" value="' . $key . '" />';
  $html .= '<div id="lc_genconf_content">';
  return $html;
}

function lc_genconf_end($next_id) {
  $html = '';
  //TODO: Affichage du bouton submit
  $html .= '</div>';//lc_genconf_content
  $html .= '<p class="submit">';
  if($next_id !== null) {
    $html .= lc_genconf_repeater($next_id);
  }
  $html .= '<input id="lc_genconf_special_submit" class="button button-primary" type="submit" value="Enregistrer les modifications" name="lc_genconf_special_submit" onclick="return lcGenconfCheckboxes();" />';
  $html .= '</p>';
  $html .= '</form>';
  $html .= '</div>';
  return $html;
  
}

function lc_genconf_title($conf_array) {
  return '<h2>'.$conf_array['title'].'</h2>';
}

function lc_genconf_start_repeater($id) {
  $html = '';
  $html .= '<div class="lc_genconf_repeater">';
  $html .= '<input type="hidden" id="lc_genconf_special_id" name="lc_genconf_special_id" value="' . $id . '"/>';
  return $html;
}

function lc_genconf_end_repeater($repeater) {
  $html = '';
  
  if($repeater) {
    $html .= '<input id="lc_genconf_special_delrepeater" class="button button-primary" type="submit" value="Supprimer" name="lc_genconf_special_delrepeater" onclick="return lcDeleteRepeaterSection(this); return false;" />';
  }
  
  $html .= '</div>';
  return $html;
}

function lc_genconf_start_section($section_id, $section_conf) {
  $html = '';
  
  //TODO: Ouverture des éléments globaux à une section
  
  $html .= '<div class="lc_genconf_section" id="lc_genconf_section_' . $section_id . '">';
  $html .= '<div class="lc_genconf_section_title">';
  $html .= '<span>' . $section_conf['title'] . '</span>';
  $html .= '</div>';
  $html .= '<div class="lc_genconf_section_content">';
  $html .= '<table>';
  
  return $html;
}

function lc_genconf_end_section() {
  $html = '';
  
  $html .= '</table>';
  $html .= '</div>';//lc_genconf_section_content
  $html .= '<div class="lc_genconf_spacer">&nbsp;</div>';
  $html .= '</div>';//lc_genconf_section
  
  return $html;
}

function lc_genconf_field($field_id, $field_conf, $value, $condition_value, $is_condition = false) {
  $html = '';
  
  $html .= lc_genconf_field_start($field_conf, $condition_value);
  
  switch ($field_conf['type']) {
  case 'checkbox':
    $html .= lc_genconf_field_checkbox($field_id, $field_conf, $value, $condition_value, $is_condition);
    break;
  case 'text':
    $html .= lc_genconf_field_text($field_id, $field_conf, $value, $condition_value, $is_condition);
    break;
  case 'category':
    $html .= lc_genconf_field_category($field_id, $field_conf, $value, $condition_value, $is_condition);
    break;
  case 'dropdown':
    $html .= lc_genconf_field_dropdown($field_id, $field_conf, $value, $condition_value, $is_condition);
    break;
  }
  
  if(isset($field_conf['explanation'])) {
    $html.= lc_genconf_field_explanation($field_conf['explanation']);
  }
  
  $html .= lc_genconf_field_end();
  
  return $html;
}

function lc_genconf_field_start($field_conf, $condition_value) {
  $html = '';
  
  $html .= '<tr ';
  if(isset($field_conf['condition'])) {
    $html .= 'data-condition="' . $field_conf['condition'] . '"';
  }
  
  if(!isset($field_conf['condition']) 
     || $field_conf['condition'] == "" 
     || $field_conf['condition'] == $condition_value) {
    $html .= '>';
  } else {
    $html .= ' class=" lc_genconf_hidden">';
  }
  
  return $html;
  
}

function lc_genconf_field_explanation($explanation) {
  $html = '';
  
  if($explanation != null) {
    $html .= '<tr><td colspan="100"><span class="lc_genconf_explanation">';
    $html .= $explanation;
    $html .= '</span></td></tr>';
  }
  
  return $html;
}

function lc_genconf_field_end() {
  $html = '';
  
  $html .= '</tr>';
  
  return $html;
}

function lc_genconf_field_checkbox($field_id, $field_conf, $value, $condition_value, $is_condition) {
  $html = '';
  
  $html .= '<td colspan="100">';
  $html .= '<label for="' . $field_id . '">';
  $html .= '<input type="checkbox"';
  $html .= ' id="' . $field_id . '"';
  $html .= ' name=lc_genconf_special_"' . $field_id . '"';
  $html .= ' value="true"';
  if($value == 'true')
    $html .= ' checked="checked"';
  if($is_condition)
    $html .= ' onchange="lcGenconfCheckCondition(this);"';
  $html .= '>';
  $html .= $field_conf['label'];
  $html .= '</input>';
  $html .= '</label>';
  $html .= '<input type="hidden" id="lc_genconf_hidden_' . $field_id . '" name="' . $field_id . '" />';
  $html .= '<td>';
  return $html;
}

function lc_genconf_field_text($field_id, $field_conf, $value, $condition_value, $is_condition) {
  $html = '';
  
  if(isset($field_conf['label'])) {
    $html .= '<td>' . $field_conf['label'] . '</td>';
  }
  $html .= '<td>';
  $html .= '<input type="text"';
  $html .= ' id="' . $field_id . '"';
  $html .= ' name="' . $field_id . '"';
  $html .= ' value="' . $value . '"';
  if($is_condition)
    $html .= ' onchange="lcGenconfCheckCondition(this);"';
  $html .= '/>';
  $html .= '</td>';
  
  return $html;
}

function lc_genconf_field_category($field_id, $field_conf, $value, $condition_value, $is_condition) {
  $html = '';
  if(isset($field_conf['label'])) {
    $html .= '<td>' . $field_conf['label'] . '</td>';
  }
  $html .= '<td>';
  $html .= wp_dropdown_categories(array('hide_empty' => 0, 'name' => $field_id, 'id' => $field_id, 'hierarchical' => true, 'echo' => 0, 'selected' => $value));
  $html .= '</td>';
  
  return $html;
}

function lc_genconf_field_dropdown($field_id, $field_conf, $value, $condition_value, $is_condition) {
  $html = '';
  
  if(isset($field_conf['label'])) {
    $html .= '<td>' . $field_conf['label'] . '</td>';
  }
  
  $html .= '<td>';
  $html .= '<select';
  $html .= ' name="' . $field_id . '"';
  $html .= ' id="' . $field_id . '"';
  if($is_condition == true) 
    $html .= ' onchange="lcGenconfCheckCondition(this);"';
  $html .= '>';
  foreach($field_conf['values'] as $field_value => $field_label) {
    $html .= '<option value="' . $field_value . '" ';
    if($field_value == $value) {
      $html .= 'selected';
    }
    $html .= '>' . $field_label . '</option>';
  }
  
  $html .= '</input>';
  $html .= '</td>';
  
  return $html;
}

?>
