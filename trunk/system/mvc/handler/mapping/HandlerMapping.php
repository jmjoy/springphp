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
 * HandlerMapping
 *
 * @abstract
 * @package		Redstart
 * @subpackage  Handler
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class HandlerMapping {

	var $order = 2147483647; //largest int
	var $defaultHandler;
	var $interceptors;

	function HandlerMapping() {}

	function &getInterceptors() {
		return $this->interceptors;
	}

	function &getHandler(&$request) {
		$handler =& $this->getHandlerInternal($request);
		if ($handler == null) {
			$handler =& $this->defaultHandler;
		}
		if ($handler == null) {
			return $handler;
		}
		// Service name or resolved handler?
		if (is_string($handler)) {
			$handler =& AppContext::service($handler);
		}
		$hec =& new HandlerExecutionChain(&$handler, &$this->interceptors);
		return $hec;
	}

}
?>
