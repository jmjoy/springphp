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
 * Drip_ServiceModel
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
class Drip_ServiceModel {

	#	internal variables
  var $instantiator;

	#	Constructor
  function Drip_ServiceModel(&$instantiator) {
    $this->instantiator =& $instantiator;
  }

  function getClassName() {
  	return $this->instantiator->className;
  }

  function &instance() {
  	show_error('ServiceModel Error', 'ServiceModel::instance() not implemented');
  }
}

###

class Drip_PrototypeServiceModel extends Drip_ServiceModel {

  function &instance() {
    return $this->instantiator->instantiate();
  }
}

###

class Drip_SingletonServiceModel extends Drip_ServiceModel {

	#	internal variables
  var $instance = NULL;

  function &instance() {
    if (is_null($this->instance))
      $this->instance = $this->instantiator->instantiate();
    return $this->instance;
  }


}
?>