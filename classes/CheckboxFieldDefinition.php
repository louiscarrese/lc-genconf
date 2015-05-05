<?php 
namespace LcWpGenconf;

class CheckboxFieldDefinition extends FieldDefinition {
  private $label;

  public function __construct($field_id, $field_conf) {
    parent::__construct($field_id, $field_conf);
    $this->label = $this->confValue($field_conf,'label');
  }

  protected function fieldHTML($id_prefix, $value = null) {
    $html = '<td colspan="100">';
    $html .= '<label for="' . $this->id . '">';
    $html .= '<input type="checkbox"';
    $html .= ' id="' . $id_prefix . $this->id . '"';
    $html .= ' name="' . $this->id . '"';
    $html .= ' value="true"';
    if($value)
      $html .= ' checked="checked"';
    if($this->is_condition)
      $html .= ' onchange="lcGenconfCheckCondition(this);"';
    $html .= '>';
    $html .= $this->label;
    $html .= '</input>';
    $html .= '</label>';
    //    $html .= '<input type="hidden" id="lc_genconf_hidden_' . $this->id . '" name="' . $this->id . '" />';
    $html .= '<td>';

    return $html;
  }

}
 ?>