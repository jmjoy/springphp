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
 * AppContext - IoC (Inversion of Control) container
 *
 * @package		Redstart
 * @subpackage  Context
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com
 * @since		Version 1.0
 * @filesource
 */
class AppContext {

	#	internal variables
	var $propertyPoints = array();

	var $properties = array();

	var $abstractServicePoints = array();
	var $servicePoints = array();
	var $aliasMap = array();

	#	Constructor
	function AppContext() {}

	# building registry from yaml files
	function load($filename) {
		global $appContext;
		$conf = & new Drip_Configuration(& $appContext);
		return $conf->process($filename);
	}

	function &autowireService(&$obj, $serviceName = '') {
		global $appContext;

		$clazz = get_class($obj);
		if($serviceName == '') $serviceName = $clazz;
		if(!$appContext->serviceExists($serviceName)) {
			if(!class_exists($clazz)) show_error('AppContext', 'Cannot autowire service, class not found: '.$clazz);

			$sp = new Drip_ServicePoint($serviceName, '');
			$sp->instantiator =& new Drip_Instantiator(array('class'=>$clazz, 'autowire'=>true), '');//autowired classes should be in the path already
            $sp->service_model =& new Drip_SingletonServiceModel(& $sp->instantiator);
            //$sp->service_model->id = uniqid('');
            $sp->service_model->instance =& $obj;
			$appContext->addServicePoint($sp);
		}
		return $appContext->service($serviceName);
	}

	function &createAutowiredService($clazz, $serviceName = '') {
		global $appContext;

		if($serviceName == '') $serviceName = $clazz;
		if(!$appContext->serviceExists($serviceName)) {
			if(!class_exists($clazz)) show_error('AppContext', 'Cannot create autowired service, class not found: '.$clazz);

			$sp = new Drip_ServicePoint($serviceName, '');
			$sp->instantiator =& new Drip_Instantiator(array('class'=>$clazz, 'autowire'=>true), '');//autowired classes should be in the path already
            $sp->service_model =& new Drip_SingletonServiceModel(& $sp->instantiator);
            //$sp->service_model->id = uniqid('');
			$appContext->addServicePoint($sp);
		}
		return $appContext->service($serviceName);
	}
/*
	function addPropertyPoint(& $property_point) {
		global $appContext;
		$appContext->propertyPoints[$property_point->name] = & $property_point;
	}
*/

	function addPropertyPoint(& $property_point) {
		global $appContext;

		foreach((array)$property_point->properties as $name => $value) {
			$appContext->properties[$name] = $value;
		}

		$appContext->propertyPoints[$property_point->name] = & $property_point;
	}


	function addServicePoint(& $service_point) {
		global $appContext;
		$appContext->servicePoints[$service_point->name] = & $service_point;
	}

	function addAbstractServicePoint($name, &$config) {
		global $appContext;
		$appContext->abstractServicePoints[$name] =& $config;
	}

	function &abstractServicePoint($name) {
		global $appContext;
		if(isset($appContext->abstractServicePoints[$name])) return $appContext->abstractServicePoints[$name];
		return _null();
	}


	# returning property points by name
	function & propertyPoint($name) {
		global $appContext;
		return $appContext->propertyPoint[$name];
	}
/*
	function &property($name) {
		global $appContext;
		# check name
		if (!preg_match('/^(.+)\.([^.]+)$/', $name, $matches))
			return _null();

		array_shift($matches);
		list ($ppn, $pn) = $matches;

		$pp =& $appContext->propertyPoints[$ppn];
		if($pp == null) {
			return _null();
		}else {
			return $pp->getProperty($pn);
		}
	}
*/
	function &property($name) {
		global $appContext;

		if(isset($appContext->properties[$name])) return $appContext->properties[$name];

		return _null();
	}

	# returning services by name
	function &service($name) {
		global $appContext;
		if (!$appContext->serviceExists($name))
			return _null();

		if($appContext->isAlias($name)) $name = $appContext->aliasMap[$name];
		$point =& $appContext->servicePoints[$name];
		return $point->instance();
	}

	# checks a service's existence
	function serviceExists($name) {
		global $appContext;
		return isset ($appContext->servicePoints[$name])||($appContext->isAlias($name));
	}

	function isAlias($name) {
		global $appContext;
		return isset($appContext->aliasMap[$name]);
	}

	# returning service points by name
	function & servicePoint($name) {
		global $appContext;
		if (!$appContext->serviceExists($name))
			return _null();

		return $appContext->servicePoints[$name];
	}

	function &getServicePoints() {
		global $appContext;
		return $appContext->servicePoints;
	}


	function &getServicesOfType($class) {
		global $appContext;

		$services = array();

		foreach($appContext->servicePoints as $sname => $sp)
		{
			$className = $sp->getClassName();
			if(is_subclass($className, $class))
			{
				$instance =& $sp->instance();
				$services[$sname] =& $instance;
			}
		}

		return $services;
	}

	function &getServicesByNameMatchEnd($string) {
		global $appContext;
		$services = array();
		$sps =& $appContext->servicePoints;

		foreach($sps as $sname => $sp)
		{
			if(str_ends_with(strtolower($sname), strtolower($string))) {
				$instance =& $sp->instance();
				$services[$sname] =& $instance;
			}
		}
		return $services;

	}

}


?>
