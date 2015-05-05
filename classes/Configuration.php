<?php 
namespace LcWpGenconf;

require_once('ConfigurationDefinition.php');


/**
 * This class is the main object to be used by plugins.
 * It instantiate the ConfigurationDefinition and holds 
 * the methods to display, load and save the configuration. 
 */
class Configuration {

  /** The key used in the wp-options table */
  private $wpKey;

  /** The definition of the configuration (ConfigurationDefinition) */
  private $confDef;

  /**
   * Constructor.
   * @param $wpKey string The identifier of the configuration to be 
   * used as a key to the wp_options table.
   * @param $conf_array array The array describing the configuration (see doc).
   */
  public function __construct($wpKey, $conf_array) {
    $this->confDef = new ConfigurationDefinition($conf_array);
    $this->wpKey = $wpKey;
  }

  /**
   * Generates the HTML fragment of the configuration.
   * @return string The HTML code for the whole configuration page, with 
   * values embedded if any where found.
   */
  public function toHTML() {
    //Load the values
    $values = $this->loadValues();

    //Display the title and open the main divs
    $html = '';
    $html .= $this->confDef->openHTML($this->wpKey);

    $repeater_id = 0;
    //If we don't have values, display an empty block
    if(empty($values)) {
      $html .= $this->confDef->toHTML($repeater_id);
    } else {
      //If we have values, display each block
      foreach($values as $repeaterBlock) {
    	$html .= $this->confDef->toHTML($repeater_id, $repeaterBlock);
	$repeater_id++;
      }
    }

    //Add the main buttons, close the divs
    $html .= $this->confDef->closeHTML($repeater_id);

    return $html;
  }

  /**
   * Generates the HTML fragment for one repeater only.
   * @param $repeaterId int The identifier of the repeater.
   * @return The HTML fragment of the repeater.
   */
  public function emptyRepeaterHTML($repeaterId) {
    return $this->confDef->toHTML($repeaterId);
  }

  /**
   * Saves the data to the database.
   * @param $data array The data, as retrieved from the form.
   */
  public function save($data) {
    update_option($this->wpKey, json_encode($data, JSON_NUMERIC_CHECK | JSON_FORCE_OBJECT));
  }
  
  /**
   * Loads values in the database
   * @return Values found in the database.
   */
  private function loadValues() {
    $array = json_decode(get_option($this->wpKey), true);
    if(!is_array($array))
      $array = array();
    return $array;

  }



}


 ?>