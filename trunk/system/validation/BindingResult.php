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
 * BindingResult
 *
 * @package		Redstart
 * @subpackage  Validation
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class BindingResult {

	var $target = null;

	var $beanWrapper = null;

	var $errors = array();

	var $objectName = null;

	var $messageCodesResolver = null;

	var $nestedPath = '';
	var $nestedPathStack = array();
	var $suppressedFields = array();

	function BindingResult(&$target, &$objectName) {
		assert_not_null($target, 'Target must not be null');
		assert_not_null($objectName, 'Object name must not be null');

		$this->objectName =& $objectName;
		$this->target =& $target;

		$this->messageCodesResolver =& new MessageCodesResolver();

	}

	function setMessageCodesResolver(&$messageCodesResolver) {
		$this->messageCodesResolver =& $messageCodesResolver;
	}

	function &getMessageCodesResolver() {
		return $this->messageCodesResolver;
	}

//---------------------------------------------------------------------
	// Implementation of Errors interface
	//---------------------------------------------------------------------

	function &getObjectName() {
		return $this->objectName;
	}

	function setNestedPath($nestedPath) {
		$this->_doSetNestedPath($nestedPath);
		$this->nestedPathStack = array();
	}

	function getNestedPath() {
		return $this->nestedPath;
	}

	function pushNestedPath($subPath) {
		array_push($this->nestedPathStack, $this->getNestedPath());
		$this->_doSetNestedPath($this->getNestedPath() . $this->subPath);
	}

	function popNestedPath() {
		$formerNestedPath = array_pop($this->nestedPathStack);
		$this->_doSetNestedPath($formerNestedPath);
	}

	/**
	 * Actually set the nested path.
	 * Delegated to by setNestedPath and pushNestedPath.
	 */
	function _doSetNestedPath($nestedPath) {
		if ($nestedPath == null) {
			$nestedPath = "";
		}
		$nestedPath = $this->_canonicalFieldName($nestedPath);
		if (strlen($nestedPath) > 0 && !str_ends_with($nestedPath,NESTED_PATH_SEPARATOR)) {
			$nestedPath .= NESTED_PATH_SEPARATOR;
		}
		$this->nestedPath = $nestedPath;
	}

	/**
	 * Transform the given field into its full path,
	 * regarding the nested path of this instance.
	 */
	function _fixedField($field) {
		if (strlen(trim($field)) != 0) {
			return $this->getNestedPath() . $this->_canonicalFieldName($field);
		}
		else {
			$path = $this->getNestedPath();
			return (str_ends_with($path,NESTED_PATH_SEPARATOR) ?
					substr($path, 0, strlen($path) - strlen(NESTED_PATH_SEPARATOR)) : $path);
		}
	}


	function reject($errorCode, $errorArgs = null, $defaultMessage = null) {
		$oe = new ObjectError($this->getObjectName(), $this->resolveMessageCodes($errorCode), $errorArgs, $defaultMessage);
		$this->addError($oe);
	}

	function rejectValue($field, $errorCode, $errorArgs = null, $defaultMessage = null) {
		if ("" == $this->getNestedPath() && strlen(trim($field)) == 0) {
			// We're at the top of the nested object hierarchy,
			// so the present level is not a field but rather the top object.
			// The best we can do is register a global error here...
			$this->reject($errorCode, $errorArgs, $defaultMessage);
			return;
		}
		$fixedField = $this->_fixedField($field);
		$newVal = $this->getActualFieldValue($fixedField);
		$fe = new FieldError(
				$this->getObjectName(), $fixedField, $newVal, false,
				$this->resolveMessageCodes($errorCode, $field), $errorArgs, $defaultMessage);
		$this->addError($fe);
	}

	function &resolveMessageCodes($errorCode, $field = null) {
		$mcr =& $this->getMessageCodesResolver();
		if($field != null) {
			$fixedField = $this->_fixedField($field);
			$fieldType = $this->getFieldType($fixedField);
			$result =& $mcr->resolveMessageCodes($errorCode, $this->getObjectName(), $fixedField, $fieldType);
			return $result;
		}else {
			$result =& $mcr->resolveMessageCodes($errorCode, $this->getObjectName());
			return $result;
		}
	}

	function addError($error) {
		$this->errors[] = $error;
	}

	function addAllErrors($errors) {
		if ($errors->getObjectName() != $this->getObjectName()) {
			show_error('Illegal Argument in BindingResult.addAllErrors',"Errors object needs to have same object name");
		}
		foreach($errors->getAllErrors() as $err) {
			$this->addError($err);
		}
	}


	function hasErrors() {
		return empty($this->errors)?false:true;
	}

	function getErrorCount() {
		return count($this->errors);
	}

	function &getAllErrors() {
		return $this->errors;
	}

	function hasGlobalErrors() {
		return ($this->getGlobalErrorCount() > 0);
	}

	function getGlobalErrorCount() {
		return count($this->getGlobalErrors());
	}

	function getGlobalErrors() {
		$result = array();
		foreach($this->errors as $err) {
			if(!is_a($err, 'FieldError'))
				$result[] = $err;
		}
		return $result;
	}

	function getGlobalError() {
		foreach($this->errors as $err) {
			if(!is_a($err, 'FieldError'))
				return $err;
		}
		return null;
	}


	function hasFieldErrors($field = null) {
		return ($this->getFieldErrorCount($field) > 0);
	}

	function getFieldErrorCount($field = null) {
		return count($this->getFieldErrors($field));
	}

	function getFieldErrors($field = null) {
		$result = array();
		$fixedField = $this->_fixedField($field);
		foreach($this->errors as $err) {
			if(is_a($err, 'FieldError')) {
				if($field == null || $this->_isMatchingFieldError($fixedField, $err)) {
					$result[] = $err;
				}
			}
		}
		return $result;
	}

	function getFieldError($field = null) {
		$fixedField = $this->_fixedField($field);
		foreach($this->errors as $err) {
			if(is_a($err, 'FieldError')) {
				if($field == null || $this->_isMatchingFieldError($fixedField, $err))
					return $err;
			}
		}
		return null;
	}

	/**
	 * Check whether the given FieldError matches the given field.
	 * @param field the field that we are looking up FieldErrors for
	 * @param fieldError the candidate FieldError
	 * @return whether the FieldError matches the given field
	 */
	function _isMatchingFieldError($field, $fieldError) {
		return ($field == $fieldError->getField()) ||
				(str_ends_with($field,"*") && str_starts_with($fieldError->getField(),substr($field, 0, strlen($field) - 1)));
	}


	function getFieldValue($field) {
		$fe = $this->getFieldError($field);
		// Use rejected value in case of error, current bean property value else.
		$value = null;
		if ($fe != null) {
			$value = $fe->getRejectedValue();
		}
		else {
			$value = $this->getActualFieldValue($this->_fixedField($field));
		}
		// Apply formatting, but not on binding failures like type mismatches.
		if ($fe == null || $fe->isBindingFailure() == false) {
			$value = $this->_formatFieldValue($field, $value);
		}
		return $value;
	}


	//---------------------------------------------------------------------
	// Implementation of BindingResult interface
	//---------------------------------------------------------------------

	/**
	 * Return a model Map for the obtained state, exposing an Errors
	 * instance as '{@link #MODEL_KEY_PREFIX MODEL_KEY_PREFIX} + objectName'
	 * and the object itself.
	 * <p>Note that the Map is constructed every time you're calling this method.
	 * Adding things to the map and then re-calling this method will not work.
	 * <p>The attributes in the model Map returned by this method are usually
	 * included in the ModelAndView for a form view that uses Spring's bind tag,
	 * which needs access to the Errors instance. Spring's SimpleFormController
	 * will do this for you when rendering its form or success view. When
	 * building the ModelAndView yourself, you need to include the attributes
	 * from the model Map returned by this method yourself.
	 * @see #getObjectName
	 * @see #MODEL_KEY_PREFIX
	 * @see org.springframework.web.servlet.ModelAndView
	 * @see org.springframework.web.servlet.tags.BindTag
	 * @see org.springframework.web.servlet.mvc.SimpleFormController
	 */
	function &getModel() {
		$model = array();
		// Errors instance, even if no errors.
		$key = BINDING_RESULT_MODEL_KEY_PREFIX . $this->getObjectName();
		$model[$key] =& $this;
		// Mapping from name to target object.
		$model[$this->getObjectName()] =& $this->getTarget();
		return $model;
	}

	/**
	 * Mark the specified disallowed field as suppressed.
	 * <p>The data binder invokes this for each field value that was
	 * detected to target a disallowed field.
	 * @see DataBinder#setAllowedFields
	 */
	function recordSuppressedField($fieldName) {
		$this->suppressedFields[] = $fieldName;
	}

	/**
	 * Return the list of fields that were suppressed during the bind process.
	 * <p>Can be used to determine whether any field values were targetting
	 * disallowed fields.
	 * @see DataBinder#setAllowedFields
	 */
	function getSuppressedFields() {
		return $this->suppressedFields;
	}

	function &getTarget() {
		return $this->target;
	}

	/**
	 * Determine the canonical field name for the given field.
	 * <p>The default implementation simply returns the field name as-is.
	 * @param field the original field name
	 * @return the canonical field name
	 */
	function _canonicalFieldName($field) {
		return BeanWrapperUtils::canonicalPropertyName($field);
	}

	function getFieldType($field) {
		$bw =& $this->getBeanWrapper();
		return $bw->getPropertyType($field);
	}

	/**
	 * Extract the actual field value for the given field.
	 * @param field the field to check
	 * @return the current value of the field
	 */
	function &getActualFieldValue($field) {
		$bw =& $this->getBeanWrapper();
		return $bw->getPropertyValue($field);
	}



	/**
	 * Format the given value for the specified field.
	 * <p>The default implementation simply returns the field value as-is.
	 * @param field the field to check
	 * @param value the value of the field (either a rejected value
	 * other than from a binding error, or an actual field value)
	 * @return the formatted value
	 */
	function _formatFieldValue($field, $value) {
		$customEditor =& $this->getCustomEditor($field);
		if (!is_null($customEditor)) {
			$customEditor->setValue($value);
			$textValue = $customEditor->getAsText();
			// If the PropertyEditor returned null, there is no appropriate
			// text representation for this value: only use it if non-null.
			if (!is_null($textValue)) {
				return $textValue;
			}
		}
		return $value;
	}

	function &getCustomEditor($field) {
		$fixedField = $this->_fixedField($field);
		$bw =& $this->getBeanWrapper();
		$type = $bw->getPropertyType($fixedField);
		$pe =& $bw->findCustomEditor($type, $fixedField);
		if($pe == null) $pe =& $bw->getDefaultEditor($type);
		return $pe;
	}

	function &getBeanWrapper() {
		if(is_null($this->beanWrapper)) {
			$this->beanWrapper =& new BeanWrapper($this->getTarget());
		}
		return $this->beanWrapper;
	}

}
?>
