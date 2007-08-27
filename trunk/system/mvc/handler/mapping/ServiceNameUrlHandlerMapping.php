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
 * ServiceNameUrlHandlerMapping
 *
 * @package		Redstart
 * @subpackage  Handler
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class ServiceNameUrlHandlerMapping extends AbstractUrlHandlerMapping {

	function ServiceNameUrlHandlerMapping() {
		parent::AbstractUrlHandlerMapping();
		$this->_detectHandlers();
	}

	function _detectHandlers() {
		if(log_enabled(LOG_DEBUG))
			log_message(LOG_DEBUG, "Looking for URL mappings in application context");

		$service_points =& AppContext::getServicePoints();
		foreach($service_points as $name => $point) {
			$urls = $this->_determineUrlsForServicePoint($point);
			if(!empty($urls)) {
				foreach($urls as $url) {
					$this->registerHandler($url, $name);
				}
			}else{
				if(log_enabled(LOG_DEBUG))
					log_message(LOG_DEBUG, "Rejected service point '".$name ."': no URL paths identified");
			}


		}
	}

	function &_determineUrlsForServicePoint(&$point) {

		$urls = array();
		$name =& $point->name;
		if(str_starts_with($name, "/"))
			$urls[] = $name;

		$aliases =& $point->aliases;
		foreach($aliases as $alias) {
			if(str_starts_with($alias, "/"))
				$urls[] = $alias;
		}
		return $urls;

	}


}

?>