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
 * BeanWrapper - manipulate Model objects
 *
 * Used to analyze and manipulate Beans (Model objects): allowing the getting and
 * setting of properties, getting property descriptors, and accessing property editors.
 *
 * Not often used directly but rather implicitly via {@link DataBinder}.
 *
 * @package		Redstart
 * @subpackage  Beans
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class BeanWrapper {

	var $object = null;

	var $nestedPath = "";

	var $rootObject = null;

	var $nestedBeanWrappers = array();

	var $propertyDescriptors = array();

	var $defaultEditors = array();
	var $customEditors = array();

	function BeanWrapper(&$object, $nestedPath = "", $rootObject = null) {
		if($rootObject != null && strtolower(get_class($rootObject)) == 'beanwrapper') $rootObject =& $rootObject->getWrappedInstance();
		$this->setWrappedInstance(&$object, $nestedPath, &$rootObject);
		$this->registerDefaultEditors();
	}

	function setWrappedInstance(&$object, $nestedPath = "", &$rootObject) {
		assert_not_null($object, "Bean object must not be null");
		$this->object =& $object;
		$this->nestedPath =& $nestedPath != null ? $nestedPath : "";
		if($this->nestedPath != '')
			$this->rootObject =& $rootObject;
		else
			$this->rootObject =& $object;
		$this->nestedBeanWrappers = null;
	}

	function &getWrappedInstance() {
		return $this->object;
	}

	function getWrappedClass() {
		return $this->object != null ? get_class($this->object) : null;
	}

	function &getNestedPath() {
		return $this->nestedPath;
	}

	function &getRootInstance() {
		return $this->rootObject;
	}

	function getRootClass() {
		return $this->rootObject != null ? get_class($this->rootObject) : null;
	}

	function &getPropertyDescriptors() {
		global $_classPropertyDescriptorsCache;

		assert_not_null($this->object, 'BeanWrapper does not hold a bean instance');

		if($this->propertyDescriptors == null) {

			$class = $this->getWrappedClass();

			if(isset($_classPropertyDescriptorsCache[$class])) $this->propertyDescriptors =& $_classPropertyDescriptorsCache[$class];
			else{
				$classInfoClass = $class.'ClassInfo';
				$propertyDescriptors = call_user_func(array($classInfoClass, 'getPropertyDescriptors'));
				if($propertyDescriptors === false) {
					if(log_enabled(LOG_DEBUG))
						log_message(LOG_DEBUG, 'Cannot wrap '.$this->getWrappedClass().' objects without ClassInfo definition.');
				}else {
					$this->propertyDescriptors = array();
					foreach((array)$propertyDescriptors as $propDescriptor) {
						$name = $propDescriptor->getPropertyName();						
						$this->propertyDescriptors[$name] = $propDescriptor;
					}
					$_classPropertyDescriptorsCache[$class] = $this->propertyDescriptors;
				}
			}
		}

		return $this->propertyDescriptors;

	}

	function getPropertyDescriptor($propertyName) {
		$pd = $this->_getPropertyDescriptorInternal($propertyName);
		if($pd == null) show_error('BeanWrapper', "No property '" . $propertyName . "' found");
		return $pd;
	}

	function &_getPropertyDescriptorInternal($propertyName) {
		assert_not_null($this->object, 'BeanWrapper does not hold a bean instance');
		assert_not_null($propertyName, "Property name must not be null");
		$nestedBw =& $this->_getBeanWrapperForPropertyPath($propertyName);
		if(strtolower(get_class($nestedBw)) != 'beanwrapper') return _null();
		$propDescriptors =& $nestedBw->getPropertyDescriptors();
		$finalPath = $this->_getFinalPath(&$nestedBw, $propertyName);
		if($propDescriptors == null || !isset($propDescriptors[$finalPath])) return _null();
		$pd = $propDescriptors[$finalPath];
		return $pd;
	}

	function getPropertyType($propertyName) {

		$pd =& $this->_getPropertyDescriptorInternal($propertyName);
		if ($pd != null) {
			return $pd->getPropertyType();
		} else {
			// Maybe an indexed/mapped property...
			$value = $this->getPropertyValue($propertyName);
			if ($value != null) {
				return get_qualified_type($value);
			}
		}
		return null;

	}

	/*
	 * IMPLEMENTATION OF: PropertyEditorRegistrySupport
	 */

	function registerDefaultEditors() {
		global $_defaultEditorsCache;

		if(!is_null($_defaultEditorsCache))
			$this->defaultEditors = $_defaultEditorsCache;
		else {
			$editors['boolean'] =& new BooleanEditor();

			$editors['int'] =& new IntEditor();
			$editors['float'] =& new FloatEditor();

			//TODO: custom objects
			$editors['date'] =& new DateEditor();
			$editors['timestamp'] =& new DateEditor();
			$editors['time'] =& new DateEditor();
	//		$editors['locale'] =& new LocaleEditor();

			$editors['string[]'] =& new StringArrayEditor();
			$_defaultEditorsCache = $editors;
			$this->defaultEditors = $editors;
		}
	}

	function &getDefaultEditor($requiredType) {
		if (!isset($this->defaultEditors[$requiredType])) {
			return _null();
		}
		return $this->defaultEditors[$requiredType];
	}

	function copyDefaultEditorsTo(&$target) {
		$target->defaultEditors =& $this->defaultEditors;
	}


	//---------------------------------------------------------------------
	// Management of custom editors
	//---------------------------------------------------------------------

	function registerCustomEditor($requiredType, $propertyPath, &$propertyEditor) {
		if ($requiredType == null && $propertyPath == null) {
			show_error('BeanWrapper', "Illegal Argument: Either requiredType or propertyPath is required");
		}
		if ($propertyPath != null) {
			$this->customEditors[$propertyPath] =& new CustomEditorHolder(&$propertyEditor, $requiredType);
		}
		else {
			$this->customEditors[$requiredType] =& $propertyEditor;
		}
	}

	function &findCustomEditor($requiredType, $propertyPath) {
		if (is_null($this->customEditors) || empty($this->customEditors)) {
			return _null();
		}
		$requiredTypeToUse = $requiredType;
		if ($propertyPath != null) {
			// Check property-specific editor first.
			$editor =& $this->getCustomEditor($propertyPath);
			if ($editor == null) {
				$strippedPaths = array();
				$this->_addStrippedPropertyPaths($strippedPaths, "", $propertyPath);
				foreach($strippedPaths as $strippedPath) {
					$editor =& $this->getCustomEditor($strippedPath);
					if($editor == null) break;
				}
			}
			if ($editor != null) {
				return $editor;
			}
			else if ($requiredType == null) {
				$requiredTypeToUse = $this->getPropertyType($propertyPath);
			}
		}
		// No property-specific editor -> check type-specific editor.
		return $this->getCustomEditor($requiredTypeToUse);
	}

	function hasCustomEditorForElement($elementType, $propertyPath) {
		if ($this->customEditors == null) {
			return false;
		}
		foreach($this->customEditors as $key => $entry) {
			if(is_string($key)) {
				$regPath = $key;
				if(BeanWrapperUtils::matchesProperty($regPath, $propertyPath)) {
					if(!is_null($entry) && $entry->getPropertyEditor() != null) return true;
				}
			}

		}
		// No property-specific editor -> check type-specific editor.
		return isset($this->customEditors[$elementType]);
	}

	function &getCustomEditor($propertyNameOrRequiredType) {
		if ($propertyNameOrRequiredType == null) {
			return _null();
		}
		// Check directly registered editor for type.
		$editor =& $this->customEditors[$propertyNameOrRequiredType];
		if(strtolower(get_class($editor)) == 'customeditorholder')
			return $editor->getPropertyEditor();
		return $editor;
	}

	function copyCustomEditorsTo(&$target, $nestedProperty) {
		$actualPropertyName =
				($nestedProperty != null ? BeanWrapperUtils::getPropertyName($nestedProperty) : null);
		if ($this->customEditors != null) {
			foreach($this->customEditors as $key => $entry) {
				if(strtolower(get_class($entry)) == 'customeditorholder') {
					$editorPath = $key;
					$pos = BeanWrapperUtils::getFirstNestedPropertySeparatorIndex($editorPath);
					if($pos != -1) {
						$editorNestedProperty = substr($editorPath, 0, $pos);
						$editorNestedPath = substr($editorPath, $pos + 1);
						if($editorNestedProperty == $nestedProperty || $editorNestedProperty == $actualPropertyName) {
							$editorHolder = $entry;
							$target->registerCustomEditor($editorHolder->getRegisteredType(), $editorNestedPath, $editorHolder->getPropertyEditor());
						}
					}

//				}else if(is_a($entry, 'PropertyEditor')) {
				}else {
					$target->registerCustomEditor($key, null, &$entry);
				}
			}
		}
	}

	function _addStrippedPropertyPaths($strippedPaths, $nestedPath, $propertyPath) {
		$startIndex = strpos($propertyPath, PROPERTY_KEY_PREFIX);
		if ($startIndex !== FALSE) {
			$endIndex = strpos($propertyPath, PROPERTY_KEY_SUFFIX);
			if ($endIndex !== FALSE) {
				$prefix = substr2($propertyPath, 0, $startIndex);
				$key = substr2($propertyPath, $startIndex, $endIndex + 1);
				$suffix = substr2($propertyPath,$endIndex + 1, strlen($propertyPath));
				// Strip the first key.
				$strippedPaths[] = $nestedPath . $prefix . $suffix;
				// Search for further keys to strip, with the first key stripped.
				$this->_addStrippedPropertyPaths($strippedPaths, $nestedPath . $prefix, $suffix);
				// Search for further keys to strip, with the first key not stripped.
				$this->_addStrippedPropertyPaths($strippedPaths, $nestedPath . $prefix . $key, $suffix);
			}
		}
	}


	function &convertIfNecessary(&$propertyName, &$oldValue, &$newValue, &$requiredType,&$descriptor) {

		$convertedValue = $newValue;

		// Custom editor for this type?
		$pe = $this->findCustomEditor($requiredType, $propertyName);

		// Value not of required type?
		if (!is_null($pe) || (!is_null($requiredType) && !is_assignable($requiredType, $convertedValue))) {
			if (is_null($pe) && !is_null($descriptor)) {
				$pe = $descriptor->getPropertyEditor();
			}
			if (is_null($pe) && !is_null($requiredType)) {
				// No custom editor -> check BeanWrapper's default editors.
				$pe = $this->getDefaultEditor($requiredType);
			}

			// For string types, NULL = ''
			if($requiredType != null && str_equals_icase($requiredType, 'string') && is_null($convertedValue))
				$convertedValue = '';

			$convertedValue =& $this->_convertValue(&$convertedValue, &$requiredType, &$pe, &$oldValue);

		}

		if ($requiredType != null) {
			// Try to apply some standard type conversion rules if appropriate.

			if (!is_null($convertedValue) && is_array_type($requiredType)) {
				// Array required -> apply appropriate conversion of elements.
				return $this->_convertToArray($convertedValue, $propertyName, get_array_component_type($requiredType));
			}

			if (!is_assignable($requiredType, $convertedValue)) {
				// Definitely doesn't match: throw IllegalArgumentException.

				$iae = new IllegalArgumentException("Cannot convert value of type [" .
						get_qualified_type($newValue) .
						"] to required type [" . $requiredType . "] for property '" .
						$propertyName . "': no matching editors or conversion strategy found.  Converted value was: ".$convertedValue);

				if(log_enabled(LOG_DEBUG))
					log_message(LOG_DEBUG, 'IllegalArgumentException in convertIfNecessary: '.$iae->getMessage());
				
				return $iae;
			}
		}

		return $convertedValue;
	}

	function &_convertValue(&$newValue, &$requiredType, &$pe, &$oldValue) {
		$convertedValue = $newValue;

		if ($pe != null && !is_string($convertedValue)) {
			// Not a String -> use PropertyEditor's setValue.
			// With standard PropertyEditors, this will return the very same object;
			// we just want to allow special PropertyEditors to override setValue
			// for type conversion from non-String values to the required type.
			$pe->setValue($convertedValue);
			$newConvertedValue = $pe->getValue();
			if ($newConvertedValue != $convertedValue) {
				$convertedValue =& $newConvertedValue;
				// Reset PropertyEditor: It already did a proper conversion.
				// Don't use it again for a setAsText call.
				$pe = null;
			}
		}

		if ($requiredType != null && !is_array_type($requiredType) && get_qualified_type($convertedValue) == 'string[]') {
			// Convert String array to a comma-separated String.
			// Only applies if no PropertyEditor converted the String array before.
			// The CSV String will be passed into a PropertyEditor's setAsText method, if any.
			if (log_enabled(LOG_INFO)) {
				log_message(LOG_INFO, "Converting String array to comma-delimited String [" . $convertedValue . "]");
			}
			$convertedValue = implode(',',$convertedValue);
		}

		if ($pe != null && is_string($convertedValue)) {
			// Use PropertyEditor's setAsText in case of a String value.
			if (log_enabled(LOG_INFO)) {
				log_message(LOG_INFO,"Converting String to [" . $requiredType . "] using property editor [" . get_class($pe) . "]");
			}
			$pe->setValue($oldValue);
			$pe->setAsText($convertedValue);
			$convertedValue = $pe->getValue();
		}

		return $convertedValue;
	}

	function &_convertToArray($input, $propertyName, $componentType) {
		if (is_array($input)) {
			// Convert array elements, if necessary.
			if ($componentType == get_array_component_type(get_qualified_type($input)) &&
					!$this->hasCustomEditorForElement($componentType, $propertyName)) {
				return $input;
			}
			$result = array();
			foreach($input as $key => $arrayValue) {
				$value =& $this->convertIfNecessary(
						$this->_buildIndexedPropertyName($propertyName, $key), _null(), $arrayValue, $componentType, _null());
			
				//if($value == null) return _null();
				$result[$key] = $value;
			}
			return $result;
		}
		else {
			// A plain value: convert it to an array with a single component.
			$result = array();
			$value =& $this->convertIfNecessary(
					$this->_buildIndexedPropertyName($propertyName, 0), _null(), $input, $componentType, _null());
			if($value == null) return _null();
			$result[] = $value;
			return $result;
		}
	}

	function _buildIndexedPropertyName($propertyName, $index) {
		return ($propertyName != null ?
				$propertyName . PROPERTY_KEY_PREFIX . $index . PROPERTY_KEY_SUFFIX :
				null);
	}


	function _getFinalPath(&$bw, $nestedPath) {
		if ($bw->nestedPath === $this->nestedPath) {
			return $nestedPath;
		}
		return substr($nestedPath, BeanWrapperUtils::getLastNestedPropertySeparatorIndex($nestedPath) + 1);
	}

	function &_getBeanWrapperForPropertyPath($propertyPath) {
		$pos = BeanWrapperUtils::getFirstNestedPropertySeparatorIndex($propertyPath);
		// Handle nested properties recursively.
		if ($pos != -1) {
			$nestedProperty = substr($propertyPath,0,$pos);
			$nestedPath = substr($propertyPath,$pos + 1);
			$nestedBw =& $this->_getNestedBeanWrapper($nestedProperty);
			if(strtolower(get_class($nestedBw)) != 'beanwrapper') return $nestedBw;
			return $nestedBw->_getBeanWrapperForPropertyPath($nestedPath);
		} else {
			return $this;
		}
	}

	function &_getNestedBeanWrapper($nestedProperty) {
		if ($this->nestedBeanWrappers == null) {
			$this->nestedBeanWrappers = array();
		}
		// Get value of bean property.
		$tokens =& $this->_getPropertyNameTokens($nestedProperty);
		$canonicalName = $tokens->canonicalName;
		$propertyValue =& $this->getPropertyValue($tokens);

		if ($propertyValue == null || is_a($propertyValue, 'InvalidPropertyException')) {
			$ex =& new NullValueInNestedPathException($this->getRootClass(), $this->nestedPath.$canonicalName, 'Null value in nested path for class ['.$this->getRootClass().']: '.$this->nestedPath.$canonicalName);
			return $ex;
		}

		// Lookup cached sub-BeanWrapper, create new one if not found.
		$nestedBw = null;
		if(isset($this->nestedBeanWrappers[$canonicalName]))
			$nestedBw =& $this->nestedBeanWrappers[$canonicalName];

		if ($nestedBw == null || $nestedBw->getWrappedInstance() != $propertyValue) {
			if (log_enabled(LOG_INFO)) {
				log_message(LOG_INFO, "Creating new nested BeanWrapper for property '" . $canonicalName . "'");
			}
			$nestedBw =& $this->_newNestedBeanWrapper($propertyValue, $this->nestedPath . $canonicalName . NESTED_PROPERTY_SEPARATOR);
			// Inherit all type-specific PropertyEditors.
			$this->copyDefaultEditorsTo(&$nestedBw);
			$this->copyCustomEditorsTo(&$nestedBw, $canonicalName);
			$this->nestedBeanWrappers[$canonicalName] =& $nestedBw;
		}
		else {
			if (log_enabled(LOG_INFO)) {
				log_message(LOG_INFO,"Using cached nested BeanWrapper for property '" . $canonicalName . "'");
			}
		}
		return $nestedBw;
	}

	function &_newNestedBeanWrapper(&$object, $nestedPath) {
		$bw =& new BeanWrapper(&$object);
		$bw->setWrappedInstance(&$object, $nestedPath, $this->getWrappedInstance());
		$bw->registerDefaultEditors();
		return $bw;
	}

	function &_getPropertyNameTokens($propertyName) {
		$tokens = new PropertyTokenHolder();
		$actualName = null;
		$keys = array();
		$searchIndex = 0;
		while ($searchIndex !== -1) {
			$keyStart = strpos($propertyName,PROPERTY_KEY_PREFIX, $searchIndex);
			$searchIndex = -1;
			if ($keyStart !== FALSE) {
				$keyEnd = strpos($propertyName,PROPERTY_KEY_SUFFIX,$keyStart + strlen(PROPERTY_KEY_PREFIX));
				if ($keyEnd !== FALSE) {
					if ($actualName == null) {
						$actualName = substr2($propertyName,0, $keyStart);
					}
					$key = substr2($propertyName,$keyStart + strlen(PROPERTY_KEY_PREFIX), $keyEnd);
					if ((str_starts_with($key,"'") && str_ends_with($key,"'")) || (str_starts_with($key,"\"") && str_ends_with($key,"\""))) {
						$key = substr($key,1, strlen($key) - 1);
					}
					$keys[] = $key;
					$searchIndex = $keyEnd + strlen(PROPERTY_KEY_SUFFIX);
				}
			}
		}
		$tokens->actualName = ($actualName != null ? $actualName : $propertyName);
		$tokens->canonicalName = $tokens->actualName;
		if (!empty($keys)) {
			$tokens->canonicalName .=
					PROPERTY_KEY_PREFIX .
					implode(PROPERTY_KEY_SUFFIX.PROPERTY_KEY_PREFIX, $keys) .
					PROPERTY_KEY_SUFFIX;
			$tokens->keys = $keys;
		}
		return $tokens;
	}


	function &getPropertyValue($tokens) {
		// not a PropertyTokenHolder, assume just a propertyName
		if(strtolower(get_class($tokens)) != 'propertytokenholder') {
			$nestedBw =& $this->_getBeanWrapperForPropertyPath($tokens);
			if(strtolower(get_class($nestedBw)) != 'beanwrapper') return $nestedBw;
			$tokens =& $this->_getPropertyNameTokens($this->_getFinalPath($nestedBw, $tokens));
			return $nestedBw->getPropertyValue($tokens);
		}

		$propertyName = $tokens->canonicalName;
		$actualName = $tokens->actualName;
		$pd =& $this->_getPropertyDescriptorInternal($tokens->actualName);
		if (is_null($pd)) {
			$ex =& new NotReadablePropertyException($this->getRootClass(), $this->nestedPath.$propertyName, 'Cannot get PropertyDescriptor for property: '.$this->nestedPath.$propertyName);
			return $ex;
		}

		$readMethod = 'get'.$pd->getPropertyName();
		if (log_enabled(LOG_INFO)) {
			log_message(LOG_INFO,"About to invoke read method [" . $readMethod . "] on object of class [" .
					get_class($this->object) . "]");
		}

		if(!method_exists($this->object, $readMethod)) {
			$ex =& new InvalidPropertyException($this->getRootClass(), $this->nestedPath.$propertyName, 'Object of type '.get_class($this->object) . ' must have get method: '.$readMethod);
			return $ex;
		}
		$value =& $this->object->$readMethod();
		if ($tokens->keys != null && !empty($tokens->keys)) {
			if (is_null($value)) {
				$ex =& new NullValueInNestedPathException($this->getRootClass(), $this->nestedPath.$propertyName,
						"Cannot access indexed value of property referenced in indexed " .
						"property path '" . $propertyName . "': returned null");
				return $ex;
			}

			// apply indexes and map keys
			foreach((array)$tokens->keys as $key) {
				if (is_array($value)) {
					if(!isset($value[$key])) {
						$ex =& new InvalidPropertyException($this->getRootClass(), $this->nestedPath.$propertyName, "Index of [$key] out of bounds in property path '$propertyName'");
						return $ex;
					}
					$value =& $value[$key];
				} else {
					$ex =& new InvalidPropertyException($this->getRootClass(), $this->nestedPath.$propertyName,
							"Property referenced in indexed property path '" . $propertyName .
							"' is not an array; returned value was [" . var_export($value, true) . "]");
					return $ex;
				}
			}
		}
		return $value;
	}

	function setPropertyValue(&$tokens, $newValue) {
		if(strtolower(get_class($tokens)) != 'propertytokenholder') {
			$nestedBw =& $this->_getBeanWrapperForPropertyPath($tokens);
			if($nestedBw == null || strtolower(get_class($nestedBw)) != 'beanwrapper')
				return new NotWriteablePropertyException($this->getRootClass(), $this->nestedPath.$tokens,'Cannot find BeanWrapper for property path: '.$tokens);
			$tokens =& $this->_getPropertyNameTokens($this->_getFinalPath($nestedBw, $tokens));
			return $nestedBw->setPropertyValue(&$tokens, $newValue);
		}

		$propertyName = $tokens->canonicalName;

		if ($tokens->keys != null) {
			// Apply indexes and map keys: fetch value for all keys but the last one.
			$getterTokens = new PropertyTokenHolder();
			$getterTokens->canonicalName = $tokens->canonicalName;
			$getterTokens->actualName = $tokens->actualName;
			$getterTokens->keys = $tokens->keys;

			array_pop($getterTokens->keys);
			$propValue =& $this->getPropertyValue($getterTokens);

			// Set value for last key.
			$lastKey = count($tokens->keys) - 1;
			$key = $tokens->keys[$lastKey];

			if (is_null($propValue)) {
				return new NullValueInNestedPathException($this->getRootClass(), $this->nestedPath.$propertyName,"Cannot access indexed value in property referenced " .
						"in indexed property path '" . $propertyName . "': returned null");
			} else if (is_array($propValue)) {
				$requiredType = get_array_component_type($propValue);
				$arrayIndex = $key;

				$oldValue = null;
				if(isset($propValue[$arrayIndex]))
					$oldValue = $propValue[$arrayIndex];

//				if(!isset($propValue[$arrayIndex]))
//					return new InvalidPropertyException($this->getRootClass(), $this->nestedPath.$propertyName,"Invalid array index in property path '" . $propertyName . "'");

				$convertedValue =& $this->convertIfNecessary(
						$propertyName, $oldValue, $newValue, $requiredType, _null());

				if(is_a($convertedValue, 'IllegalArgumentException'))
					return new TypeMismatchException($this->object, $this->nestedPath.$propertyName,&$oldValue, &$newValue, $requiredType);

				$propValue[$key] =& $convertedValue;
			} else {
				return new InvalidPropertyException($this->getRootClass(), $this->nestedPath.$propertyName,
					"Property referenced in indexed property path '" . $propertyName .
						"' is not an array; returned value was [" . var_export($propValue, true) . "]");
			}
		} else {
			$pd =& $this->_getPropertyDescriptorInternal($propertyName);
			if (is_null($pd)) {
				return new NotWriteablePropertyException($this->getRootClass(), $this->nestedPath.$propertyName,'Cannot get PropertyDescriptor for property: '.$this->nestedPath.$propertyName);
			}

			$propName =& $pd->getPropertyName();
			$propType =& $pd->getPropertyType();
			$readMethod = 'get'.$propName;
			$oldValue = null;

			if(method_exists($this->object, $readMethod))
				$oldValue =& $this->object->$readMethod();

			$convertedValue =& $this->convertIfNecessary(&$propertyName, &$oldValue, &$newValue, &$propType, &$pd);

			if(is_a($convertedValue, 'IllegalArgumentException'))
				return new TypeMismatchException(&$this->object, $this->nestedPath.$propertyName,&$oldValue, &$newValue, &$propType);

//			if (log_enabled(LOG_INFO)) {
//				log_message(LOG_INFO, microtime()." About to set property [" . $prop . "] on object of class [" .
//						get_class($this->object) . "]");
//			}

//TODO: is this necessary?
//			if(!array_key_exists($prop,get_object_vars($this->object)))
//				return new NotWriteablePropertyException($this->getRootClass(), $this->nestedPath.$propertyName,'Property does not exist on object: '.$this->nestedPath.$propertyName);

			$this->object->$propName =& $convertedValue;

//			if (log_enabled(LOG_INFO)) {
//				log_message(LOG_INFO, "Set property [" . $prop . "] with value of type [" .
//						$pd->getPropertyType() . "]");
//			}
		}
		return true;
	}

	function setPropertyValues($pvs, $ignoreUnknown = FALSE, $ignoreInvalid = FALSE) {

		$propertyAccessExceptions = array();
		foreach((array)$pvs as $key => $value) {
			$result = $this->setPropertyValue($key, $value);

			if($result !== true) {
				if(strtolower(get_class($result)) == 'notwriteablepropertyexception') {
					if(!$ignoreUnknown)
						show_error('BeanWrapper: Not Writeable Property', $result->getMessage());
				} else if(strtolower(get_class($result)) == 'nullvalueinnestedpathexception') {
					if(!$ignoreInvalid)
						show_error('BeanWrapper: Null Value in Nested Path', $result->getMessage());
				} else if(strtolower(get_class($result)) == 'typemismatchexception') {
					$propertyAccessExceptions[] = $result;
				} else {
					show_error('BeanWrapper: '.get_class($result), $result->getMessage());
				}
			}

		}

		if(empty($propertyAccessExceptions)) return true;

		return $propertyAccessExceptions;
	}

}

