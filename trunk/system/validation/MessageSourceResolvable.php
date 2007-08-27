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
 * MessageSourceResolvable
 *
 * @package		Redstart
 * @subpackage  Validation
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class MessageSourceResolvable {


	var $codes; //array

	var $arguments; //array

	var $defaultMessage;


	function MessageSourceResolvable(&$codes, $arguments = null, $defaultMessage = null) {

		if(is_a($codes, 'MessageSourceResolvable')) {
			$this->codes =& $codes->getCodes();
			$this->arguments =& $codes->getArguments;
			$this->defaultMessage =& $codes->getDefaultMessage();
			return;
		}

		if(!is_array($codes)) $codes = array($codes);

		$this->codes =& $codes;
		$this->arguments =& $arguments;
		$this->defaultMessage =& $defaultMessage;
	}


	function &getCodes() {
		return $this->codes;
	}

	function getCode() {
		return ($this->codes != null && count($this->codes) > 0) ? $this->codes[count($this->codes) - 1] : null;
	}

	function &getArguments() {
		return $this->arguments;
	}

	function &getDefaultMessage() {
		return $this->defaultMessage;
	}


	function _resolvableToString() {
		$buf = '';
		$buf .= "codes [". implode(',',$this->codes);
		$buf .= "]; arguments [" . implode(',',$this->arguments);
		$buf .= "]; default message [" . $this->defaultMessage .']';
		return $buf;
	}

	function toString() {
		return get_class($this) . ": " . $this->_resolvableToString();
	}

}
?>
