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
 * AbstractUrlHandlerMapping
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
class AbstractUrlHandlerMapping extends HandlerMapping{

	//TODO: prefix and suffix to url

	var $pathMatcher;

	var $rootHandler;

	var $handlerMap = array();

	function AbstractUrlHandlerMapping() {
		$this->pathMatcher =& new AntPathMatcher();
		$this->rootHandler = $this->defaultHandler;
	}



	function &getHandlerInternal(&$request) {
		$lookupPath = $request->getContextPath();

		if(log_enabled(LOG_DEBUG))
			log_message(LOG_DEBUG, "Looking up handler for [" . $lookupPath . "]");

		$handler = $this->_lookupHandler($lookupPath, $request);
		if ($handler == null) {
			// We need to care for the default handler directly, since we need to
			// expose the PATH_WITHIN_HANDLER_MAPPING_ATTRIBUTE for it as well.
			if ($lookupPath == "/" || $lookupPath == '') {
				$handler = $this->rootHandler;
			}
			if ($handler == null) {
				$handler = $this->defaultHandler;
			}
			if ($handler != null) {
				$this->_exposePathWithinMapping($lookupPath,$request);
			}
		}
		return $handler;
	}

	function _lookupHandler($urlPath, &$request) {
		// Direct match?
		$handler = null;
		if(isset($this->handlerMap[$urlPath])) {
			$handler = $this->handlerMap[$urlPath];
			if ($handler != null) {
				$this->_exposePathWithinMapping($urlPath, $request);
				return $handler;
			}
		}

		$bestPathMatch = null;
		foreach(array_keys($this->handlerMap) as $registeredPath) {
			if($this->pathMatcher->match($registeredPath, $urlPath) && ($bestPathMatch == null || strlen($bestPathMatch) <= strlen($registeredPath)))
				$bestPathMatch = $registeredPath;
		}

		if ($bestPathMatch != null) {
			$handler = $this->handlerMap[$bestPathMatch];
			$this->_exposePathWithinMapping($this->pathMatcher->extractPathWithinPattern($bestPathMatch, $urlPath), $request);
		}
		return $handler;
	}

	function _exposePathWithinMapping($pathWithinMapping, &$request) {
		$request->setAttribute(PATH_WITHIN_HANDLER_MAPPING_ATTRIBUTE, $pathWithinMapping);
	}

	// all handlers must be registered as strings, they MUST be lazy loaded (PHP)
	function registerHandler($urlPath, $serviceName) {
		assert_not_null($urlPath,'URL path must not be null');
		assert_not_null($serviceName,'Service name must not be null');

		if ($urlPath == "/") {
			if(log_enabled(LOG_DEBUG))
				log_message(LOG_DEBUG, "Root mapping to handler [".$serviceName."]");
			$this->rootHandler = $serviceName;
		}
		else if ($urlPath == "/*") {
			if(log_enabled(LOG_DEBUG))
				log_message(LOG_DEBUG, "Default mapping to handler [".$serviceName."]");
			$this->defaultHandler = $serviceName;
		}
		else {
			$this->handlerMap[$urlPath] = $serviceName;
			if(log_enabled(LOG_DEBUG))
				log_message(LOG_DEBUG, "Mapped URL path [" . $urlPath . "] onto handler [" . $serviceName . "]");
		}
	}

}


?>