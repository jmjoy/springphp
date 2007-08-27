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
 * Request
 *
 * @package		Redstart
 * @subpackage  HTTP
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class Request {

	var $locale = null;
	var $theme = null;
	var $attributes = array();
	
	var $ipAddress = FALSE;

	function Request() {

		if ( get_magic_quotes_gpc() ) {
			$_REQUEST= stripslashes_deep($_REQUEST);
			$_GET    = stripslashes_deep($_GET   );
			$_POST   = stripslashes_deep($_POST  );
			$_COOKIE = stripslashes_deep($_COOKIE);
		}

		foreach (array($_GET, $_POST, $_COOKIE, $_REQUEST) as $global)
		{
			if ( ! is_array($global))
			{
				global $global;
				$$global = NULL;
			}
			else
			{
				foreach ($global as $key => $val)
				{
					global $$key;
					$$key = NULL;
					$global[$key] = clean_input_data($val);
				}
			}
		}

		// Fix for IIS, which doesn't set REQUEST_URI
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME']; // Does this work under CGI?

			// Append the query string if it exists and isn't null
			if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
				$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
			}
		}

		// Fix for PHP as CGI hosts that set SCRIPT_FILENAME to something ending in php.cgi for all requests
		if ( isset($_SERVER['SCRIPT_FILENAME']) && ( strpos($_SERVER['SCRIPT_FILENAME'], 'php.cgi') == strlen($_SERVER['SCRIPT_FILENAME']) - 7 ) )
			$_SERVER['SCRIPT_FILENAME'] = $_SERVER['PATH_TRANSLATED'];

		// Fix for Dreamhost and other PHP as CGI hosts
		if ( strstr( $_SERVER['SCRIPT_NAME'], 'php.cgi' ) )
			unset($_SERVER['PATH_INFO']);

		// Fix empty PHP_SELF
		$PHP_SELF = $_SERVER['PHP_SELF'];
		if ( empty($PHP_SELF) )
			$_SERVER['PHP_SELF'] = $PHP_SELF = preg_replace("/(\?.*)?$/",'',$_SERVER["REQUEST_URI"]);

		if ( isset($_SERVER['HTTP_PC_REMOTE_ADDR']) )
			$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_PC_REMOTE_ADDR'];

	}

	function setAttribute($name, &$value) {
		$this->attributes[$name] =& $value;
	}

	function &getAttribute($name) {
		return $this->attributes[$name];
	}

	function getAttributeNames() {
		return array_keys($this->attributes);
	}

	function removeAttribute($name) {
		if(isset($this->attributes[$name])) unset($this->attributes[$name]);
	}

	function &getParameter($name, $xssClean = null) {
		if(!isset($_REQUEST[$name])) return _null();

		$xss_clean = is_xss_clean($xssClean);

		if ($xss_clean === TRUE)
		{
			if (is_array($_REQUEST[$name]))
			{
				foreach($_REQUEST[$name] as $key => $val)
				{
					$_REQUEST[$name][$key] = xss_clean($val);
				}
			}
			else
			{
				$val = xss_clean($_REQUEST[$name]);
				return $val;
			}
		}

		return $_REQUEST[$name];
	}




	function getParameterNames() {
		return array_keys($_REQUEST);
	}

	function &getParameterMap($xssClean = null) {

		$xss_clean = is_xss_clean($xssClean);

		if ($xss_clean === TRUE)
		{
			foreach($_REQUEST as $name => $value)
			{

				if (is_array($value))
				{
					foreach($value as $key => $val)
					{
						$_REQUEST[$name][$key] = xss_clean($val);
					}
				}
				else
				{
					$_REQUEST[$name] = xss_clean($value);
				}
			}
		}

		return $_REQUEST;
	}

	function &getUploadedFile($name) {
		if(!isset($_FILES[$name])) return _null();

		if(is_array($_FILES[$name]['name'])) {
			$result = array();
			foreach($_FILES[$name]['name'] as $key => $name) {
				$file = new UploadedFile($name, $_FILES[$name]['tmp_name'][$key], $_FILES[$name]['size'][$key], $_FILES[$name]['type'][$key], $_FILES[$name]['error'][$key] );
				$result[] =& $file;
			}
			return $result;
		}else {
			$file = new UploadedFile($_FILES[$name]['name'], $_FILES[$name]['tmp_name'], $_FILES[$name]['size'], $_FILES[$name]['type'], $_FILES[$name]['error']);
			return $file;
		}
	}

	function getUploadedFiles() {
		$result = array();
		foreach((array)array_keys($_FILES) as $name) {
			$result[$name] = $this->getUploadedFile($name);
		}
		return $result;
	}

