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
 * ValidationUtils
 *
 * @package		Redstart
 * @subpackage  Validation
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class ValidationUtils {

	function invokeValidator(&$validator, &$obj, &$bindingResult) {
		assert_not_null($validator, "Validator must not be null");
		assert_not_null($bindingResult, "BindingResult object must not be null");

		if(log_enabled(LOG_DEBUG))
			log_message(LOG_DEBUG,"Invoking validator [" . get_class($validator) . "]");

		if ($obj != null && $validator->supports(get_class($obj)) != true) {
			show_error('Validator', "Validator " . get_class($validator) .
					" does not support " . get_class($obj));
		}

		$currentCount = $bindingResult->getErrorCount();

		$validator->validate(&$obj, &$bindingResult);

		if (log_enabled(LOG_DEBUG)) {
			if ($bindingResult->hasErrors()) {
				$thisCount = $bindingResult->getErrorCount() - $currentCount;
				log_message(LOG_DEBUG,"Validator found " . $thisCount . " errors");
			} else {
				log_message(LOG_DEBUG,"Validator found no errors");
			}
		}
	}

	function rejectIfEmpty(&$bindingResult, $field, $errorCode, $errorArgs = null, $defaultMessage = null) {

		assert_not_null($bindingResult, "BindingResult object must not be null");

		$value = $bindingResult->getFieldValue($field);
		if ($value == null || strlen($value) == 0) {
			$bindingResult->rejectValue($field, $errorCode, $errorArgs, $defaultMessage);
			return false;
		}
		return true;
	}

	function rejectIfEmptyOrWhitespace(&$bindingResult, $field, $errorCode, $errorArgs = null, $defaultMessage = null) {

		assert_not_null($bindingResult, "BindingResult object must not be null");
		$value = $bindingResult->getFieldValue($field);
		if ($value == null || strlen(trim($value)) == 0) {
			$bindingResult->rejectValue($field, $errorCode, $errorArgs, $defaultMessage);
			return false;
		}
		return true;
	}

}
?>
