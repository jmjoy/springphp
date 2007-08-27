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
 * DateEditor
 *
 * @package		Redstart
 * @subpackage  PropertyEditors
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @since		Version 1.0
 * @filesource
 */

class TimeEditor extends PropertyEditor {

	var $dateFormat = 'Y-m-d H:i:s';
	var $allowEmpty = false;

	function TimeEditor($dateFormat = null, $allowEmpty = false) {
		if(!is_null($dateFormat)) $this->dateFormat = $dateFormat;
		$this->allowEmpty = $allowEmpty;
	}

	function setValue($val) {
		if(is_array($val)) {
			$time = array_merge(array('year'=>'1970', 'month'=>'01', 'day'=>'01', 'hour'=>'00', 'minute'=>'00', 'second'=>'00'), $val);
			if(isset($time['ampm']))
				$time['hour'] = str_equals_icase($time['ampm'],'pm')?$time['hour']+12:$time['hour'];

			$this->value =& new Time(mktime($time['hour'], $time['minute'], $time['second'], $time['month'], $time['day'], $time['year']));
		}else if(is_a($val, 'Time')){
			$this->value =& $val;
		}else {
			$this->value = new Time(time());
		}
	}

	function setAsText($text) {
		if($text==null || strlen(trim($text)) == 0) {
			if ($this->allowEmpty)  $this->setValue(new Time(time()));
			else return false;
		}
		$time = strtotime($text);
		if($time === FALSE || $time === -1)
			return false;

		$this->setValue(new Time($time));
	}

	function getAsText() {
		if(!is_null($this->value) && is_a($this->value, 'Time')) return date($this->dateFormat, $this->value->getTime());
		else return date($this->dateFormat, time());
	}


}
?>
