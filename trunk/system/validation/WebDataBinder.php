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
 * WebDataBinder
 *
 * @package		Redstart
 * @subpackage  Validation
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */

class WebDataBinder extends DataBinder{

	var $fieldMarkerPrefix = DEFAULT_FIELD_MARKER_PREFIX;

	var $bindEmptyUploadedFiles = true;

	function WebDataBinder (&$target, $objectName = DEFAULT_OBJECT_NAME) {
		parent::DataBinder(&$target, $objectName);
	}

	function &getFieldMarkerPrefix() {
		return $this->fieldMarkerPrefix;
	}

	function isBindEmptyUploadedFiles() {
		return $this->bindEmptyUploadedFiles;
	}

	function bind(&$nvps) {	
		$this->checkFieldMarkers(&$nvps);		
		return parent::bind(&$nvps);
	}

	function checkFieldMarkers(&$nvps) {
		if ($this->getFieldMarkerPrefix() != null) {
			$fieldMarkerPrefix = $this->getFieldMarkerPrefix();

			foreach($nvps as $name => $value) {

				if(str_starts_with($name, $fieldMarkerPrefix)) {
					$field = substr($name, strlen($fieldMarkerPrefix));
					if(!isset($nvps[$field])) {
						$bw =& $this->getBeanWrapper();
						$fieldType = $bw->getPropertyType($field);
						if(!is_null($fieldType))
							$nvps[$field] = $this->getEmptyValue($field, $fieldType);
					}
					unset($nvps[$name]);
				}

			}
		}
	}

	function getEmptyValue($field, $fieldType) {
		if (str_equals_icase($fieldType, 'boolean'))
			return FALSE;
		else if(is_array_type($fieldType))
			return array();
		else
			return null;
	}

	function bindUploadedFiles($uploadedFiles, &$nvps) {
		foreach($uploadedFiles as $name => $file) {
			if($this->isBindEmptyUploadedFiles() || !$file->isEmpty()) {
				$nvps[$name] = $file;
			}
		}
	}
}
?>
