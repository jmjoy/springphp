<?php

class SQLMapper {

	var $datasource;
	var $sqlMaps = array();

	var $_propertyDescriptors = array();
 	
 	function _getPropertyDescriptors($class) {
		global $_classPropertyDescriptorsCache;

		if(!isset($this->_propertyDescriptors[$class])) {

			if(isset($_classPropertyDescriptorsCache[$class])) $this->_propertyDescriptors[$class] =& $_classPropertyDescriptorsCache[$class];
			else{
				$classInfoClass = $class.'ClassInfo';
				$propertyDescriptors = call_user_func(array($classInfoClass, 'getPropertyDescriptors'));
				if($propertyDescriptors === false) {
					if(log_enabled(LOG_DEBUG))
						log_message(LOG_DEBUG, 'SQLMapper: Cannot convert "'.$class.'" objects without ClassInfo definition.');
				}else {
					$this->_propertyDescriptors[$class] = array();
					foreach((array)$propertyDescriptors as $propDescriptor) {
						$name = $propDescriptor->getPropertyName();
						$this->_propertyDescriptors[$class][$name] = $propDescriptor;
					}
					$_classPropertyDescriptorsCache[$class] = $this->_propertyDescriptors[$class];
				}
			}
		}

		return $this->_propertyDescriptors[$class];
 	}
 	
	function &mapObjectToArray(&$obj, $class) {
		assert_not_null($obj, 'SQLMapper::mapObjectToArray : obj cannot be null');
		assert_not_null($class, 'SQLMapper::mapObjectToArray : class cannot be null');
	
		if(!isset($this->sqlMaps[$class]))
			show_error('SQLMapper', 'No SQLmap found for class: '.$class);
	
		$values = array();
		
		$propertyDescriptors = $this->_getPropertyDescriptors($class);
		$propertyMap = $this->sqlMaps[$class];
		
		foreach($propertyMap as $dbColumn => $objectProperty) {
			if(isset($propertyDescriptors[$objectProperty]))
				$propDescriptor = $propertyDescriptors[$objectProperty];
			else continue;
			
			$datatype = $propDescriptor->getPropertyType();
			
			switch($datatype) {
			
				case 'string':
				case 'int':
				case 'float':
					$values[$dbColumn] = $obj->$objectProperty;
					break;
										
				case 'boolean':
					$values[$dbColumn] = $this->datasource->db->formatBoolean($obj->$objectProperty);
					break;

				case 'timestamp':
					$values[$dbColumn] = $this->datasource->db->formatTimestamp($obj->$objectProperty);
					break;			
				
				case 'date':
					$values[$dbColumn] = $this->datasource->db->formatDate($obj->$objectProperty);
					break;			
					
				case 'time':
					$values[$dbColumn] = $this->datasource->db->formatTime($obj->$objectProperty);
					break;
					
				case 'string[]':
					$values[$dbColumn] = implode(',', $obj->$objectProperty);
					break;		
				
				default:
					continue;			
			
			}
		
		}
	
		return $values;
	}

	function &mapArrayToObject($array, $class) {
		assert_not_null($array, 'SQLMapper::mapArrayToObject : array cannot be null');
		assert_not_null($class, 'SQLMapper::mapArrayToObject : class cannot be null');
	
		if(!isset($this->sqlMaps[$class]))
			show_error('SQLMapper', 'No SQLmap found for class: '.$class);

		$obj =& new $class();
			
		$propertyDescriptors = $this->_getPropertyDescriptors($class);
		$propertyMap = $this->sqlMaps[$class];
		
		foreach($propertyMap as $dbColumn => $objectProperty) {
			if(isset($propertyDescriptors[$objectProperty]))
				$propDescriptor = $propertyDescriptors[$objectProperty];
			else continue;
			
			$datatype = $propDescriptor->getPropertyType();
			
			switch($datatype) {
			
				case 'string':
					$obj->$objectProperty = $this->_getArrayValue($dbColumn, $array);
					break;
					
				case 'int':
					$obj->$objectProperty = is_null($this->_getArrayValue($dbColumn, $array))?null:intVal($this->_getArrayValue($dbColumn, $array));
					break;
				
				case 'float':
					$obj->$objectProperty = is_null($this->_getArrayValue($dbColumn, $array))?null:floatVal($this->_getArrayValue($dbColumn, $array));
					break;
										
				case 'boolean':
					$obj->$objectProperty = $this->datasource->db->convertBoolean($this->_getArrayValue($dbColumn, $array));
					break;

				case 'timestamp':
					$obj->$objectProperty = $this->datasource->db->convertTimestamp($this->_getArrayValue($dbColumn, $array));
					break;
				
				case 'date':
					$obj->$objectProperty = $this->datasource->db->convertDate($this->_getArrayValue($dbColumn, $array));
					break;
					
				case 'time':
					$obj->$objectProperty = $this->datasource->db->convertTime($this->_getArrayValue($dbColumn, $array));
					break;
					
				case 'string[]':
					$obj->$objectProperty = explode(',', $this->_getArrayValue($dbColumn, $array));
					break;		
				
				default:
					continue;			
			
			}
		
		}
		return $obj;
	}
	
	function _getArrayValue($dbColumn, $array) {
		
		if(!isset($array[$dbColumn])) return null;
		
		return $array[$dbColumn];
	
	}


}

?>