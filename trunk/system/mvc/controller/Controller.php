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
 * Controller
 *
 * @abstract
 * @package		Redstart
 * @subpackage  Controllers
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class Controller {

	var $supportedMethods = array('HEAD', 'GET', 'POST');

	var $clientCacheSeconds = -1;

	var $serverCacheMinutes = -1;

	var $useExpiresHeader = true;
	var $useCacheControlHeader = true;

	function Controller() {}

	function &handleRequest(&$request, &$response) {
		$this->checkAndPrepare(&$request, &$response);
		return $this->handleRequestInternal(&$request, &$response);
	}

	function checkAndPrepare(&$request, &$response, $clientCacheSeconds = -1, $serverCacheMinutes = -1, $lastModified = false) {

		// Check whether we should support the request method.
		$method = $request->getMethod();
		if (!in_array($method,$this->supportedMethods)) {
			show_error('Controller', 'HTTP Method not supported with this request: '.$method);
		}

		if($clientCacheSeconds == -1) $clientCacheSeconds = $this->clientCacheSeconds;
		if($serverCacheMinutes == -1) $serverCacheMinutes = $this->serverCacheMinutes;

		// Do declarative cache control.
		// Revalidate if the controller supports last-modified.
		$this->applyClientCacheSeconds(&$response, $clientCacheSeconds, $lastModified);
		$response->setCacheExpiration($serverCacheMinutes);
	}


	function preventClientCaching(&$response) {
		$response->setHeader(HEADER_PRAGMA, "No-cache");

		if ($this->useExpiresHeader == true) {
			// HTTP 1.0 header
			$response->setDateHeader(HEADER_EXPIRES, 1);
		}
		if ($this->useCacheControlHeader == true) {
			// HTTP 1.1 header: "no-cache" is the standard value,
			// "no-store" is necessary to prevent caching on FireFox.
			$response->setHeader(HEADER_CACHE_CONTROL, "no-cache");
			$response->addHeader(HEADER_CACHE_CONTROL, "no-store");
		}
	}

	function clientCacheForSeconds(&$response, $seconds, $mustRevalidate = false) {
		if ($this->useExpiresHeader) {
			// HTTP 1.0 header
			$response->setDateHeader(HEADER_EXPIRES, time() + ($seconds));
		}
		if ($this->useCacheControlHeader) {
			// HTTP 1.1 header
			$headerValue = "max-age=" . $seconds;
			if ($mustRevalidate) {
				$headerValue .= ", must-revalidate";
			}
			$response->setHeader(HEADER_CACHE_CONTROL, $headerValue);
		}
	}

	function applyClientCacheSeconds(&$response, $seconds, $mustRevalidate = false) {
		if ($seconds > 0) {
			$this->clientCacheForSeconds(&$response, $seconds, $mustRevalidate);
		}
		else if ($seconds == 0) {
			$this->preventClientCaching(&$response);
		}
		// Leave caching to the client otherwise.
	}

	function &handleRequestInternal(&$request, &$response) {}


}
?>
