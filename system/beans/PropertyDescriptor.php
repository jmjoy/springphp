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
 * PropertyDescriptor
 *
 * @package		Redstart
 * @subpackage  Beans
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class PropertyDescriptor {

	var $propertyName = null;
	var $propertyType = null;
	var $propertyEditor = null;

	function PropertyDescriptor($propertyName, $propertyType, $propertyEditor = null) {
		assert_not_null($propertyName, 'PropertyDescriptor: propertyName must not be null');
		assert_not_null($propertyType, 'PropertyDescriptor: propertyType must not be null');
		$this->propertyName =& $propertyName;
		$this->propertyType =& $propertyType;
		$this->propertyEditor =& $propertyEditor;
	}

	function &getPropertyName() {
		return $this->propertyName;
	}

	function &getPropertyType() {
		return $this->propertyType;
	}

	function &getPropertyEditor() {
		return $this->propertyEditor;
	}

}
?>