/**
 * @access private
 */
class PropertyTokenHolder {
	var $canonicalName;
	var $actualName;
	var $keys;
}

/**
 * @access private
 */
class CustomEditorHolder {

		var $propertyEditor;

		var $registeredType;

		function CustomEditorHolder(&$propertyEditor, $registeredType) {
			$this->propertyEditor =& $propertyEditor;
			$this->registeredType = $registeredType;
		}

		function &getPropertyEditor() {
			return $this->propertyEditor;
		}

		function &getRegisteredType() {
			return $this->registeredType;
		}
/*
		function getPropertyEditor($requiredType = null) {
			// Special case: If no required type specified, which usually only happens for
			// Collection elements, or required type is not assignable to registered type,
			// which usually only happens for generic properties of type Object -
			// then return PropertyEditor if not registered for Collection or array type.
			// (If not registered for Collection or array, it is assumed to be intended
			// for elements.)
			if (this.registeredType == null ||
					(requiredType != null &&
					(ClassUtils.isAssignable(this.registeredType, requiredType) ||
					ClassUtils.isAssignable(requiredType, this.registeredType))) ||
					(requiredType == null &&
					(!Collection.class.isAssignableFrom(this.registeredType) && !this.registeredType.isArray()))) {
				return this.propertyEditor;
			}
			else {
				return null;
			}
		}*/
	}
?>
