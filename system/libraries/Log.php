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
 * Log
 *
 * @package		Redstart
 * @subpackage  Libraries
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class Log {

	var $path = null;
	var $threshold = null;
	var $dateFormat = null;
	var $_enabled	= TRUE;
	var $_levels	= array(
		LOG_DEBUG => 'DEBUG ',
		LOG_INFO => 'INFO  ',
		LOG_NOTICE => 'NOTICE',
		LOG_WARNING => 'WARN  ',
		LOG_ERR=>'ERROR ',
		LOG_CRIT=>'CRIT  ',
		LOG_EMERG=>'EMERG ');

	function Log(){}

	function initialize() {

		if($this->path==NULL) $this->path = BASEPATH .'logs/';
		if($this->threshold==NULL) $this->threshold = LOG_ERR;
		if($this->dateFormat == NULL) $this->dateFormat ='Y-m-d H:i:s';

		if ( ! is_dir($this->path) OR ! is_writable($this->path))
			$this->_enabled = FALSE;

		if($this->threshold == 0)
			$this->_enabled = FALSE;

	}

	function enabled($level) {
		if(!$this->_enabled || $this->threshold < $level) return false;
		return true;
	}

	function message($level = LOG_ERR, $msg)
	{

		if ($this->_enabled === FALSE || $this->threshold < $level)
			return FALSE;

		$filepath = $this->path.'log-'.date('Y-m-d').EXT;
		$message  = '';

		if ( ! file_exists($filepath))
		{
			$message .= "<"."?php  if (!defined('BASEPATH')) exit('No direct script access allowed'); ?".">\n\n";
		}

		if ( ! $fp = @fopen($filepath, "a"))
		{
			return FALSE;
		}

		$message .= $this->_levels[$level].' - '.date($this->dateFormat). ' --> '.$msg."\n";

		flock($fp, LOCK_EX);
		fwrite($fp, $message);
		flock($fp, LOCK_UN);
		fclose($fp);

		@chmod($filepath, 0666);
		return TRUE;
	}

}



?>