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
 * Drip_Instantiator
 *
 * @package		Redstart
 * @subpackage  Context
 * @author		Ryan Scheuermann
 * @author		Marcus Lunzenauer <mlunzena@uos.de>
 * @link		http://www.concept64.com
 * @link		http://drip.tigris.org/
 * @since		Version 1.0
 * @filesource
 */
class Drip_Instantiator {

	#	internal variables
  var $definition, $base, $className, $filename;

	#	Constructor
  function Drip_Instantiator($definition, $base) {
    $this->definition = $definition;
    $this->base = $base;


	if(is_array($definition) && isset($definition['class'])) {
  		$this->_load_class($definition['class']);
  	}else {
  		show_error('Instantiator Error', 'No class defined: '.var_export($definition, true));
  	}
  }

  function addProperties($properties) {
	if(isset($this->definition['properties'])){
		$new_array = array_merge_recursive($this->definition['properties'], $properties);
		$this->definition['properties']	=& $new_array;
	}
  }

  function &instantiate() {
		if($this->className == null) return _null();

		if(!class_exists($this->className)) {
			if(!is_file($this->base.$this->filename) || !file_exists($this->base.$this->filename)) show_error('Instantiator Error', "File not found for '{$this->className}': ".$this->base.$this->filename);
			include($this->base.$this->filename);
		}

		$class = $this->className;

  	    if (!is_array($this->definition)) {
		    $instance =& new $class();
  	    }else{
		    # parameters
		    $parameters = false;
		    if (isset($this->definition['parameters']))
		    	if (!is_array($parameters = $this->definition['parameters']))
		        $parameters = array($parameters);

		    # instantiate
		    if (!$parameters)
		      $instance =& new $class();
		    else {
		      $instance =& $this->_create_obj_array($class, $parameters);
		    }

			# autowire?
			if(isset($this->definition['autowire']) && $this->definition['autowire'] == true) {
				$vars = get_object_vars($instance);
				foreach($vars as $name => $val) {
					$service =& AppContext::service($name);
					if($service != null) $instance->$name =& $service;
				}
			}

		    # properties
		    if (isset($this->definition['properties'])){

			  $properties = array_map(array(&$this, '_get_typed_value'), $this->definition['properties']);

		      foreach ($properties as $name => $value){
		         $instance->$name =& $properties[$name];
		      }

		    }

		    # invoke
		    if (isset($this->definition['invoke']))
		      foreach ($this->definition['invoke'] as $name => $value) {
		        if (!is_array($value)) $value = array($value);
		        call_user_func_array(array(&$instance, $name), $value);
		      }
		    # initialize-method
		    if (isset($this->definition['initialize-method']))
		      call_user_func(array(&$instance, $this->definition['initialize-method']));

  	    }

		return $instance;
  }

  function _load_class($string) {
	if(strstr($string, '::'))
	{
		list($filename, $className) = explode('::', $string);
		$this->filename = $filename;
		$this->className = $className;
	}else{
		$this->className = $string;
	}
  }

  function &_get_typed_value($value) {

    # value is a string
    if (is_string($value)) {

      # tagged value
      if (preg_match('/^!!(autowire|service|property|constant)\s+(.+)$/', $value, $matches)) {

        switch ($matches[1]) {
        	case 'autowire':
        		$string = $matches[2];
				if(strstr($string, '::'))
				{
					list($filename, $clazz) = explode('::', $string);
					
					if(!class_exists($clazz)) {
						if(!is_file($this->base.$filename) || !file_exists($this->base.$filename)) show_error('Instantiator Error', "File not found for '{$clazz}': ".$this->base.$filename);
						include($this->base.$filename);
					}
	
				}else{
					$clazz = $string;
				}
				
				$value =& AppContext::createAutowiredService($clazz);
        		break;        	
        
        	case 'service':
        		$service = $matches[2];
        		$point =& AppContext::servicePoint($service);
				if($point == NULL) show_error('Instantiator Error', 'Undefined service-point: '.$service);
				$value =& $point->instance();
        		break;

        	case 'property':
        		$value = AppContext::property($matches[2]);
        		break;

        	case 'constant':
        		$value = constant($matches[2]);
        		break;
        }
      }
    }

    # value is an array
    else if (is_array($value)) {

      # recurse
      $newvalue = array_map(array(&$this, '_get_typed_value'), &$value);
      $value =& $newvalue;
    }

    return $value;
  }


  function &_create_obj_array($type, $args) {
    $paramstr = array();
    foreach ($args as $key => $arg)
      $paramstr[] = '$args[' . $key . ']';
    $paramstr = join(',', $paramstr);
    return eval("return new $type($paramstr);");
  }

}
?>