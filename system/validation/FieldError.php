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
 * FieldError
 *
 * @package		Redstart
 * @subpackage  Validation
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class FieldError extends ObjectError{

	var $field;
	var $rejectedValue;
	var $bindingFailure;

	function FieldError(&$objectName, &$field, &$rejectedValue, $bindingFailure, &$codes, $arguments = null, $defaultMessage = null) {
		parent::ObjectError($objectName, $codes, $arguments, $defaultMessage);
		assert_not_null($field, 'Field must not be null');

		$this->field =& $field;
		$this->rejectedValue =& $rejectedValue;
		$this->bindingFailure =& $bindingFailure;
	}

	function &getField() {
		return $this->field;
	}

	function &getRejectedValue() {
		return $this->rejectedValue;
	}

	function &isBindingFailure() {
		return $this->bindingFailure;
	}

	function toString() {
		return "Field error in object '" . $this->getObjectName() . "' on field '" . $this->field .
				"': rejected value [" . $this->rejectedValue . "]; " . $this->_resolvableToString();
	}

}
?>
