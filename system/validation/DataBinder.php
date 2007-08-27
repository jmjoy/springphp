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
 * DataBinder
 *
 * @package		Redstart
 * @subpackage  Validation
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class DataBinder {

	var $target = null;
	var $objectName = null;
	var $bindingResult = null;

	var $ignoreUnknownFields = true;
	var $ignoreInvalidFields = false;

	var $allowedFields = array();
	var $disallowedFields = array();
	var $requiredFields = array();

	var $bindingErrorProcessor;


	function DataBinder(&$target, $objectName = DEFAULT_OBJECT_NAME) {
		assert_not_null($target, 'DataBinder: target must not be null');
		$this->target =& $target;
		$this->objectName =& $objectName;
		$this->bindingErrorProcessor =& new BindingErrorProcessor();
	}

	function &getTarget() {
		return $this->target;
	}

	function &getObjectName() {
		return $this->objectName;
	}

	function &_getInternalBindingResult() {
		if ($this->bindingResult == null) {
			$this->bindingResult =& new BindingResult($this->getTarget(), $this->getObjectName());
		}
		return $this->bindingResult;
	}

	function &getBeanWrapper() {
		$br =& $this->_getInternalBindingResult();
		return $br->getBeanWrapper();
	}

	function &getBindingResult() {
		return $this->_getInternalBindingResult();
	}

	function &isIgnoreUnknownFields() {
		return $this->ignoreUnknownFields;
	}

	function &isIgnoreInvalidFields() {
		return $this->ignoreInvalidFields;
	}

	function setAllowedFields(&$allowedFields) {
		$this->allowedFields =& BeanWrapperUtils::canonicalPropertyNames($allowedFields);
	}

	function &getAllowedFields() {
		return $this->allowedFields;
	}

	function setDisallowedFields(&$disallowedFields) {
		$this->disallowedFields =& BeanWrapperUtils::canonicalPropertyNames($disallowedFields);
	}

	function &getDisallowedFields() {
		return $this->disallowedFields;
	}

	function setRequiredFields($requiredFields) {
		$this->requiredFields =& BeanWrapperUtils::canonicalPropertyNames($requiredFields);
		if (log_enabled(LOG_DEBUG)) {
			log_message(LOG_DEBUG, "DataBinder requires binding of required fields [" .
					implode(',', $this->requiredFields) . "]");
		}
	}

	function &getRequiredFields() {
		return $this->requiredFields;
	}

	function registerCustomEditor($requiredType, $field,  &$propertyEditor) {
		$bw =& $this->getBeanWrapper();
		$bw->registerCustomEditor($requiredType, $field, &$propertyEditor);
	}

	function &findCustomEditor($requiredType, $propertyPath) {
		$bw =& $this->getBeanWrapper();
		return $bw->findCustomEditor($requiredType, $propertyPath);
	}

	function setMessageCodesResolver(&$messageCodesResolver) {
		$br =& $this->getBindingResult();
		$br->setMessageCodesResolver(&$messageCodesResolver);
	}

	function setBindingErrorProcessor(&$bindingErrorProcessor) {
		$this->bindingErrorProcessor =& $bindingErrorProcessor;
	}

	function &getBindingErrorProcessor() {
		return $this->bindingErrorProcessor;
	}

	function bind($nvps) {
		$this->checkAllowedFields(&$nvps);
		$this->checkRequiredFields(&$nvps);
		return $this->applyPropertyValues(&$nvps);
	}

	function checkAllowedFields(&$nvps) {
		foreach((array)$nvps as $name => $value) {
			$field = BeanWrapperUtils::canonicalPropertyName($name);
			if(!$this->isAllowed($field)) {
				unset($nvps[$name]);
				$br =& $this->getBindingResult();
				$br->recordSuppressedField($field);
				if(log_enabled(LOG_DEBUG))
					log_message(LOG_DEBUG, "Field [" . $field . "] has been removed from the nvps array " .
							"and will not be bound, because it has not been found in the list of allowed fields");
			}
		}
	}

	function isAllowed($field) {
		$allowed =& $this->getAllowedFields();
		$disallowed =& $this->getDisallowedFields();
		return ((empty($allowed) || simple_pattern_match($allowed, $field)) &&
				(empty($disallowed) || !simple_pattern_match($disallowed, $field)));
	}

	function checkRequiredFields(&$nvps) {
		$requiredFields =& $this->getRequiredFields();
		if (!empty($requiredFields)) {
			$values = array();
			foreach($nvps as $name => $value) {
				$canonicalName = BeanWrapperUtils::canonicalPropertyName($name);
				$values[$canonicalName] = $value;
			}
			foreach($requiredFields as $field) {
				$value = isset($values[$field])?$values[$field]:null;
				if(($value == null) || (is_string($value) && trim($value) == ""))
				{
					$bep =& $this->getBindingErrorProcessor();
					$bep->processMissingFieldError($field, $this->_getInternalBindingResult());
					unset($nvps[$field]);
					unset($values[$field]);
				}

			}
		}
	}

	function applyPropertyValues(&$nvps) {

		$bw =& $this->getBeanWrapper();
		$result = $bw->setPropertyValues(&$nvps, $this->isIgnoreUnknownFields(), $this->isIgnoreInvalidFields());
		if($result !== true) {
			if(is_array($result)) {
				foreach($result as $ex) {
					$bep =& $this->getBindingErrorProcessor();
					$bep->processTypeMismatchFieldError($ex->getPropertyName(), $ex->getNewValue(), $this->_getInternalBindingResult());
				}
			}
		}
		return $result;
	}
}
?>
