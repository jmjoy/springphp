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
 * SimpleUrlHandlerMapping
 *
 * @package		Redstart
 * @subpackage  Handler
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class SimpleUrlHandlerMapping extends AbstractUrlHandlerMapping {

	var $mappings = array();

	function SimpleUrlHandlerMapping($mappings) {
		parent::AbstractUrlHandlerMapping();
		$this->mappings = $mappings;
		$this->_registerHandlers();
	}

	function _registerHandlers() {
		foreach($this->mappings as $url => $handler) {
			if(!str_starts_with($url, '/'))
				$url = '/'.$url;

			$this->registerHandler($url, $handler);

		}

	}


}

?>