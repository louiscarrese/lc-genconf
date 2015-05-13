<?php 
namespace LcGenconf;

require_once('ConfigurationDefinitionElement.php');
require_once('FieldFactory.php');
require_once('FieldDefinition.php');

/**
 * This classes matches the "section" element of the configuration description
 * array.
 */
class SectionDefinition extends ConfigurationDefinitionElement {
  public $id;
  private $title;
  private $condition;
  private $fields;

  /**
   * Constructor.
   * @param $id the identifier of the section.
   * @param $conf_array The "section" element of the configuration description
   * array.
   */
  public function __construct($id, $conf_array) {
    //Get the main elements
    $this->id = $id;
    $this->title = $this->confValue($conf_array, 'title');
    
    //Initialize the condition if any
    if(isset($conf_array['condition'])) {
      $this->condition = FieldFactory::makeField('condition', $conf_array['condition']);
      $this->condition->setIsCondition();
    } else {
      $this->condition = null;
    }

    //Initialize the fields
    foreach($conf_array['fields'] as $field_id => $field_value) {
      $this->fields[] = FieldFactory::makeField($field_id, $field_value);
    }
  }

  /**
   * Generates the HTML fragment for the section.
   * @param id_prefix string The string to be prepend to each id, guaranteeing 
   * id uniqueness.
   * @param values array The values of this particular section.
   * @return string The HTML fragment.
   */
  public function toHTML($id_prefix, $values = array()) {
    $thisPrefix = $id_prefix . $this->id . '_';

    $condition_value = null;
    if($this->condition != null)
      $condition_value = $this->condition->computeValue($values);

    $html = <<<EOT
      <div class="lc_genconf_section" id="lc_genconf_section_{$this->id}" data-sectionid="{$this->id}">
      <div class="lc_genconf_section_title">
      <span>{$this->title}</span>
      </div>
      <div class="lc_genconf_section_content">
      <table>
EOT;

    if($this->condition != null) {
      $html .= $this->condition->toHTML($thisPrefix, $condition_value);
    }

    foreach($this->fields as $field) {
      $field_value = $field->computeValue($values);
      $html .= $field->toHTML($thisPrefix, $field_value, $condition_value);
    }

    $html .= <<<EOT
  </table>
  </div>
  <div class="lc_genconf_spacer">&nbsp;</div>
  </div>
EOT;

    return $html;
  }
}
 ?>