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
 * Drip_ServicePoint
 *
 * @package		Redstart
 * @subpackage  Context
 * @author		Ryan Scheuermann
 * @author		Marcus Lunzenauer <mlunzena@uos.de>
 * @link		http://www.concept64.com
 * @link		http://drip.tigris.org/
 * @since		Version 1.0
 * @filesource
 */
class Drip_ServicePoint {

	#	internal variables
	var $aliases = array();

	var $name, $description, $service_model;

	#	Constructor
  function Drip_ServicePoint($name, $description) {
    $this->name = $name;
    $this->description = $description;
  }

  function addAlias($name) {
  	$this->aliases[] = $name;
  }

  function &instance() {
    return $this->service_model->instance();
  }

  function getClassName() {
  	return $this->service_model->getClassName();
  }
}
?>