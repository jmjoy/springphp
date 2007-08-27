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
//FIXME: use getters

/**
 * UserAgent
 *
 * @package		Redstart
 * @subpackage  HTTP
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class UserAgent {

	var $platforms = array (
					'windows nt 6.0'	=> 'Windows Longhorn',
					'windows nt 5.2'	=> 'Windows 2003',
					'windows nt 5.0'	=> 'Windows 2000',
					'windows nt 5.1'	=> 'Windows XP',
					'windows nt 4.0'	=> 'Windows NT 4.0',
					'winnt4.0'			=> 'Windows NT 4.0',
					'winnt 4.0'			=> 'Windows NT',
					'winnt'				=> 'Windows NT',
					'windows 98'		=> 'Windows 98',
					'win98'				=> 'Windows 98',
					'windows 95'		=> 'Windows 95',
					'win95'				=> 'Windows 95',
					'windows'			=> 'Unknown Windows OS',
					'os x'				=> 'Mac OS X',
					'ppc mac'			=> 'Power PC Mac',
					'freebsd'			=> 'FreeBSD',
					'ppc'				=> 'Macintosh',
					'linux'				=> 'Linux',
					'debian'			=> 'Debian',
					'sunos'				=> 'Sun Solaris',
					'beos'				=> 'BeOS',
					'apachebench'		=> 'ApacheBench',
					'aix'				=> 'AIX',
					'irix'				=> 'Irix',
					'osf'				=> 'DEC OSF',
					'hp-ux'				=> 'HP-UX',
					'netbsd'			=> 'NetBSD',
					'bsdi'				=> 'BSDi',
					'openbsd'			=> 'OpenBSD',
					'gnu'				=> 'GNU/Linux',
					'unix'				=> 'Unknown Unix OS'
				);


	// The order of this array should NOT be changed. Many browsers return
	// multiple browser types so we want to identify the sub-type first.
	var $browsers = array(
					'Opera'				=> 'Opera',
					'MSIE'				=> 'Internet Explorer',
					'Internet Explorer'	=> 'Internet Explorer',
					'Shiira'			=> 'Shiira',
					'Firefox'			=> 'Firefox',
					'Chimera'			=> 'Chimera',
					'Phoenix'			=> 'Phoenix',
					'Firebird'			=> 'Firebird',
					'Camino'			=> 'Camino',
					'Netscape'			=> 'Netscape',
					'OmniWeb'			=> 'OmniWeb',
					'Mozilla'			=> 'Mozilla',
					'Safari'			=> 'Safari',
					'Konqueror'			=> 'Konqueror',
					'icab'				=> 'iCab',
					'Lynx'				=> 'Lynx',
					'Links'				=> 'Links',
					'hotjava'			=> 'HotJava',
					'amaya'				=> 'Amaya',
					'IBrowse'			=> 'IBrowse'
				);

	var $mobiles = array(
					'mobileexplorer'	=> 'Mobile Explorer',
					'openwave'			=> 'Open Wave',
					'opera mini'		=> 'Opera Mini',
					'operamini'			=> 'Opera Mini',
					'elaine'			=> 'Palm',
					'palmsource'		=> 'Palm',
					'digital paths'		=> 'Palm',
					'avantgo'			=> 'Avantgo',
					'xiino'				=> 'Xiino',
					'palmscape'			=> 'Palmscape',
					'nokia'				=> 'Nokia',
					'ericsson'			=> 'Ericsson',
					'blackBerry'		=> 'BlackBerry',
					'motorola'			=> 'Motorola'
				);

	// There are hundreds of bots but these are the most common.
	var $robots = array(
					'googlebot'			=> 'Googlebot',
					'msnbot'			=> 'MSNBot',
					'slurp'				=> 'Inktomi Slurp',
					'yahoo'				=> 'Yahoo',
					'askjeeves'			=> 'AskJeeves',
					'fastcrawler'		=> 'FastCrawler',
					'infoseek'			=> 'InfoSeek Robot 1.0',
					'lycos'				=> 'Lycos'
				);



	var $agent		= NULL;

	var $is_browser	= FALSE;
	var $is_robot	= FALSE;
	var $is_mobile	= FALSE;

	var $languages	= array();
	var $charsets	= array();

	var $platform	= '';
	var $browser	= '';
	var $version	= '';
	var $mobile		= '';
	var $robot		= '';

	function UserAgent() {

		if (isset($_SERVER['HTTP_USER_AGENT']))
		{
			$this->agent = trim($_SERVER['HTTP_USER_AGENT']);
		}

		if ( ! is_null($this->agent))
		{
			$this->_compile_data();
		}

	}

	function _compile_data() {

		$this->_set_platform();

		foreach (array('_set_browser', '_set_robot', '_set_mobile') as $function)
		{
			if ($this->$function() === TRUE)
			{
				break;
			}
		}


	}

	function _set_platform()
	{
		if (is_array($this->platforms) AND count($this->platforms) > 0)
		{
			foreach ($this->platforms as $key => $val)
			{
				if (preg_match("|".preg_quote($key)."|i", $this->agent))
				{
					$this->platform = $val;
					return TRUE;
				}
			}
		}
		$this->platform = 'Unknown Platform';
	}

	function _set_browser()
	{
		if (is_array($this->browsers) AND count($this->browsers) > 0)
		{
			foreach ($this->browsers as $key => $val)
			{
				if (preg_match("|".preg_quote($key).".*?([0-9\.]+)|i", $this->agent, $match))
				{
					$this->is_browser = TRUE;
					$this->version = $match[1];
					$this->browser = $val;
					$this->_set_mobile();
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Robot
	 *
	 * @access	private
	 * @return	bool
	 */
	function _set_robot()
	{
		if (is_array($this->robots) AND count($this->robots) > 0)
		{
			foreach ($this->robots as $key => $val)
			{
				if (preg_match("|".preg_quote($key)."|i", $this->agent))
				{
					$this->is_robot = TRUE;
					$this->robot = $val;
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the Mobile Device
	 *
	 * @access	private
	 * @return	bool
	 */
	function _set_mobile()
	{
		if (is_array($this->mobiles) AND count($this->mobiles) > 0)
		{
			foreach ($this->mobiles as $key => $val)
			{
				if (FALSE !== (strpos(strtolower($this->agent), $key)))
				{
					$this->is_mobile = TRUE;
					$this->mobile = $val;
					return TRUE;
				}
			}
		}
		return FALSE;
	}

	function is_browser()
	{
		return $this->is_browser;
	}

	// --------------------------------------------------------------------

	/**
	 * Is Robot
	 *
	 * @access	public
	 * @return	bool
	 */
	function is_robot()
	{
		return $this->is_robot;
	}

	// --------------------------------------------------------------------

	/**
	 * Is Mobile
	 *
	 * @access	public
	 * @return	bool
	 */
	function is_mobile()
	{
		return $this->is_mobile;
	}

	function agent_string()
	{
		return $this->agent;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Platform
	 *
	 * @access	public
	 * @return	string
	 */
	function platform()
	{
		return $this->platform;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Browser Name
	 *
	 * @access	public
	 * @return	string
	 */
	function browser()
	{
		return $this->browser;
	}

	// --------------------------------------------------------------------

	/**
	 * Get the Browser Version
	 *
	 * @access	public
	 * @return	string
	 */
	function version()
	{
		return $this->version;
	}

	// --------------------------------------------------------------------

	/**
	 * Get The Robot Name
	 *
	 * @access	public
	 * @return	string
	 */
	function robot()
	{
		return $this->robot;
	}
	// --------------------------------------------------------------------

	/**
	 * Get the Mobile Device
	 *
	 * @access	public
	 * @return	string
	 */
	function mobile()
	{
		return $this->mobile;
	}


	function getAgentString() {
		return $this->agent;
	}


}
?>
