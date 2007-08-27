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
 * Bind Helpers
 *
 * @package		Redstart
 * @subpackage  Bind-Tags
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
$_tag_ids = array();
$_tag_attributes = array();

function _tag_generate_id($tag) {
	global $_tag_ids;

	if(!isset($_tag_ids[$tag])) $_tag_ids[$tag] = array();

	$last_id = end($_tag_ids[$tag]);
	if($last_id === false) $last_id = 0;

	$new_id = ++$last_id;

	$_tag_ids[$tag][] = $new_id;

	return $new_id;
}

function _tag_get_id($tag) {
	global $_tag_ids;

	if(!isset($_tag_ids[$tag]) || empty($_tag_ids[$tag]))	show_error('Error', "tag '$tag' not started");
	$last_id = end($_tag_ids[$tag]);
	return $last_id;
}

function _tag_pop_id($tag) {
	global $_tag_ids, $_tag_attributes;

	if(!isset($_tag_ids[$tag]) || empty($_tag_ids[$tag])) show_error('Error', "tag '$name' not started");

	$last_id = array_pop(&$_tag_ids[$tag]);
	unset($_tag_attributes[$tag][$last_id]);
	return $last_id;
}

function _tag_set_attribute($tag, $name, &$value) {
	global $_tag_attributes;

	$id = _tag_get_id($tag);

	$_tag_attributes[$tag][$id][$name] =& $value;
}

function &_tag_get_attribute($tag, $name) {
	global $_tag_attributes;

	$id = _tag_get_id($tag);

	if(!isset($_tag_attributes[$tag][$id][$name])) return _null();

	return $_tag_attributes[$tag][$id][$name];
}

function _tag_remove_attribute($tag, $name) {
	global $_tag_attributes;

	$id = _tag_get_id($tag);

	if(!isset($_tag_attributes[$tag][$id][$name])) return;
	unset($_tag_attributes[$tag][$id][$name]);
}


function bind_errors($name = '') {

	if($name == '') $name = DEFAULT_COMMAND_NAME;
	assert_not_null($name, 'bind_errors_tag() - name must not be null');

	_tag_generate_id('bind_errors');
	_tag_set_attribute('bind_errors', 'name', &$name);

	ob_start();
}

function _bind_errors() {
	global $request;

	$result = ob_get_contents();
	ob_end_clean();

	$name = _tag_get_attribute('bind_errors', 'name');
	$br_name = BINDING_RESULT_MODEL_KEY_PREFIX . $name;
	$bindingResult = $request->getAttribute($br_name);

	if($bindingResult != null && $bindingResult->hasErrors()) {
		echo $result;
	}
}

function message($code, $arguments = null, $text = '', $textDomain = '', $htmlEscape = null, $jsEscape = false) {
	$result = RequestUtils::getMessage($code, $arguments, $text, $textDomain, $htmlEscape);
	if($jsEscape) $result = escape_javascript($result);
	return $result;
}

function form($commandName = '', $options = array()) {
	global $request;

	// start the form tag
	_tag_generate_id('form');

	if($commandName == '') $commandName = DEFAULT_COMMAND_NAME;
	assert_not_null($commandName, 'form() - commandName must not be null');



	if(isset($options['action']) && $options['action'] != null) {
		$url = $options['action'];
	}else {
		$url = $request->getCurrentURL();
	}

	// set the default id attribute
	if(!isset($options['id'])) $options['id'] = $commandName;

	// store the command name for later
	_tag_set_attribute('form', COMMAND_NAME_VARIABLE_NAME, $commandName);

	// expose the command name to the request (for nested tags)
	$request->setAttribute(COMMAND_NAME_REQUEST_ATTRIBUTE, $commandName);

	return form_tag($url, $options);
}

function _form() {
	global $request;
	$request->removeAttribute(COMMAND_NAME_REQUEST_ATTRIBUTE);
	_tag_remove_attribute('form', COMMAND_NAME_VARIABLE_NAME);
	_tag_pop_id('form');
	return '</form>';
}

