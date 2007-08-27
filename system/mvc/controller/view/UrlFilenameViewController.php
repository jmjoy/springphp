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
 * UrlFilenameViewController
 *
 * @package		Redstart
 * @subpackage  Controllers
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class UrlFilenameViewController extends AbstractUrlViewController {

	var $prefix = '';
	var $suffix = '';

	var $_viewNameCache = array();

	function getViewNameForRequest(&$request) {
		$uri = $this->extractOperableUrl($request);
		return $this->getViewNameForUrlPath($uri);
	}

	function extractOperableUrl($request) {
		$urlPath = $request->getAttribute(PATH_WITHIN_HANDLER_MAPPING_ATTRIBUTE);
		if (empty($urlPath)) {
			$urlPath = $request->getContextPath();
		}
		return $urlPath;
	}

	function getViewNameForUrlPath($uri) {
		$viewName = isset($this->viewNameCache[$uri])?$this->viewNameCache[$uri]:null;
		if ($viewName == null) {
			$viewName = $this->extractViewNameFromUrlPath($uri);
			$viewName = $this->postProcessViewName($viewName);
			$this->viewNameCache[$uri] = $viewName;
		}
		return $viewName;
	}

	function extractViewNameFromUrlPath($uri) {
		$start = (substr($uri,0,1) == '/') ? 1 : 0;
		$lastIndex = strrpos($uri,".");
		$end = ($lastIndex < 0) ? strlen($uri) : $lastIndex;
		return substr($uri, $start, $end);
	}

	function postProcessViewName($viewName) {
		return $this->prefix . $viewName . $this->suffix;
	}
}
?>
