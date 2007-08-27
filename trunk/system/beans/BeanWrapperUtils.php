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
 * BeanWrapperUtils
 *
 * Utility methods for BeanWrapper
 *
 * @final
 * @package		Redstart
 * @subpackage  Beans
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class BeanWrapperUtils {

	/**
	 * Return the actual property name for the given property path.
	 *
	 * @param string $propertyPath the property path to determine the property name
	 * for (can include property keys, for example for specifying a map entry)
	 * @return string the actual property name, without any key elements
	 */
	function getPropertyName($propertyPath) {
		$separatorIndex = strpos($propertyPath, PROPERTY_KEY_PREFIX);
		return ($separatorIndex != -1 ? substr($propertyPath,0, $separatorIndex) : $propertyPath);
	}

	/**
	 * Determine the first nested property separator in the
	 * given property path, ignoring separators in keys (like "map[my_key]").
	 *
	 * @param string $propertyPath the property path to check
	 * @return int the index of the nested property separator, or -1 if none
	 */
	function getFirstNestedPropertySeparatorIndex($propertyPath) {
		return BeanWrapperUtils::getNestedPropertySeparatorIndex($propertyPath, false);
	}

	/**
	 * Determine the first nested property separator in the
	 * given property path, ignoring separators in keys (like "map[my_key]").
	 *
	 * @param string $propertyPath the property path to check
	 * @return int the index of the nested property separator, or -1 if none
	 */
	function getLastNestedPropertySeparatorIndex($propertyPath) {
		return BeanWrapperUtils::getNestedPropertySeparatorIndex($propertyPath, true);
	}

	/**
	 * Determine the first (or last) nested property separator in the
	 * given property path, ignoring separators in keys (like "map[my_key]").
	 *
	 * @param string $propertyPath the property path to check
	 * @param boolean $last whether to return the last separator rather than the first
	 * @return int the index of the nested property separator, or -1 if none
	 */
	function getNestedPropertySeparatorIndex($propertyPath, $last = false) {
		$inKey = false;
		$length = strlen($propertyPath);
		$i = ($last ? $length - 1 : 0);
		while ($last ? $i >= 0 : $i < $length) {
			switch (str_char_at($propertyPath,$i)) {
				case PROPERTY_KEY_PREFIX:
				case PROPERTY_KEY_SUFFIX:
					$inKey = $inKey==true?false:true;
					break;
				case NESTED_PROPERTY_SEPARATOR:
					if ($inKey != true) {
						return $i;
					}
			}
			if ($last)
				$i--;
			else
				$i++;
		}
		return -1;
	}

	/**
	 * Determine whether the given registered path matches the given property path,
	 * either indicating the property itself or an indexed element of the property.
	 *
	 * @param string $propertyPath the property path (typically without index)
	 * @param string $registeredPath the registered path (potentially with index)
	 * @return boolean whether the paths match
	 */
	function matchesProperty($registeredPath, $propertyPath) {
		if (str_starts_with($registeredPath,$propertyPath) != true) {
			return false;
		}
		if (strlen($registeredPath) == strlen($propertyPath)) {
			return true;
		}
		if (str_char_at($registeredPath,strlen($propertyPath)) != PROPERTY_KEY_PREFIX) {
			return false;
		}
		return (strpos($registeredPath,PROPERTY_KEY_SUFFIX, strlen($propertyPath) + 1) == strlen($registeredPath) - 1);
	}

	/**
	 * Determine the canonical name for the given property path.
	 * Removes surrounding quotes from map keys:<br>
	 * <pre>map['key']</pre> becomes <pre>map[key]</pre><br>
	 * <pre>map["key"]</pre> becomes <pre>map[key]</pre>
	 *
	 * @param string $propertyName the bean property path
	 * @return string the canonical representation of the property path
	 */
	function canonicalPropertyName($propertyName) {
		if ($propertyName == null) {
			return "";
		}

		// The following code does not use JDK 1.4's StringBuffer.indexOf(String)
		// method to retain JDK 1.3 compatibility. The slight loss in performance
		// is not really relevant, as this code will typically just run on startup.

		$buf = $propertyName;
		$searchIndex = 0;
		while ($searchIndex != -1) {
			$keyStart = strpos($buf, PROPERTY_KEY_PREFIX, $searchIndex);
			$searchIndex = -1;
			if ($keyStart !== FALSE) {
				$keyEnd = strpos($buf, PROPERTY_KEY_SUFFIX, $keyStart + strlen(PROPERTY_KEY_PREFIX));
				if ($keyEnd !== FALSE) {
					$key = substr2($buf,$keyStart + strlen(PROPERTY_KEY_PREFIX), $keyEnd);
					if ((str_starts_with($key,"'") && str_ends_with($key,"'")) || (str_starts_with($key,"\"") && str_ends_with($key,"\""))) {
						substr_replace($buf, '', $keyStart + 1, 1);
						substr_replace($buf, '', $keyEnd - 2, 1);
						$keyEnd = $keyEnd - 2;
					}
					$searchIndex = $keyEnd + strlen(PROPERTY_KEY_SUFFIX);
				}
			}
		}
		return $buf;
	}

	/**
	 * Determine the canonical names for the given property paths.
	 *
	 * @param array $propertyNames the bean property paths (as array)
	 * @return array the canonical representation of the property paths
	 * (as array of the same size)
	 * @see canonicalPropertyName
	 */
	function &canonicalPropertyNames($propertyNames) {
		if ($propertyNames == null) {
			return null;
		}
		$result = array();
		for ($i = 0; $i < count($propertyNames); $i++) {
			$result[$i] =& BeanWrapperUtils::canonicalPropertyName($propertyNames[$i]);
		}
		return $result;
	}
}
?>