function nested_path($path = '') {
	global $request;

	_tag_generate_id('nested_path');

	if (strlen($path) > 0 && !str_ends_with($path, NESTED_PATH_SEPARATOR)) {
		$path .= NESTED_PATH_SEPARATOR;
	}

	$nestedPath = $request->getAttribute(NESTED_PATH_VARIABLE_NAME);

	if ($nestedPath != null) {
		_tag_set_attribute('nested_path', 'previousNestedPath', $nestedPath);
		$nestedPath = $nestedPath . $path;
	}
	else {
		$nestedPath = $path;
	}

	$request->setAttribute(NESTED_PATH_VARIABLE_NAME, $nestedPath);
}

function _nested_path() {

	$previousNestedPath = _tag_get_attribute('nested_path', 'previousNestedPath');
	if(!is_null($previousNestedPath)) {
		$request->setAttribute(NESTED_PATH_VARIABLE_NAME, $previousNestedPath);
	}else {
		$request->removeAttribute(NESTED_PATH_VARIABLE_NAME);
	}
	_tag_pop_id('nested_path');
}

function form_errors($path = '', $textDomain = '', $tag = 'span', $delimiter = '<br/>', $options = array()) {
	global $request;

	$bindStatus =& new BindStatus(_get_bind_path($path), $textDomain);

	if(!$bindStatus->isError()) return;

	$messages = $bindStatus->getErrorMessagesAsString($delimiter);

	return content_tag($tag, $messages, $options);

}

function escape_body($htmlEscape = null, $jsEscape = false) {

	_tag_generate_id('escape_body');
	$htmlEscape = is_html_escape($htmlEscape);
	_tag_set_attribute('escape_body', 'htmlEscape', $htmlEscape);
	_tag_set_attribute('escape_body', 'jsEscape', $jsEscape);

	ob_start();
}

function _escape_body() {

	$result = ob_get_contents();
	ob_end_clean();

	$htmlEscape = _tag_get_attribute('escape_body', 'htmlEscape');
	$jsEscape = _tag_get_attribute('escape_body', 'jsEscape');
	if(is_null($jsEscape) || !is_bool($jsEscape)) $jsEscape = false;

	if($jsEscape == true) $result = escape_javascript($result);
	if($htmlEscape == true) $result = specialchars($result);

	return $result;
}

function input($path, $options = array()) {
	assert_not_null($path, 'input(): $path cannot be null');

	$bindStatus =& new BindStatus(_get_bind_path($path));
	$value = $bindStatus->getDisplayValue();

	return input_tag(_get_complete_bind_path($path), $value, $options);
}


function label($path, $label = null, $options = array()) {

	$id = '';
	if(isset($options['id'])) $id = $options['id'];
	else $id = _get_complete_bind_path($path);

	if(is_null($label)) $label = $id . ': ';

	return label_for($id, $label, $options);
}

function file_input($path, $options = array()) {
	return input($path, array_merge(array('type'=>'file'), $options));
}

function hidden($path, $value = null, $options = array()) {

	$bindStatus =& new BindStatus(_get_bind_path($path));
	if($value == null)
		$value = $bindStatus->getDisplayValue();

	return input_hidden_tag(_get_complete_bind_path($path), $value, $options);
}

function checkbox($path, $value = null, $options = array()) {

	$result = '';

	$name = _get_complete_bind_path($path);
	$bindStatus =& new BindStatus(_get_bind_path($path));
	$boundValue = $bindStatus->getValue();
	$valueType = $bindStatus->getValueType();

	if(str_equals_icase($valueType, 'boolean')) {
		if(is_string($boundValue)) $boundValue = str_bool($boundValue);
		$booleanValue = $boundValue != null ? $boundValue : false;

		$result .= checkbox_tag($name, 'true', $booleanValue, $options);
	} else {
		assert_not_null($value, "Attribute 'value' is required when binding to non-boolean values");
		$result .= checkbox_tag($name, specialchars($value, true), SelectedValueComparator::isSelected($bindStatus, $value), $options);
	}

	if(!isset($options['disabled']) || $options['disabled'] != 'disabled') {
		$result .= input_hidden_tag('_'.$name, '1');
	}

	return $result;
}


