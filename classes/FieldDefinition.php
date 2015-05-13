<?php 
namespace LcGenconf;

require_once('ConfigurationDefinitionElement.php');

/**
 * This interface is the common function declarations for all the fields.
 */
interface FieldDefinitionInterface {

  public function __construct($field_id, $field_conf);
  public function toHTML($id_prefix, $value = null);

}

/**
 * This class implements the common parts of all the fields.
 * This includes "condition" management, value computation and the generation of
 * surrounding HTML.
 */
abstract class FieldDefinition extends ConfigurationDefinitionElement implements FieldDefinitionInterface {
  public $id;

  protected $is_condition = false;
  protected $condition;
  protected $default_value = null;
  protected $explanation = null;

  abstract protected function fieldHTML($id_prefix, $value = null);

  /**
   * Constructor.
   * @param $field_id string The identifier of the field.
   * @param $field_conf The array describing the configuration of this field.
   */
  public function __construct($field_id, $field_conf) {
    $this->condition = $this->confValue($field_conf, 'condition');
    $this->default_value = $this->confValue($field_conf, 'default_value');
    $this->explanation = $this->confValue($field_conf, 'explanation');
    $this->id = $field_id;
  }

  /**
   * Sets the fact that this field is the condition for the section.
   * @param $value boolean true if the field is the condition, else false.
   */
  public function setIsCondition($value = true) {
    $this->is_condition = $value;
  }
  
  /**
   * Computes the value of the field, based on the values given and the 
   * default value in the configuration.
   * @param $values array The values for this section.
   * @return The computed value.
   */
  public function computeValue($values) {
    return (isset($values[$this->id])) ? $values[$this->id] : $this->default_value;
    return ($value != null) ? $value : $this->default_value;
  }

  /**
   * Generates the HTML fragment for the field.
   * @param $id_prefx string The string to be prepended to each id, guaranteeing
   * id uniqueness.
   * @param $value The value of this field.
   * @param $condition_value The value of the condition field of the section.
   * @return string The HTML fragment.
   */
  public function toHTML($id_prefix, $value = null, $condition_value = null) {
    $html = $this->toHTMLStart($condition_value);

    $html .= $this->fieldHTML($id_prefix, $value);

    $html .= $this->toHTMLEnd();

    return $html;
  }

  /**
   * Generates the starting tags and the "condition" management for the HTML
   * fragment of the field.
   * @param $condition_value The value of the condition field of the section.
   * @return A part of the HTML fragment for the field.
   */
  protected function toHTMLStart($condition_value) {
    $html = '';

    $html .= '<tr ';
    if(isset($this->condition)) {
      $html .= 'data-condition="' . $this->condition . '"';
    }
    
    if(!isset($this->condition) 
       || $this->condition == "" 
       || $this->condition == $condition_value) {
      $html .= '>';
    } else {
      $html .= ' class=" lc_genconf_hidden">';
    }
    
    return $html;    
  }

  /**
   * Generates the closing tags for the HTML fragment of the field.
   * @return A part of the HTML fragment of the field.
   */
  protected function toHTMLEnd() {
    $html = '</tr>';
  
    if($this->explanation != null) {
      $html .= '<tr><td colspan="100"><span class="lc_genconf_explanation">';
      $html .= $this->explanation;
      $html .= '</span></td></tr>';
    }
    
    return $html;
  }

}

 ?>