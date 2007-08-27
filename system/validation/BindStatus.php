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
 * BindStatus
 *
 * @package		Redstart
 * @subpackage  Validation
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class BindStatus {

	var $htmlEscape;
	var $textDomain;

	var $path;
	var $expression;

	var $bindingResult;

	var $value;
	var $valueType;
	var $editor;

	var $objectErrors;
	var $errorCodes;
	var $errorMessages;



	function BindStatus($path, $textDomain = '', $htmlEscape = null) {

		$this->path = $path;
		$this->htmlEscape = is_html_escape($htmlEscape)===true;
		$this->textDomain = $textDomain;

		// determine name of the object and property
		$beanName = null;
		$dotPos = strpos($path,NESTED_PATH_SEPARATOR);
		if ($dotPos === FALSE) {
			// property not set, only the object itself
			$beanName = $path;
			$this->expression = null;
		}
		else {
			$beanName = substr($path, 0, $dotPos);
			$this->expression = substr($path,$dotPos + 1);
		}
		$this->bindingResult =& RequestUtils::getBindingResult($beanName);

		if ($this->bindingResult != null) {
			// Usual case: A BindingResult is available as request attribute.
			// Can determine error codes and messages for the given expression.
			// Can use a custom PropertyEditor, as registered by a form controller.

			if ($this->expression != null) {
				if ("*" == $this->expression) {
					$this->objectErrors =& $this->bindingResult->getAllErrors();
				}
				else if (str_ends_with($this->expression,"*")) {
					$this->objectErrors = $this->bindingResult->getFieldErrors($this->expression);
				}
				else {
					$this->objectErrors = $this->bindingResult->getFieldErrors($this->expression);
					$this->value = $this->bindingResult->getFieldValue($this->expression);
					$this->valueType = $this->bindingResult->getFieldType($this->expression);
					$this->editor =& $this->bindingResult->getCustomEditor($this->expression);
				}
			}

			else {
				$this->objectErrors =& $this->bindingResult->getGlobalErrors();
			}

			$this->_initErrorCodes();
		}

		else {
			// No BindingResult available as request attribute:
			// Probably forwarded directly to a form view.
			// Let's do the best we can: extract a plain target if appropriate.

			$target =& RequestUtils::getModelObject($beanName);
			if ($target == null) {
				show_error('BindStatus', "Neither BindingResult nor plain target object for bean name '" .$beanName . "' available as request attribute");
			}

			if ($this->expression != null && $this->expression != "*" && !str_ends_with($this->expression,"*")) {
				$bw =& new BeanWrapper($target);
				$this->valueType =& $bw->getPropertyType($this->expression);
				$this->value =& $bw->getPropertyValue($this->expression);
			}

			$this->errorCodes = array();
			$this->errorMessages = array();
		}

		if ($htmlEscape && is_string($this->value)) {
			$this->value = specialchars($this->value);
		}
	}

	function _initErrorCodes() {
		$this->errorCodes = array();
		foreach($this->objectErrors as $error) {
			$this->errorCodes[] = $error->getCode();
		}
	}

	function _initErrorMessages() {
		if ($this->errorMessages == null) {
			$this->errorMessages = array();
			foreach($this->objectErrors as $error) {
				$this->errorMessages[] = RequestUtils::getMessage($error, null, '', $this->textDomain, $this->htmlEscape);
			}
		}
	}

	function &getPath() {
		return $this->path;
	}

	function &getExpression() {
		return $this->expression;
	}

	function &getValue() {
		return $this->value;
	}

	function &getValueType() {
		return $this->valueType;
	}

	function getDisplayValue() {
		if (is_string($this->value)) {
			return $this->htmlEscape?specialchars($this->value):$this->value;
		}
		if ($this->value != null && $this->editor != null) {
			$this->editor->setValue($this->value);
			$result = $this->editor->getAsText();
			if($this->htmlEscape) $result = specialchars($result);
			return $result;
		}
		return "";
	}

	function &getEditor() {
		return $this->editor;
	}

	function &getBindingResult() {
		return $this->bindingResult;
	}

	function &getTextDomain() {
		return $this->textDomain;
	}

	function isError() {
		return ($this->errorCodes != null && count((array)$this->errorCodes) > 0);
	}

	function &getErrorCodes() {
		return $this->errorCodes;
	}

	function getErrorCode() {
		return (count((array)$this->errorCodes) > 0 ? $this->errorCodes[0] : "");
	}

	function &getErrorMessages() {
		$this->_initErrorMessages();
		return $this->errorMessages;
	}

	function getErrorMessage() {
		$this->_initErrorMessages();
		return (count((array)$this->errorMessages) > 0 ? $this->errorMessages[0] : "");
	}

	function getErrorMessagesAsString($delimiter) {
		$this->_initErrorMessages();
		return implode($delimiter, $this->errorMessages);
	}

}
?>
