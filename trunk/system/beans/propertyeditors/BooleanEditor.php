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
 * BooleanEditor
 *
 * @package		Redstart
 * @subpackage  PropertyEditors
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class BooleanEditor extends PropertyEditor {

	var $trueString = null;
	var $falseString = null;

	function BooleanEditor($trueString = null, $falseString = null) {
		$this->trueString = $trueString;
		$this->falseString = $falseString;
	}

	function setAsText($text) {
		if($text == null) return false;

		if ($this->trueString != null && str_equals_icase($this->trueString,$text)) {
			$this->setValue(TRUE);
		}
		else if ($this->falseString != null && str_equals_icase($this->falseString, $text)) {
			$this->setValue(FALSE);
		}
		else if ($this->trueString == null &&
				(str_equals_icase($text,'true') || str_equals_icase($text, 'on') ||
				str_equals_icase($text, 'yes') || str_equals_icase($text, '1'))) {
			$this->setValue(TRUE);
		}
		else if ($this->falseString == null &&
				(str_equals_icase($text,'false') || str_equals_icase($text, 'off') ||
				str_equals_icase($text, 'no') || str_equals_icase($text, '0'))) {
			$this->setValue(FALSE);
		}else {
			return false;
		}

		return true;
	}

	function getAsText() {
		if (TRUE == $this->getValue()) {
			return ($this->trueString != null ? $this->trueString : 'true');
		}
		else if (FALSE == $this->getValue()) {
			return ($this->falseString != null ? $this->falseString : 'false');
		}
		else {
			return "";
		}
	}

}
?>