function checkboxes($path, $items, $itemValue = null, $itemLabel = null, $options = array()) {
	global $request;

	if(!is_array($items)) show_error('$items must be an array');

	$name = _get_complete_bind_path($path);
	$bindStatus =& new BindStatus(_get_bind_path($path));

	$contents = '';

	foreach($items as $key => $value) {

		if(is_object($value)){
			if(!is_null($itemValue) || !is_null($itemLabel)){
				$bw = new BeanWrapper($value);
			}
			$iVal = is_null($itemValue)?$value:$bw->getPropertyValue($itemValue);
			$iLabel = is_null($itemLabel)?$value:$bw->getPropertyValue($itemLabel);
		}else if(is_string($key)) {
			$iVal = $key;
			$iLabel = $value;
		}else {
			$iVal = $value;
			$iLabel = $value;
		}

		$iVal = _get_display_string($iVal, $bindStatus->getEditor());
		$iLabel = _get_display_string($iLabel, $bindStatus->getEditor());

		$id = $name.'_'.$iVal;

		$checkbox = checkbox_tag($name.'[]', $iVal, SelectedValueComparator::isSelected($bindStatus, $iVal)||SelectedValueComparator::isSelectedUsingBeanWrapper($bindStatus, $iVal, $itemValue), array_merge(array('id'=>$id),$options));
		$contents .= content_tag('label',$checkbox .' '.$iLabel, array('for'=>$id));

	}
	$contents .= input_hidden_tag('_'.$name, '1');

	return $contents;

}

function password($path, $showPassword = false, $options = array()){

	$name = _get_complete_bind_path($path);
	$bindStatus =& new BindStatus(_get_bind_path($path));
	$boundValue = $bindStatus->getDisplayValue();

	return input_password_tag($name, $showPassword==true?$boundValue:null, $options);
}

function textarea($path, $options = array()) {

	$name = _get_complete_bind_path($path);
	$bindStatus =& new BindStatus(_get_bind_path($path));
	$boundValue = $bindStatus->getDisplayValue();

	return textarea_tag($name, $boundValue, $options);
}

function radio($path, $value = '', $options = array()) {

	$result = '';

	$name = _get_complete_bind_path($path);
	$bindStatus =& new BindStatus(_get_bind_path($path));
	$boundValue = $bindStatus->getValue();
	$valueType = $bindStatus->getValueType();

	return radiobutton_tag($name, _get_display_string($value, $bindStatus->getEditor()), SelectedValueComparator::isSelected($bindStatus, $value), $options);
}

function select($path, $items = null, $itemValue = null, $itemLabel = null, $options = array()) {
	global $request;

	//TODO: forceMultiple for arrays
	$isMultiple = (isset($options['multiple']) && $options['multiple'] == 'multiple');

	$name = _get_complete_bind_path($path);

	if ($isMultiple && substr($name, -2) !== '[]')
	    $name .= '[]';

	$bindStatus =& new BindStatus(_get_bind_path($path));

	$result = '';

	if($items != null ) {

		if(!is_array($items)) show_error('$items must be an array');

		$contents = '';
		
		  if (isset($options['include_custom']))
		  {
			$contents .= content_tag('option', $options['include_custom'], array('value' => ''))."\n";
		  }
		  else if (isset($options['include_blank']))
		  {
			$contents .= content_tag('option', '', array('value' => ''))."\n";
		  }
		unset($options['include_custom']);
		unset($options['include_blank']);

		$i = 0;
		foreach($items as $key => $value) {

			if(is_object($value)){
				if(!is_null($itemValue) || !is_null($itemLabel)){
					$bw = new BeanWrapper($value);
				}
				$iVal = is_null($itemValue)?$value:$bw->getPropertyValue($itemValue);
				$iLabel = is_null($itemLabel)?$value:$bw->getPropertyValue($itemLabel);
			}else if(is_string($key) || (is_numeric($key) && $key != $i)) {
				$iVal = $key;
				$iLabel = $value;
			}else {
				$iVal = $value;
				$iLabel = $value;
			}

			$iVal = _get_display_string($iVal, $bindStatus->getEditor());
			$iLabel = _get_display_string($iLabel, $bindStatus->getEditor());

			$opt_options = array('value'=>$iVal);
			if(SelectedValueComparator::isSelected($bindStatus, $iVal) || SelectedValueComparator::isSelected($bindStatus, $iLabel))
				$opt_options['selected'] = 'selected';

			$contents .= content_tag('option', $iLabel, $opt_options);
			++$i;
		}

		$result .= select_tag($name, $contents, $options);

		if($isMultiple){
			$result .= input_hidden_tag('_'.$name, '1');
		}


	} else {
		 _tag_generate_id('select');
		 _tag_set_attribute('select', 'multiple', $isMultiple);
		 _tag_set_attribute('select', 'name', $name);
		 $request->setAttribute('select_listValue', $bindStatus);
		$result .= tag('select', array_merge(array('name'=>$name, 'id'=>get_id_from_name($name)),$options), true);
	}

	return $result;
}

