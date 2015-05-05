<?php 
namespace LcWpGenconf;
require_once('FieldDefinition.php');
require_once('TextFieldDefinition.php');
require_once('DropdownFieldDefinition.php');
require_once('CheckboxFieldDefinition.php');
require_once('CategoryFieldDefinition.php');


/**
 * This class is used to abstract the multiple field types.
 * It has one static function to instantiate a FieldDescription, based
 * on its configuration.
 */
class FieldFactory {
  /**
   * Depending on the configuration of the field, the correct instance will
   * be returned.
   * @param $field_id string The identifier of the field.
   * @param $field_conf array The configuration array of the field.
   * @return FieldDefinitionInterface The FieldDefinition implementation.
   */
  public static function makeField($field_id, $field_conf) {
    switch($field_conf['type']) {
    case 'text':
      return new TextFieldDefinition($field_id, $field_conf);
      break;
    case 'dropdown':
      return new DropdownFieldDefinition($field_id, $field_conf);
      break;
    case 'checkbox':
      return new CheckboxFieldDefinition($field_id, $field_conf);
      break;
    case 'category':
      return new CategoryFieldDefinition($field_id, $field_conf);
      break;
    default:
      return null;
      break;
    }
    
  }
  
}

 ?>