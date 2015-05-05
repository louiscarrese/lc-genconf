<?php 
namespace LcWpGenconf;

class DropdownFieldDefinition extends FieldDefinition {
  private $label;
  private $values;
  
  public function __construct($field_id, $field_conf) {
    parent::__construct($field_id, $field_conf);
    $this->label = $this->confValue($field_conf, 'label');
    $this->values = $this->confValue($field_conf, 'values');
  }

  protected function fieldHTML($id_prefix, $value = null) {
    $html = '';

    if(isset($field_conf['label'])) {
      $html .= '<td>' . $field_conf['label'] . '</td>';
    }
    
    $html .= '<td>';
    $html .= '<select';
    $html .= ' name="' . $this->id . '"';
    $html .= ' id="' . $id_prefix . $this->id . '"';
    if($this->is_condition == true)
      $html .= ' onchange="lcGenconfCheckCondition(this);"'; 
    $html .= '>';
    foreach($this->values as $field_value => $field_label) {
      $html .= '<option value="' . $field_value . '" ';
      if($field_value == $value) {
	$html .= 'selected';
      }
      $html .= '>' . $field_label . '</option>';
    }  
    $html .= '</select>';
    $html .= '</td>';
    
    return $html;
  }

}
 ?>