/*
	function get($index = '', $xss_clean = FALSE)
	{
		if ( ! isset($_GET[$index]))
		{
			return FALSE;
		}

		if ($xss_clean === TRUE)
		{
			if (is_array($_GET[$index]))
			{
				foreach($_GET[$index] as $key => $val)
				{
					$_GET[$index][$key] = xss_clean($val);
				}
			}
			else
			{
				return xss_clean($_GET[$index]);
			}
		}

		return $_GET[$index];
	}

	function post($index = '', $xss_clean = FALSE)
	{
		if ( ! isset($_POST[$index]))
		{
			return FALSE;
		}

		if ($xss_clean === TRUE)
		{
			if (is_array($_POST[$index]))
			{
				foreach($_POST[$index] as $key => $val)
				{
					$_POST[$index][$key] = xss_clean($val);
				}
			}
			else
			{
				return xss_clean($_POST[$index]);
			}
		}

		return $_POST[$index];
	}
*/
	function getCookieValue($index = '', $xss_clean = null)
	{
		if ( ! isset($_COOKIE[$index]))
		{
			return FALSE;
		}

		$xss_clean = is_xss_clean($xss_clean);

		if ($xss_clean === TRUE)
		{
			if (is_array($_COOKIE[$index]))
			{
				$cookie = array();
				foreach($_COOKIE[$index] as $key => $val)
				{
					$cookie[$key] = xss_clean($val);
				}

				return $cookie;
			}
			else
			{
				return xss_clean($_COOKIE[$index]);
			}
		}
		else
		{
			return $_COOKIE[$index];
		}
	}

	function server($index = '', $xss_clean = FALSE)
	{
		if ( ! isset($_SERVER[$index]))
		{
			return FALSE;
		}

		if ($xss_clean === TRUE)
		{
			return xss_clean($_SERVER[$index]);
		}

		return $_SERVER[$index];
	}

	function &getSession() {
		return AppContext::createAutowiredService('Session');
	}

	function &getTheme() {
		return $this->theme;
	}

	function &getLocale() {
		return $this->locale;
	}

	function &getLocaleFromHeaders() {
		$languages = preg_replace('/(;q=.+)/i', '', trim($_SERVER['HTTP_ACCEPT_LANGUAGE']));

		list($variant, $lang) = explode(',', $languages);
		list($lang2, $country) = explode('-',$variant);


		$locale =& new Locale($lang, $country, $variant);

		return $locale;
	}

	function getQueryString() {
		return $this->server('QUERY_STRING');
	}

	function getMethod() {
		return $this->server('REQUEST_METHOD');
	}


	function getRequestURI() {
		return $this->server('REQUEST_URI');
	}

	function &getUserAgent() {
		return AppContext::createAutowiredService('UserAgent');
	}

	function isReferral() {
		return ( ! isset($_SERVER['HTTP_REFERER']) OR $_SERVER['HTTP_REFERER'] == '') ? FALSE : TRUE;
	}

	function getReferrer() {
		return ( ! isset($_SERVER['HTTP_REFERER']) OR $_SERVER['HTTP_REFERER'] == '') ? '' : trim($_SERVER['HTTP_REFERER']);
	}

	function getAcceptedLanguages() {
		if ((count($this->languages) == 0) AND isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) AND $_SERVER['HTTP_ACCEPT_LANGUAGE'] != '')
		{
			$languages = preg_replace('/(;q=.+)/i', '', trim($_SERVER['HTTP_ACCEPT_LANGUAGE']));

			$this->languages = explode(',', $languages);
		}

		if (count($this->languages) == 0)
		{
			$this->languages = array('Undefined');
		}

		return $this->languages;

	}

	function getAcceptedCharsets() {
		if ((count($this->charsets) == 0) AND isset($_SERVER['HTTP_ACCEPT_CHARSET']) AND $_SERVER['HTTP_ACCEPT_CHARSET'] != '')
		{
			$charsets = preg_replace('/(;q=.+)/i', '', trim($_SERVER['HTTP_ACCEPT_CHARSET']));

			$this->charsets = explode(',', $charsets);
		}

		if (count($this->charsets) == 0)
		{
			$this->charsets = array('Undefined');
		}

	}

	function doesAcceptLanguage($lang = 'en')
	{
		return (in_array(strtolower($lang), $this->languages(), TRUE)) ? TRUE : FALSE;
	}


	function doesAcceptCharset($charset = 'utf-8')
	{
		return (in_array(strtolower($charset), $this->charsets(), TRUE)) ? TRUE : FALSE;
	}

	function getPathTranslated() {
		return ( ! isset($_SERVER['PATH_TRANSLATED']) OR $_SERVER['PATH_TRANSLATED'] == '') ? '' : trim($_SERVER['PATH_TRANSLATED']);
	}

	function getHost() {
		return $this->server('HTTP_HOST');
	}

	function getServerName() {
		return $this->server('SERVER_NAME');
	}

	function getServerIP() {
		return $this->server('SERVER_ADDR');
	}

	function getServerPort() {
		return $this->server('SERVER_PORT');
	}

	function isSecure() {
		return isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on';
	}

	function getServerProtocol() {
		return $this->server('SERVER_PROTOCOL');
	}

	function getScheme() {
		return $this->isSSL()?'https':'http';
	}

	function getCurrentURL() {
		return $this->getDomainRoot().$this->getRequestURI();
	}

	function getContextRoot() {
		$requestUri = $this->getRequestURI();
		$requestUri = str_replace($this->getContextPath(), '',$requestUri);
		if(strpos($requestUri, '?'))
			$requestUri = substr($requestUri, 0, strpos($requestUri,'?'));

		if(strpos($requestUri, '.')) {
			$requestUri = substr($requestUri, 0, strpos($requestUri, '.'));
			$requestUri = substr($requestUri, 0, strrpos($requestUri, '/'));
		}

		return $requestUri;
	}

	function getDomainRoot() {
		$protocol = $this->isSecure()?'https':'http';
		$hostname = $this->getServerName();
		$port = $this->getServerPort()==80?"":":".$this->getServerPort();
		return $protocol.'://'.$hostname.$port;
	}

	function getFullContextRoot() {
		return $this->getDomainRoot().$this->getContextRoot();
	}

	//strips contextPath and query string from full current url
	function getSystemURL() {
		$currentUrl = $this->getCurrentURL();
		if(strpos($currentUrl, '?'))
			$currentUrl = substr($currentUrl, 0, strpos($currentUrl,'?'));

		return str_replace($this->getContextPath(), '',$currentUrl);
	}

	function getContextPath() {

		// Is there a PATH_INFO variable?
		// Note: some servers seem to have trouble with getenv() so we'll test it two ways
		$path = (isset($_SERVER['PATH_INFO'])) ? $_SERVER['PATH_INFO'] : @getenv('PATH_INFO');
		if ($path != '' AND $path != "/".SELF)
		{
			return $path;
		}

		// No QUERY_STRING?... Maybe the ORIG_PATH_INFO variable exists?
		$path = (isset($_SERVER['ORIG_PATH_INFO'])) ? $_SERVER['ORIG_PATH_INFO'] : @getenv('ORIG_PATH_INFO');
		if ($path != '' AND $path != "/".SELF)
		{
			return $path;
		}

		// no more options
		return $path;
	}


	function getIPAddress()
	{
		if ($this->ipAddress !== FALSE)
		{
			return $this->ipAddress;
		}

		if ($this->server('REMOTE_ADDR') AND $this->server('HTTP_CLIENT_IP'))
		{
			 $this->ipAddress = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif ($this->server('REMOTE_ADDR'))
		{
			 $this->ipAddress = $_SERVER['REMOTE_ADDR'];
		}
		elseif ($this->server('HTTP_CLIENT_IP'))
		{
			 $this->ipAddress = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif ($this->server('HTTP_X_FORWARDED_FOR'))
		{
			 $this->ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}

		if ($this->ipAddress === FALSE)
		{
			$this->ipAddress = '0.0.0.0';
			return $this->ipAddress;
		}

		if (strstr($this->ipAddress, ','))
		{
			$x = explode(',', $this->ipAddress);
			$this->ipAddress = end($x);
		}

		if ( ! is_valid_ip($this->ipAddress))
		{
			$this->ipAddress = '0.0.0.0';
		}

		return $this->ipAddress;
	}



}

?>