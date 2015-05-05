<?php 
namespace LcWpGenconf;
require_once('FieldDefinition.php');

class TextFieldDefinition extends FieldDefinition {
  private $label;

  public function __construct($field_id, $field_conf) {
    parent::__construct($field_id, $field_conf);
    $this->label = $this->confValue($field_conf, 'label');
  }

  protected function fieldHTML($id_prefix, $value = null) {
    $html = '';

    if(isset($this->label)) {
      $html .= '<td>' . $this->label . '</td>';
    }
    $html .= '<td>';
    $html .= '<input type="text"';
    $html .= ' id="' . $id_prefix . $this->id . '"';
    $html .= ' name="' . $this->id . '"';
    $html .= ' value="' . $value . '"';
    if($this->is_condition)
      $html .= ' onchange="lcGenconfCheckCondition(this);"';
    $html .= '/>';
    $html .= '</td>';
    
    return $html;
  }

}
 ?>