<?php 
namespace LcGenconf;

class CategoryFieldDefinition extends FieldDefinition {
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
    $html .= wp_dropdown_categories(array('hide_empty' => 0, 'name' => $this->id, 'id' => $id_prefix . $this->id, 'hierarchical' => true, 'echo' => 0, 'selected' => $value));
    $html .= '</td>';

    return $html;
  }

}
 ?>