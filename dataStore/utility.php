<?php

function verifyUser ($u, $p) {
	if (isset($u) && isset($p)) {
    include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
		$connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
    		mysql_select_db($database);

		$results = mysql_query("SELECT * FROM tblUser WHERE email = '".mysql_real_escape_string($u)."' AND password = '".md5($p)."' LIMIT 1");
		if (mysql_num_rows($results) > 0) {
			while ($row = mysql_fetch_assoc($results)) {
				$result = true;
			}
		} else {
			$result = false;
		}
	} else {
		$result = false;
	}
	return $result;
}

function logActionRequest ($user, $url, $action, $agent) {
        include '/var/www/dataStore/dataAccess/bzb_db_connect.php';
        $connection = mysql_connect($hostname, $username, $password) OR die('MYSQL Fail: ' . mysql_error());
        mysql_select_db($database);

        mysql_query("INSERT INTO LivingWithEnergyPilot (user, url, action, timeStamp, agent) VALUES ('".$user."', '".$url."', '".$action."', NOW(), '".$agent."')");

        mysql_close();
}

function logAction ($action) {
        logActionRequest($_SESSION['loggedIn'], 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $action, $_SERVER['HTTP_USER_AGENT']);
}

/** 
 * Convert an xml file to an associative array (including the tag attributes): 
 * 
 * @param Str $xml file/string. 
 */ 
class xmlToArrayParser { 
  /** 
   * The array created by the parser which can be assigned to a variable with: $varArr = $domObj->array. 
   * 
   * @var Array 
   */ 
  public  $array; 
  private $parser; 
  private $pointer; 

  /** 
   * $domObj = new xmlToArrayParser($xml); 
   * 
   * @param Str $xml file/string 
   */ 
  public function __construct($xml) { 
    $this->pointer =& $this->array; 
    $this->parser = xml_parser_create("UTF-8"); 
    xml_set_object($this->parser, $this); 
    xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false); 
    xml_set_element_handler($this->parser, "tag_open", "tag_close"); 
    xml_set_character_data_handler($this->parser, "cdata"); 
    xml_parse($this->parser, ltrim($xml)); 
  } 

  private function tag_open($parser, $tag, $attributes) { 
    $this->convert_to_array($tag, '_'); 
    $idx=$this->convert_to_array($tag, 'cdata'); 
    if(isset($idx)) { 
      $this->pointer[$tag][$idx] = Array('@idx' => $idx,'@parent' => &$this->pointer); 
      $this->pointer =& $this->pointer[$tag][$idx]; 
    }else { 
      $this->pointer[$tag] = Array('@parent' => &$this->pointer); 
      $this->pointer =& $this->pointer[$tag]; 
    } 
    if (!empty($attributes)) { $this->pointer['_'] = $attributes; } 
  } 

  /** 
   * Adds the current elements content to the current pointer[cdata] array. 
   */ 
  private function cdata($parser, $cdata) { 
    if(isset($this->pointer['cdata'])) { $this->pointer['cdata'] .= $cdata;} 
    else { $this->pointer['cdata'] = $cdata;} 
  } 

  private function tag_close($parser, $tag) { 
    $current = & $this->pointer; 
    if(isset($this->pointer['@idx'])) {unset($current['@idx']);} 
    $this->pointer = & $this->pointer['@parent']; 
    unset($current['@parent']); 
    if(isset($current['cdata']) && count($current) == 1) { $current = $current['cdata'];} 
    else if(empty($current['cdata'])) { unset($current['cdata']); } 
  } 

  /** 
   * Converts a single element item into array(element[0]) if a second element of the same name is encountered. 
   */ 
  private function convert_to_array($tag, $item) { 
    if(isset($this->pointer[$tag][$item])) { 
      $content = $this->pointer[$tag]; 
      $this->pointer[$tag] = array((0) => $content); 
      $idx = 1; 
    }else if (isset($this->pointer[$tag])) { 
      $idx = count($this->pointer[$tag]); 
      if(!isset($this->pointer[$tag][0])) { 
        foreach ($this->pointer[$tag] as $key => $value) { 
            unset($this->pointer[$tag][$key]); 
            $this->pointer[$tag][0][$key] = $value; 
    }}}else $idx = null; 
    return $idx; 
  } 
}

?>
	  
