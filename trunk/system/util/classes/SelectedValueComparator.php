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
 * SelectedValueComparator
 *
 * @package		Redstart
 * @subpackage  Validation
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class SelectedValueComparator {

	function isSelectedUsingBeanWrapper(&$bindStatus, $candidateValue, $propertyName) {
		$boundValue = SelectedValueComparator::_getBoundValue(&$bindStatus);
		if(is_null($propertyName) || (!is_object($boundValue) && !is_array($boundValue))) return false;

		if(is_array($boundValue)) {
			foreach($boundValue as $value) {
				$bw = new BeanWrapper($value);
				$testValue = $bw->getPropertyValue($propertyName);
				if ($testValue == $candidateValue) return true;
			}
		}else {
			$bw = new BeanWrapper($boundValue);
			$testValue = $bw->getPropertyValue($propertyName);
			return ($testValue == $candidateValue);
		}

		return false;
	}

	function isSelected(&$bindStatus, $candidateValue) {
		$boundValue = SelectedValueComparator::_getBoundValue(&$bindStatus);

		if(is_null($boundValue)) return is_null($candidateValue);
		//if(log_enabled(LOG_INFO))
		//	log_message(LOG_INFO, 'SelectedValueComparator is testing ['.var_export($boundValue, true).'] against ['.var_export($candidateValue, true).']' );

		$selected = false;

		if(is_array($boundValue)){
			$selected = SelectedValueComparator::_arrayCompare(&$boundValue, &$candidateValue, &$bindStatus);
		}

		if(!$selected) {
			if($boundValue == $candidateValue)
				$selected = true;
			else
				$selected = SelectedValueComparator::_exhaustiveCompare($boundValue, $candidateValue, $bindStatus->getEditor());
		}

		//if(log_enabled(LOG_INFO))
		//	log_message(LOG_INFO, 'SelectedValueComparator result was = '.$selected );

		return $selected;
	}

	function _arrayCompare(&$boundValue, &$candidateValue, &$bindStatus) {
		if(in_array($candidateValue, $boundValue))
			return true;
		else {
			foreach($boundValue as $val) {
				if(SelectedValueComparator::_exhaustiveCompare($val, $candidateValue, $bindStatus->getEditor()))
					return true;
			}
		}

		return false;
	}

	function _exhaustiveCompare(&$val, &$candidateValue, &$propertyEditor) {

		$candidateExport = var_export($candidateValue, true);
		$valExport = var_export($val, true);

		if($candidateExport == $valExport)
			return true;
		else if(!is_null($propertyEditor) && !is_string($candidateValue)) {
			$result = false;

			// try PE-based comparison (PE should *not* be allowed to escape creating thread)
			$originalValue = $propertyEditor->getValue();
			$propertyEditor->setValue($candidateValue);
			$value = $propertyEditor->getAsText();
			if(!is_null($value) && ($value === $val)) $result =  true;
			$propertyEditor->setValue($originalValue);
			return $result;

		}
		else if(!is_null($propertyEditor) && is_string($candidateValue)) {

			$result = false;

			// try PE-based comparison (PE should *not* be allowed to escape creating thread)
			$originalValue = $propertyEditor->getValue();
			$propertyEditor->setAsText($candidateValue);
			$value = $propertyEditor->getValue();
			if(is_null($value) && is_null($val)) $result =  true;
			else if($value === $val) $result =  true;

			$propertyEditor->setValue($originalValue);

			return $result;
		}

		return false;
	}

	function _getBoundValue(&$bindStatus) {
		if (is_null($bindStatus)) {
			return null;
		}
		if ($bindStatus->getEditor() != null) {
			$editor = $bindStatus->getEditor();
			$editorValue = $editor->getValue();
			if ($editorValue != null) {
				return $editorValue;
			}
		}
		return $bindStatus->getValue();
	}

}
?>
