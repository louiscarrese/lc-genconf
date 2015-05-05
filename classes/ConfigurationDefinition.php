<?php 
namespace LcWpGenconf;

require_once('ConfigurationDefinitionElement.php');
require_once('SectionDefinition.php');

/**
 * This class matches the main element of the configuration description
 * array.
 */
class ConfigurationDefinition extends ConfigurationDefinitionElement {
  private $title;
  private $is_repeateable;
  
  private $sections;

  /** 
   * Constructor.
   * @param $conf_array The configuration description array (see doc).
   */
  public function __construct($conf_array) {
    //Get the main elements
    $this->title = $this->confValue($conf_array, 'title');
    $this->is_repeatable = $this->confValue($conf_array, 'repeater');

    //Initialize the sections
    foreach($conf_array['sections'] as $conf_id => $conf_section) {
      $this->sections[] = new SectionDefinition($conf_id, $conf_section);
    }    
  }

  /**
   * Generates the opening tags for the configuration page.
   * @param $wpKey string The key used in the wp_options table.
   * @return string An HTML fragment.
   */
  public function openHTML($wpKey) {
    $html = <<<EOT
      <h2>{$this->title}</h2>
    <div class="lc_genconf_wrap">
    <form id="lc_genconf_form" method="post" action="">
    <input type="hidden" id="lc_genconf_special_conf_key" name="lc_genconf_special_conf_key" value="{$wpKey}" />
    <div id="lc_genconf_content">
EOT;

    return $html;
  }

  /**
   * Generates the closing tags for the configuration page.
   * @param $next_id int The next repeater id.
   * @return string An HTML fragment.
   */
  public function closeHTML($next_id) {
    $html = '</div>';

    //If we have a repeatable configuration, display a "Add" button.
    if($this->is_repeatable) {
      $html .= <<<EOT
<input type="hidden" id="lc_genconf_add_repeater_nextid" value="{$next_id}" />
<input id="lc_genconf_special_addrepeater" class="button button-primary" type="submit" value="Ajouter" name="lc_genconf_special_addrepeater" onclick="lcAddRepeaterSection(); return false;" />
EOT;
    }

    $html .= <<<EOT
    <p class="submit">
    <input id="lc_genconf_special_submit" class="button button-primary" type="submit" value="Enregistrer les modifications" name="lc_genconf_special_submit" onclick="lcSendForm(); return false;" />
    </p>
    </form>
    </div>
EOT;

    return $html;
  }

  /**
   * Generates the HTML fragment for a repeater.
   * @param $repeater_id int the identifier of the repeater.
   * @param $values array The values of this repeater.
   * @return string The HTML fragment.
   */
  public function toHTML($repeater_id, $values = array()) {
    $id_prefix = $repeater_id . '_';
    
    $html = '';

    //Start the repeater
    $html .= '<div class="lc_genconf_repeater">';
    $html .= '<input type="hidden" id="lc_genconf_special_id" name="lc_genconf_special_id" value="' . $repeater_id . '"/>';

    //Iterate over all the sections
    foreach($this->sections as $section) {
      //If we have values, use them
      if(isset($values[$section->id]))
	$html .= $section->toHTML($id_prefix, $values[$section->id]);
      else
	$html .= $section->toHTML($id_prefix);
    }
    
    //Close the repeater
    if($this->is_repeatable) {
      $html .= '<input id="lc_genconf_special_delrepeater" class="button button-primary" type="submit" value="Supprimer" name="lc_genconf_special_delrepeater" onclick="return lcDeleteRepeaterSection(this); return false;" />';
    }
    
    $html .= '</div>';

    return $html;
  }
}

 ?>