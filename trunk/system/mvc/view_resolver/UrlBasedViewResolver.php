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
 * UrlBasedViewResolver
 *
 * @package		Redstart
 * @subpackage  ViewResolver
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class UrlBasedViewResolver extends ViewResolver{

	var $order = 2147483647; //largest int

	var $viewClass = 'PHPView'; // default view class
	var $prefix = "/views/"; //default views folder
	var $suffix = ".php"; //default extension
	var $viewNames = null;
	var $contentType;
	var $requestContextAttribute;
	var $staticAttributes = array();

	function UrlBasedViewResolver() {

	}

	function &createView($viewName, &$locale) {
		// If this resolver is not supposed to handle the given view,
		// return null to pass on to the next resolver in the chain.
		if (!$this->canHandle($viewName, $locale) || $this->viewClass == null) return _null();

		// Check for special "redirect:" prefix.
		if (str_starts_with($viewName,REDIRECT_URL_PREFIX)) {
			$redirectUrl = substr($viewName, strlen(REDIRECT_URL_PREFIX));
			$view =& AppContext::createAutowiredService('RedirectView');
			$view->serviceName = $viewName;
			$view->url = $redirectUrl;
			return $view;
		}

		$view =& $this->buildView($viewName);
		return $view;
	}

	function canHandle($viewName, $locale) {
		return ($this->viewNames == null || simple_pattern_match($this->viewNames, $viewName));
	}

	function &buildView($viewName) {
		$view =& AppContext::createAutowiredService($this->viewClass, $viewName);

		$view->serviceName = $viewName;
		$view->url = $this->prefix.$viewName .$this->suffix;
		if ($this->contentType != null) {
			$view->contentType = $this->contentType;
		}
		$view->requestContextAttribute =& $this->requestContextAttribute;
		$view->staticAttributes =& $this->staticAttributes;
		return $view;
	}

	function &resolveViewName($viewName, &$locale) {
		$view =& $this->createView($viewName, $locale);
		return $view;
	}

}

?>