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
 * MessageCodesResolver
 *
 * @package		Redstart
 * @subpackage  Validation
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class MessageCodesResolver {

	var $prefix = "";


	function &resolveMessageCodes($errorCode, $objectName, $field = null, $fieldType = null) {
		$codelist = array();
		if($field == null && $fieldType == null)
		{
			$codelist[] = $this->_postProcessMessageCode($errorCode.CODE_SEPARATOR.$objectName);
			$codelist[] = $this->_postProcessMessageCode($errorCode);
			return $codelist;
		}

		$fieldList = array();
		$this->_buildFieldList($field, &$fieldList);
		foreach($fieldList as $fieldInList) {
			$codelist[] = $this->_postProcessMessageCode($errorCode . CODE_SEPARATOR . $objectName . NESTED_PATH_SEPARATOR . $fieldInList);
		}

		$dotIndex = strrpos($field,'.');
		if ($dotIndex !== FALSE) {
			$this->_buildFieldList(substr($field,$dotIndex + 1), &$fieldList);
		}
		foreach($fieldList as $fieldInList) {
			$codelist[] = $this->_postProcessMessageCode($errorCode . CODE_SEPARATOR . $fieldInList);
		}
		if ($fieldType != null && !empty($fieldType)) {
			$codelist[] = $this->_postProcessMessageCode($errorCode . CODE_SEPARATOR . $fieldType);
		}
		$codelist[] = $this->_postProcessMessageCode($errorCode);
		return $codelist;
	}


	function _buildFieldList($field, &$fieldList) {
		$fieldList[] = $field;
		$plainField = $field;
		$keyIndex = strrpos($plainField, '[');
		while ($keyIndex !== FALSE) {
			$endKeyIndex = strpos($plainField, ']', $keyIndex);
			if ($endKeyIndex !== FALSE) {
				$plainField = substr($plainField, 0, $keyIndex) . substr($plainField, $endKeyIndex + 1);
				$fieldList[] =  $plainField;
				$keyIndex = strrpos($plainField, '[');
			}
			else {
				$keyIndex = FALSE;
			}
		}
	}

	function _postProcessMessageCode($code) {
		return ($this->prefix==null?"":$this->prefix) . $code;
	}

}

?>
