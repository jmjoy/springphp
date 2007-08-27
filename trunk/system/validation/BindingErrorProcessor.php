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
 * BindingErrorProcessor
 *
 * @package		Redstart
 * @subpackage  Validation
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class BindingErrorProcessor {

	function processMissingFieldError($missingField, &$bindingResult) {
		// Create field error with code "required".
		$codes =& $bindingResult->resolveMessageCodes(MISSING_FIELD_ERROR_CODE, $missingField);
		$arguments = $this->_getArgumentsForBindError($bindingResult->getObjectName(), $missingField);
		$newerror = new FieldError(
				$bindingResult->getObjectName(), $missingField, "", true,
				$codes, $arguments, "Field '" . $missingField . "' is required");
		$bindingResult->addError($newerror);
	}

	function processTypeMismatchFieldError($mismatchedField, $value, &$bindingResult) {
		$codes =& $bindingResult->resolveMessageCodes(TYPE_MISMATCH_ERROR_CODE, $mismatchedField);
		$arguments = $this->_getArgumentsForBindError($bindingResult->getObjectName(), $mismatchedField);
		$newerror = new FieldError(
				$bindingResult->getObjectName(), $mismatchedField, $value, true,
				$codes, $arguments, "Field '" . $mismatchedField . "' with value '".var_export($value, true)."' not of correct type.");
		$bindingResult->addError($newerror);
	}

	function _getArgumentsForBindError($objectName, $field) {
		$codes = array( $objectName . NESTED_PATH_SEPARATOR . $field, $field);
		$defaultMessage = $field;
		$msr =& new MessageSourceResolvable($codes, $defaultMessage);
		return array($msr);
	}

}
?>
