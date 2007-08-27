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
 * RedirectView
 *
 * @package		Redstart
 * @subpackage  View
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class RedirectView extends View {

	var $contextRelative = false;

	function RedirectView() {}

	function renderMergedOutputModel(&$model, &$request, &$response) {

		$targetUrl = '';
		if ($this->contextRelative == TRUE && str_starts_with($this->url, "/")) {
			// Do not apply context path to relative URLs.
			$targetUrl .= $request->getContextPath();
		}
		$targetUrl .= $this->url;

		$targetUrl = $this->appendQueryProperties($targetUrl, $model);

		log_message(LOG_DEBUG, 'Redirecting to: '.$targetUrl);

		$response->setStatus(SC_SEE_OTHER);
		$response->sendRedirect($targetUrl);
		return true;
	}

	function appendQueryProperties($targetUrl, &$model) {

		// Extract anchor fragment, if any.
		$fragment = null;
		$anchorIndex = strpos($targetUrl,'#');
		if ($anchorIndex !== FALSE) {
			$fragment = substr($targetUrl, $anchorIndex);
			$targetUrl = substr($targetUrl, 0, $anchorIndex);
		}

		// If there aren't already some parameters, we need a "?".
		$first = strpos($this->url, '?') === FALSE?true:false;
		foreach((array) $model as $name => $value) {
			if(!is_array($value) && !is_object($value)) {

				if($first == true) {
					$targetUrl .= '?';
					$first = false;
				}else {
					$targetUrl .= '&';
				}

				$encodedKey = urlencode("".$name);
				$encodedValue = urlencode("".$value);
				$targetUrl .= $encodedKey . '='.$encodedValue;
			}

		}

		// Append anchor fragment, if any, to end of URL.
		if ($fragment != null) {
			$targetUrl .= $fragment;
		}
		return $targetUrl;
	}


}

?>