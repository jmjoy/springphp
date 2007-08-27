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

function is_subclass($sClass, $sExpectedParentClass){
    do if( strtolower($sExpectedParentClass) === strtolower($sClass) ) return true;
    while( false != ($sClass = get_parent_class($sClass)) );
    return false;
}

function &_null(){
	$null = null;
	return $null;
}

function assert_not_null($obj, $msg) {
	if($obj === null) show_error('Assert', $msg);
}

function assert_is_null($obj, $msg) {
	if($obj !== null) show_error('Assert', $msg);
}

function get_qualified_type($value) {
	if(is_null($value)) return 'NULL';

	if(is_string($value)) {
		return 'string';
	} else if(is_array($value)) {
		if(!empty($value)) {
			foreach($value as $val) {
				$array_type = get_qualified_type($val);
				if($array_type != null) break;
			}
			return $array_type.'[]';
		}else {
			return 'array';
		}
	}else if(is_int($value)) {
		return 'int';
	}else if(is_bool($value)) {
		return 'boolean';
	}else if(is_float($value)) {
		return 'float';
	}else if(is_object($value)) {
		return strtolower(get_class($value));
	}

	show_error('get_qualified_type', 'Cannot determine type for value: '.var_export($value, true));
}

function is_assignable($type, $value) {
	if(is_null($value)) return true;
	assert_not_null($type, 'Function is_assignable: $type must not be null');
	if(!is_string($type)) show_error('is_assignable', '$type must be a string');

	if(is_array_type($type)) {
//		$array_type = get_array_component_type($type);
		return get_qualified_type($value)==$type;
	}
	switch(strtolower($type)) {

		case 'string':
			if(is_string($value)) return true;
			break;

		case 'int':
			if(is_int($value)) return true;
			break;

		case 'boolean':
			if(is_bool($value)) return true;
			break;

		case 'array':
			if(is_array($value)) return true;
			break;

		case 'float':
			if(is_float($value)) return true;
			break;

		default:
			if(is_object($value) && is_subclass(get_class($value),$type)) return true;
			break;

	}

	return false;
}

function is_array_type($type) {
	if(!is_string($type)) return false;
	$last_2_chars = substr($type, strlen($type)-2);
	return ($last_2_chars == '[]'?true:false);
}

function get_array_component_type($type) {
	if(is_array_type($type)) return substr($type, 0, -2);
	return null;
}

function order_comparator($a, $b) {
	$c = $a->order;
	$d = $b->order;


	if ($c == $d) {
       return 1;
   }

   return ($c < $d) ? -1 : 1;

}

function zeroise($number,$threshold) { // function to add leading zeros when necessary
	return sprintf('%0'.$threshold.'s', $number);
}

function html_entity_decode2($str, $charset='ISO-8859-1')
{
	if (stristr($str, '&') === FALSE) return $str;

	// The reason we are not using html_entity_decode() by itself is because
	// while it is not technically correct to leave out the semicolon
	// at the end of an entity most browsers will still interpret the entity
	// correctly.  html_entity_decode() does not convert entities without
	// semicolons, so we are left with our own little solution here. Bummer.

	if (function_exists('html_entity_decode') && (strtolower($charset) != 'utf-8' OR version_compare(phpversion(), '5.0.0', '>=')))
	{
		$str = html_entity_decode($str, ENT_COMPAT, $charset);
		$str = preg_replace('~&#x([0-9a-f]{2,5})~ei', 'chr(hexdec("\\1"))', $str);
		return preg_replace('~&#([0-9]{2,4})~e', 'chr(\\1)', $str);
	}

	// Numeric Entities
	$str = preg_replace('~&#x([0-9a-f]{2,5});{0,1}~ei', 'chr(hexdec("\\1"))', $str);
	$str = preg_replace('~&#([0-9]{2,4});{0,1}~e', 'chr(\\1)', $str);

	// Literal Entities - Slightly slow so we do another check
	if (stristr($str, '&') === FALSE)
	{
		$str = strtr($str, array_flip(get_html_translation_table(HTML_ENTITIES)));
	}

	return $str;
}




?>
