<?php
/*
 * Copyright 2007 the original author or authors.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Drip_PropertyPoint
 *
 * @package		Redstart
 * @subpackage  Context
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com
 * @since		Version 1.0
 * @filesource
 */
class Drip_PropertyPoint {

	#	internal variables
  var $name, $property_file, $properties;

	#	Constructor
  function Drip_PropertyPoint($name, $base, $property_file) {
    $this->name = $name;
    $this->property_file = $property_file;
    $this->properties = array();

    include($base.$property_file);

    if(isset($properties) && is_array($properties)) {

    	foreach($properties as $name=>$value){
       		$this->properties[$name] = $value;
    	}

    }

  }

  function &getProperty($name) {
  	if(isset($this->properties[$name])) return $this->properties[$name];
	return _null();
  }


}
?>