function _select() {
	$id = _tag_get_id('select');
	$result = '</select>';
	if($id != null) {
		$multiple = _tag_get_attribute('select', 'multiple');
		if($multiple){
			$result = input_hidden_tag('_'._tag_get_attribute('select', 'name'), '1');
		}
		_tag_pop_id('select');
	}
	return $result;
}

function options($items, $itemValue = null, $itemLabel = null, $options = array()) {
	global $request;

	$bindStatus =& $request->getAttribute('select_listValue');
	assert_not_null($bindStatus, 'options tag must be used within a select tag');

	if(!is_array($items)) show_error('$items must be an array');

	$contents = '';

	foreach($items as $key => $value) {

		//TODO: optgroup

		if(is_object($value)){
			$bw = new BeanWrapper($value);
			$iVal = is_null($itemValue)?$value:$bw->getPropertyValue($itemValue);
			$iLabel = is_null($itemLabel)?$value:$bw->getPropertyValue($itemLabel);
		}else if(is_string($key)) {
			$iVal = $key;
			$iLabel = $value;
		}else {
			$iVal = $value;
			$iLabel = $value;
		}

		$iVal = _get_display_string($iVal, $bindStatus->getEditor());
		$iLabel = _get_display_string($iLabel, $bindStatus->getEditor());

		$opt_options = array('value'=>$iVal);
		if(SelectedValueComparator::isSelected($bindStatus, $iVal) || SelectedValueComparator::isSelected($bindStatus, $iLabel))
			$opt_options['selected'] = 'selected';

		$contents .= content_tag('option', $iLabel, $opt_options);

	}
	return $contents;

}

function option($value, $label = null, $options = array()) {
	global $request;

	$bindStatus =& $request->getAttribute('select_listValue');
	assert_not_null($bindStatus, 'option tag must be used within a select tag');

	$iVal = _get_display_string($value, $bindStatus->getEditor());
	$iLabel = $label == null?$iVal:$label;

	$options['value'] = $iVal;
	
	if(SelectedValueComparator::isSelected($bindStatus, $iVal))
	{
		$selectedVal = _tag_get_attribute('select', 'selectedVal');
		if($selectedVal !== true)	
		{
			$options['selected'] = 'selected';
			_tag_set_attribute('select', 'selectedVal', $val = true);
		}
		
	}

	return content_tag('option', $iLabel, $options);

}

function select_date($path, $options = array(), $html_options = array()) {
	$name = _get_complete_bind_path($path);
	$bindStatus =& new BindStatus(_get_bind_path($path));
	$boundValue = $bindStatus->getDisplayValue();

	return select_date_tag($name, $boundValue, $options, $html_options);
}

function select_time($path, $options = array(), $html_options = array()) {
	$name = _get_complete_bind_path($path);
	$bindStatus =& new BindStatus(_get_bind_path($path));
	$boundValue = $bindStatus->getDisplayValue();

	return select_time_tag($name, $boundValue, $options, $html_options);
}

function select_datetime($path, $options = array(), $html_options = array()) {
	$name = _get_complete_bind_path($path);
	$bindStatus =& new BindStatus(_get_bind_path($path));
	$boundValue = $bindStatus->getDisplayValue();

	return select_datetime_tag($name, $boundValue, $options, $html_options);
}

function select_month($path, $options = array(), $html_options = array()) {
	$name = _get_complete_bind_path($path);
	$bindStatus =& new BindStatus(_get_bind_path($path));
	$boundValue = $bindStatus->getDisplayValue();

	return select_month_tag($name, $boundValue, $options, $html_options);
}

