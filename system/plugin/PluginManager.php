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

include(BASEPATH.'plugin/Plugin.php');
/**
 * PluginManager
 *
 * @package		Redstart
 * @subpackage  Plugin
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class PluginManager {

	var $plugins = array();
	var $pluginsDir = null;

	function PluginManager() {
		$this->pluginsDir = APPPATH .'plugins/';

	}

	function autodiscover() {

		$handle = dir($this->pluginsDir);

		// Loop through each item
		while (false !== ($file = $handle->read())) {
			// If it is not an alias
			if ($file != "." && $file != "..") {
				$file = $this->pluginsDir . "/" . $file;

                // If it is a directory
				if (is_dir($file)) {
					$pluginDir = $file;
					$pluginName = basename($file);
					$this->addPlugin($pluginName, $pluginDir);
				}
			}
		}

		// Close Directory Handle
		$handle->close();

	}

	function &addPlugin($name, $directory) {

		if($this->pluginExists($name))
			show_error('PluginManager', 'Plugin with name "'.$name.'" exists already.');

		$plugin =& new Plugin($name, $directory);

		$pluginContext = file_exists($directory.'/plugin-context.yml')?$directory.'/plugin-context.yml':null;

		if($pluginContext) {
			AppContext::load($pluginContext);
		}

		$bootstrap = file_exists($directory.'/bootstrap'.EXT)?$directory.'/bootstrap'.EXT:null;
		if($bootstrap)
			include($bootstrap);


		$this->plugins[$name] =& $plugin;
		return $plugin;
	}

	function pluginExists($name) {
		return isset($this->plugins[$name]);
	}

	function &plugin($name) {
		if(pluginExists($name)) return $this->plugins[$name];
		else return _null();
	}
}


?>
