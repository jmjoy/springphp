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
 * Drip_Configuration
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
class Drip_Configuration {

	#	internal variables
	var $registry;

	#	Constructor
	function Drip_Configuration(& $registry) {
		$this->registry = & $registry;
	}

	function process($filename) {

		# load yaml
		$yaml =& yaml_load($filename);


		//$id = $yaml['id'];
//		$base = isset ($yaml['base']) ? $this->_parse_base($yaml['base']) : dirname($filename) . '/';
		$base = isset ($yaml['base']) ? $this->_parse_base($yaml['base']) : APPPATH;
		
		if(isset($yaml['includes'])) {
			foreach($yaml['includes'] as $file) {
				if($file != $filename)
					$this->process($base.$file);
			}
		}

		#create property points
		if (isset ($yaml['property-points'])) {

			foreach ($yaml['property-points'] as $file) {
				$name = basename($file,EXT);
				$this->registry->addPropertyPoint(new Drip_PropertyPoint($name, $base, $file));
			}
		}

		# create abstract service points
		if(isset($yaml['abstract-service-points'])) {

			foreach ($yaml['abstract-service-points'] as $aname => $asp) {
				$this->registry->addAbstractServicePoint($aname, $asp);
			}

		}

		# create service points
		if (isset ($yaml['service-points'])) {

			# add configuration points
			foreach ($yaml['service-points'] as $name => $sp) {

				if(isset($sp['extends'])) {
					$ext = $sp['extends'];
					$ext_conf =& $this->registry->abstractServicePoint($ext);
					if($ext_conf != null)
					{
						$sp = array_merge_recursive($ext_conf, $sp);
					}
				}


				if(str_starts_with($name, '+')) {
					$name = substr($name, 1);

					$service_point =& $this->registry->servicePoint($name);

					if($service_point != null) {
						if(isset($sp['implementor']['properties'])){
							$service_point->instantiator->addProperties($sp['implementor']['properties']);
						}
					}

				}else{
					$service_point = & new Drip_ServicePoint($name, isset ($sp['description']) ? $sp['description'] : "");
					$this->registry->addServicePoint($service_point);


					$service_point->instantiator = new Drip_Instantiator($sp['implementor'], isset ($sp['base']) ? $this->_parse_base($sp['base']) : $base);

					if(!isset($sp['model'])) $sp['model'] = 'singleton';
					switch ($sp['model']) {

						default :
						case 'singleton' :
							$service_point->service_model = & new Drip_SingletonServiceModel(& $service_point->instantiator);
							//$service_point->service_model->id = uniqid('');
							break;

						case 'prototype' :
							$service_point->service_model = & new Drip_PrototypeServiceModel(& $service_point->instantiator);
							//$service_point->service_model->id = uniqid('');
							break;
					}

				}


				if (isset ($sp['aliases'])) {
					foreach ($sp['aliases'] as $alias) {
						$service_point->addAlias($alias);
						$this->registry->aliasMap[$alias] = $name;
					}
				}


			}
		}



		return true;
	}

	function _parse_base($value) {
      # tagged value
      if (preg_match('/^!!(constant)\s+(.+)$/', $value, $matches)) {
        switch ($matches[1]) {
        	case 'constant':
        		$value = constant($matches[2]);
        		break;
        }
      }
      return $value;
	}

}
?>