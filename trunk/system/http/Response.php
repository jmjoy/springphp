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
 * Response
 *
 * @package		Redstart
 * @subpackage  HTTP
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class Response {

	var $sc = SC_OK;
	var $headers = array();
	var $cookies = array();

	var $cacheExpiration = 0;
	var $shouldUseCache = true;


	function Response() {}

	function setCookie($cookie) {
		$this->cookies[] = $cookie;
	}

	function clearCookie($cookie) {
		if(is_a($cookie, 'Cookie')) {
			$array = $cookie->toArray();
			setcookie ($array[0], "", time() - 3600, $array[3], $array[4]);
		}else{
			setcookie ($cookie, "", time() - 3600);
		}
	}

	function setHeader($name, $value) {
		$this->headers[$name] = array($value);
	}

	function setDateHeader($name, $value) {
		if(is_numeric($value)) $value = rfc_date($value);
		$this->headers[$name] = array($value);
	}

	function addHeader($name, $value) {
		$this->headers[$name][] = $value;
	}

	function containsHeader($name) {
		return isset($this->headers[$name]);
	}

	function sendError($sc) {
		ob_end_clean();
		header('HTTP/1.0 '.$sc);
		exit;
	}

	function sendRedirect($location) {
		$this->_prepareResponse();
		header('Location: '.system_url($location));
		exit();
	}

	function setStatus($status_code) {
		$this->sc = $status_code;
	}

	function setCacheExpiration($time)
	{
		$this->cacheExpiration = ( ! is_numeric($time)) ? 0 : $time;
	}

	function start() {
		ob_start();
	}

	function clean() {
		ob_end_clean();
	}

	function _prepareResponse() {
	

		// headers
		foreach ($this->headers as $name => $values) {
			foreach($values as $value)
			{
				header($name . ': ' . $value, false);
				log_message(LOG_INFO, 'Response: send header "' . $name . '": "' . $value . '"');
			}
		}

		foreach($this->cookies as $cookie) {
			call_user_func_array('setcookie', $cookie->toArray() );
		}
	}

	function flush() {
		header('HTTP/1.0 '.$this->sc);
		log_message(LOG_INFO, 'Response: send status "' . $this->sc . '"');

		$this->_prepareResponse();
	
		$output = ob_get_contents();
		ob_end_clean();

		if ($this->cacheExpiration > 0)
		{
			$this->_writeCache($output);
		}

		$output = str_replace('{elapsed_time}', $this->_totalExecutionTime(), $output);

		$memory	 = ( ! function_exists('memory_get_usage')) ? '0' : round(memory_get_usage()/1024/1024, 2).'MB';
		$output = str_replace('{memory_usage}', $memory, $output);

		if (extension_loaded('zlib') && ini_get('zlib.output_compression') != true && isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE)
			ob_start('ob_gzhandler');

		echo $output;

	}

	function _totalExecutionTime() {
		global $timer_start;

		list($sm, $ss) = explode(' ', $timer_start);
		list($em, $es) = explode(' ', microtime());

		return number_format(($em + $es) - ($sm + $ss), 4);
	}

	function shouldUseCache() {
		return $this->shouldUseCache;
	}

	function setShouldUseCache($should) {
		$this->shouldUseCache = is_bool($should)?$should:FALSE;
	}

	function _writeCache($output) {
		global $request;

		$cache_dir = BASEPATH . '/cache/output/';
		$cache_file = $cache_dir . md5($request->getCurrentURL()) . '.php';

		if (!is_dir($cache_dir))
			mkdir($cache_dir);

		if (!$fp = @ fopen($cache_file, 'wb')) show_error('Response Error', 'Response - can\'t write cache file');

		$oldtime = time() + ($this->cacheExpiration * 60);

		$cache_contents = "<?php \n";
		$cache_contents .= "if (!defined('BASEPATH')) exit('No direct script access allowed'); \n";
		$cache_contents .= '$mytime = ' . $oldtime . ';'."\n";

		foreach ($this->headers as $name => $value) {
			$name = addslashes($name);
			$value = addslashes($value);
			$cache_contents .= "header(\"$name: $value\");\n";
		}
		$cache_contents .= " ?>\n";
		$cache_contents .= $output;

		flock($fp, LOCK_EX);
		fwrite($fp, $cache_contents);
		flock($fp, LOCK_UN);
		fclose($fp);
		@ chmod($cache_file, 0777);
	}

	function clearCache() {
		$cache_dir = BASEPATH . '/cache/output/';
		clear_directory($cache_dir);
	}

	function displayCache() {
		global $request;

		if($this->shouldUseCache() !== TRUE) return FALSE;

		$cache_dir = BASEPATH . '/cache/output/';
		$cache_file = $cache_dir . md5($request->getCurrentURL()) . '.php';

		if(file_exists($cache_file)) {

			// initialize $mytime var from cache_file
			$mytime = null;

			ob_start();

			include($cache_file);

			if(time() >= $mytime) {
				@unlink($cache_file);
				ob_end_clean();
				log_message(LOG_DEBUG, "Cache file has expired. File deleted");
				return FALSE;
			}

			ob_end_flush();
			log_message(LOG_DEBUG, "Cache file is current. Sending it to browser.");
			return TRUE;
		}

		return FALSE;
	}

}

?>