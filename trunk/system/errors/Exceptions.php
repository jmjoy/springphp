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
 * Exceptions
 *
 * @package		Redstart
 * @subpackage  Errors
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.codeigniter.com/
 * @since		Version 1.0
 * @filesource
 */
class Exceptions {

	var $_ob_level = null;
	var $_levels = array(
		E_WARNING			=>	'Warning',
		E_NOTICE			=>	'Notice',
		E_USER_ERROR		=>	'User Error',
		E_USER_WARNING		=>	'User Warning',
		E_USER_NOTICE		=>	'User Notice'
	);
	var $_level_log_map = array(
		E_WARNING			=>	LOG_WARNING,
		E_NOTICE			=>	LOG_INFO,
		E_USER_ERROR		=>	LOG_ERR,
		E_USER_WARNING		=>	LOG_WARNING,
		E_USER_NOTICE		=>	LOG_INFO
	);

	function Exceptions() {
		$this->ob_level = ob_get_level();
	}

	function &instance() {
		global $ex_instance;
		if($ex_instance == null) {
			$ex_instance =& new Exceptions();
		}
		return $ex_instance;
	}

	function show_error($heading, $message)
	{
		$message = '<p>'.implode('</p><p>', ( ! is_array($message)) ? array($message) : $message).'</p>';

		while (ob_get_level())
			ob_end_clean();

		ob_start();
		include(BASEPATH.'errors/views/error_general'.EXT);
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;
		exit;
	}


	function show_php_error($severity, $message, $filepath, $line)
	{
		$severity = ( ! isset($this->_levels[$severity])) ? $severity : $this->_levels[$severity];

		$filepath = str_replace("\\", "/", $filepath);

		// For safety reasons we do not show the full file path
		if (FALSE !== strpos($filepath, '/'))
		{
			$x = explode('/', $filepath);
			$filepath = $x[count($x)-2].'/'.end($x);
		}

		if (ob_get_level() > $this->ob_level + 1)
		{
			ob_end_flush();
		}
		ob_start();
		include(BASEPATH.'errors/views/error_php'.EXT);
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;
	}

	function log_exception($severity, $message, $filepath, $line)
	{
		$log_level = ( ! isset($this->_level_log_map[$severity])) ? LOG_DEBUG : $this->_level_log_map[$severity];

		if(function_exists('log_enabled')  && log_enabled($log_level))
		{
			$severity = ( ! isset($this->_levels[$severity])) ? $severity : $this->_levels[$severity];
			log_message($log_level, 'Severity: '.$severity.'  --> '.$message. ' '.$filepath.' '.$line);
		}
	}

}


function _exception_handler($severity, $message, $filepath, $line) {

	if ($severity == E_STRICT)
		return;

	$exceptions =& Exceptions::instance();

	if (($severity & error_reporting()) == $severity)
		$exceptions->show_php_error($severity, $message, $filepath, $line);

	$exceptions->log_exception($severity, $message, $filepath, $line);

}

set_error_handler('_exception_handler');

function show_error($heading, $message) {

	$exceptions =& Exceptions::instance();
	$exceptions->show_error($heading, $message);
}
?>
