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
 * RequestToViewNameTranslator
 *
 * @package		Redstart
 * @subpackage  ViewNameTranslator
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class RequestToViewNameTranslator {

	var $prefix = '';
	var $suffix = '';
	var $defaultViewName = 'index';
	var $separator = '/';
	var $stripLeadingSlash = true;
	var $stripExtension = true;
	//var $urlPathHelper;

	function RequestToViewNameTranslator() {
		//$this->urlPathHelper =& new UrlPathHelper();
	}

	function &transformPath($lookupPath) {
		$path = $lookupPath;
		if ($this->stripLeadingSlash && str_starts_with($path, '/')) {
			$path = substr($path, 1);
		}
		if ($this->stripExtension) {
			$path = strip_filename_extension($path);
		}
		if ('/' != $this->separator) {
			$path = str_replace($this->separator, '/', $path);
		}
		return $path;
	}

	function &getViewName(&$request) {
		$lookupPath = $request->getContextPath();
		$view = $this->transformPath($lookupPath);
		if(empty($view)) $view = $this->defaultViewName;
		$viewName = $this->prefix .$view. $this->suffix;
		return $viewName;
	}
}

?>