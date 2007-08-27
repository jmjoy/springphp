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
 * ObjectError
 *
 * @package		Redstart
 * @subpackage  Validation
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class ObjectError extends MessageSourceResolvable {

	var $objectName = null;

	function ObjectError(&$objectName, &$codes, $arguments = null, $defaultMessage = null) {
		parent::MessageSourceResolvable($codes, $arguments, $defaultMessage);
		assert_not_null($objectName, 'Object name must not be null');
		$this->objectName =& $objectName;
	}

	function &getObjectName() {
		return $this->objectName;
	}

	function toString() {
		return 'Error in object '.  $this->objectName . "': " . $this->_resolvableToString();
	}

}


?>