function select_day($path, $options = array(), $html_options = array()) {
	$name = _get_complete_bind_path($path);
	$bindStatus =& new BindStatus(_get_bind_path($path));
	$boundValue = $bindStatus->getDisplayValue();

	return select_day_tag($name, $boundValue, $options, $html_options);
}

function select_year($path, $options = array(), $html_options = array()) {
	$name = _get_complete_bind_path($path);
	$bindStatus =& new BindStatus(_get_bind_path($path));
	$boundValue = $bindStatus->getDisplayValue();

	return select_year_tag($name, $boundValue, $options, $html_options);
}

function select_hour($path, $options = array(), $html_options = array()) {
	$name = _get_complete_bind_path($path);
	$bindStatus =& new BindStatus(_get_bind_path($path));
	$boundValue = $bindStatus->getDisplayValue();

	return select_hour_tag($name, $boundValue, $options, $html_options);
}

function select_minute($path, $options = array(), $html_options = array()) {
	$name = _get_complete_bind_path($path);
	$bindStatus =& new BindStatus(_get_bind_path($path));
	$boundValue = $bindStatus->getDisplayValue();

	return select_minute_tag($name, $boundValue, $options, $html_options);
}

function select_second($path, $options = array(), $html_options = array()) {
	$name = _get_complete_bind_path($path);
	$bindStatus =& new BindStatus(_get_bind_path($path));
	$boundValue = $bindStatus->getDisplayValue();

	return select_second_tag($name, $boundValue, $options, $html_options);
}

function select_ampm($path, $options = array(), $html_options = array()) {
	$name = _get_complete_bind_path($path);
	$bindStatus =& new BindStatus(_get_bind_path($path));
	$boundValue = $bindStatus->getDisplayValue();

	return select_ampm_tag($name, $boundValue, $options, $html_options);
}

function select_timezone($path, $options = array(), $html_options = array()) {
	$name = _get_complete_bind_path($path);
	$bindStatus =& new BindStatus(_get_bind_path($path));
	$boundValue = $bindStatus->getDisplayValue();

	return select_timezone_tag($name, $boundValue, $options, $html_options);
}

function select_country($path, $options = array(), $html_options = array()) {
	$name = _get_complete_bind_path($path);
	$bindStatus =& new BindStatus(_get_bind_path($path));
	$boundValue = $bindStatus->getDisplayValue();

	return select_country_tag($name, $boundValue, $options, $html_options);
}


function _get_bind_path($path = '') {
	global $request;

	$resolvedPath = '';

	$commandName = $request->getAttribute(COMMAND_NAME_REQUEST_ATTRIBUTE);
	if($commandName == null) show_error('Bind', 'No commandName attribute found in request attributes');

	$resolvedPath .= $commandName .NESTED_PROPERTY_SEPARATOR;

	$resolvedPath .= _get_complete_bind_path($path);
	$resolvedPath = preg_replace('/\[\]/', '', $resolvedPath);
	return $resolvedPath;
}

function _get_complete_bind_path($path = '') {
	global $request;
	$resolvedPath = '';

	$nestedPath =& $request->getAttribute(NESTED_PATH_VARIABLE_NAME);
	if($nestedPath != null && strlen(trim($nestedPath)) == 0)
		$resolvedPath .= $nestedPath;

	$resolvedPath .= $path;
	return $resolvedPath;
}

function _get_display_string($value, &$propertyEditor) {

	if (is_string($value) || $propertyEditor == null) {
		if(is_object($value) && is_null($propertyEditor)) show_error('Bind', 'Cannot get display string for object of type: '.get_class($value));
		return is_html_escape(null)?specialchars($value):$value;
	}

	$originalValue = $propertyEditor->getValue();

//	var_dump($value);
//	var_dump($propertyEditor);
	$propertyEditor->setValue($value);
	$result = $propertyEditor->getAsText();
	if(!is_null($result))
		$result = is_html_escape(null)?specialchars($result):$result;
	else
		$result = '';

	$propertyEditor->setValue($originalValue);

	return $result;
}

?>
