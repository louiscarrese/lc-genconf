<?php 
namespace LcGenconf;

class ConfigurationReader {
  private $wpKey;
  private $values;

  private $currentRepeater;

  public function __construct($wpKey) {
    $this->wpKey = $wpKey;
    $this->values = json_decode(get_option($this->wpKey), true);

    if(!isset($this->values[1])) {
      $this->currentRepeater = 0;
    }
  }

  public function getRaw() {
    return $this->values;
  }

  public function setCurrentRepeater($repeaterId) {
    $this->currentRepater = $repeaterId;
  }

  public function findRepeater($sectionKey, $fieldKey, $keyValue) {
    foreach($this->values as $repeaterId => $conf) {
      if(isset($conf[$sectionKey])
	 && isset($conf[$sectionKey][$fieldKey])
	 && $conf[$sectionKey][$fieldKey] == $keyValue) {
	$this->setCurrentRepeater($repeaterId);
	return $repeaterId;
      } else {
	return false;
      }
    }
  }

  public function get($sectionId, $keyId) {
    if(isset($this->values[$this->currentRepeater])
       && isset($this->values[$this->currentRepeater][$sectionId])
       && isset($this->values[$this->currentRepeater][$sectionId][$keyId])) {
	 return $this->values[$this->currentRepeater][$sectionId][$keyId];
       } else {
	 return null;
       }
  }
  

}

 ?>