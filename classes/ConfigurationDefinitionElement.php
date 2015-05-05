<?php 
namespace LcWpGenconf;

abstract class ConfigurationDefinitionElement {


  protected function confValue($array, $key) {
    if(!empty($array[$key]))
      return $array[$key];
    else
      return null;
  }


}

 ?>