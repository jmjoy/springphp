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
 * RequestUtils
 *
 * @package		Redstart
 * @subpackage  HTTP
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class RequestUtils {

	function getMessage($codes, $arguments= null, $defaultMessage = '', $textDomain = '', $htmlEscape = null) {
		if(is_a($codes, 'MessageSourceResolvable')) {
			$msr = $codes;
			$codes = $msr->getCodes();
			$arguments = $msr->getArguments();
			$defaultMessage = $msr->getDefaultMessage();
		}

		if($codes == null) $codes = array();
		if(!is_array($codes)) $codes = array($codes);

		foreach($codes as $code) {

			$message =& AppContext::property($code);
			if($message != null) {
				return RequestUtils::_renderMessage($message, $arguments, $textDomain, $htmlEscape);
			}

		}

		if($defaultMessage != null && strlen(trim($defaultMessage)) != 0) {
			return RequestUtils::_renderMessage($defaultMessage, $arguments, $textDomain, $htmlEscape);
		}

		if(count($codes) > 0) {
			return $codes[0];
		}

		show_error('Message', 'No messages found for codes.');
	}

	function _renderMessage($message, $arguments, $textDomain, $htmlEscape = null) {
		if($arguments != null && is_array($arguments) && !empty($arguments)) {
			$message = __($message, $textDomain);
			$sprintargs = array_merge(array($message),$arguments);
			$result = call_user_func_array('sprintf', $sprintargs);
			if(is_html_escape($htmlEscape)===true) $result = specialchars($result);
			return $result;

		}else {
			$result = __($message, $textDomain);
			if(is_html_escape($htmlEscape)===true) $result = specialchars($result);
			return $result;
		}
	}

	function &getBindingResult($name) {
		global $request;

		$br_name = BINDING_RESULT_MODEL_KEY_PREFIX . $name;
		$bindingResult =& $request->getAttribute($br_name);

		return $bindingResult;
	}

	function &getModelObject($name) {
		global $request;
		return $request->getAttribute($name);
	}

	function hasSubmitParameter(&$request, $name) {
		assert_not_null($request, "Request must not be null");
		if ($request->getParameter($name) != null) {
			return true;
		}
		$submit_image_suffixes = array(".x", ".y");
		foreach($submit_image_suffixes as $suffix) {
			if($request->getParameter($name . $suffix) != null) return true;
		}
		return false;
	}

	function getParametersStartingWith(&$request, $prefix) {
		assert_no_null($request, "Request must not be null");
		$paramNames = $request->getParameterNames();
		$params = array();
		if ($prefix == null) {
			$prefix = "";
		}
		foreach((array)$paramNames as $name) {
			if($prefix == "" || str_starts_with($name, $prefix)) {
				$unprefixed = substr($name, strlen($prefix));
				$value = $request->getParameter($name);
				if($value == null) {

				}else if(is_array($value)) {
					$params[$unprefixed] = $value;
				}else {
					$params[$unprefixed] = $value;
				}
			}
		}
		return $params;
	}

	function extractFilenameFromUrlPath($urlPath) {
		$begin = strrpos($urlPath,'/') + 1;
		$end = strpos($urlPath,';');
		if ($end === FALSE) {
			$end = strpos($urlPath,'?');
			if ($end === FALSE) {
				$end = strlen($urlPath);
			}
		}
		$filename = substr2($urlPath,$begin,$end);
		$dotIndex = strrpos($filename,'.');
		if ($dotIndex !== FALSE) {
			$filename = substr($filename,0, $dotIndex);
		}
		return $filename;
	}
	
	function getPathWithinHandlerMapping() {
		global $request;
		return $request->getAttribute(PATH_WITHIN_HANDLER_MAPPING_ATTRIBUTE);
	}

	function getIntParameter(&$request, $parameterName, $defaultValue = null) {
		$val = $request->getParameter($parameterName);
		if($defaultValue === null && $val !== null && !is_numeric($val) )
			show_error('Error', "Parameter '$parameterName' with value of '{$val}' is not a valid number.");

		if($val === null || !is_numeric($val))
			return $defaultValue;

		return intVal($val);
	}

	function getRequiredIntParameter(&$request, $parameterName) {
		if($request->getParameter($parameterName) === null)
			show_error('Error', "Required int parameter '$parameterName' missing.");
		return RequestUtils::getIntParameter($request, $parameterName);
	}

	function getIntParameters(&$request, $parameterName) {
		$values = $request->getParameter($parameterName);
		if(!is_array($values)) return array(RequestUtils::getIntParameter($request, $parameterName));

		$result = array();
		foreach((array)$values as $key => $value) {
			if($value === null || !is_numeric($value))
				show_error('Error', "Parameter '{$parameterName}[$key]' with value of '{$value}' is not a valid number.");

			$result[$key] = intVal($value);
		}
		return $result;
	}


	function getFloatParameter(&$request, $parameterName, $defaultValue = null) {
		$val = $request->getParameter($parameterName);
		if($defaultValue === null && $val !== null && !is_numeric($val) )
			show_error('Error', "Parameter '$parameterName' with value of '{$val}' is not a valid number.");

		if($val === null || !is_numeric($val))
			return $defaultValue;

		return floatVal($val);
	}

	function getRequiredFloatParameter(&$request, $parameterName) {
		if($request->getParameter($parameterName) === null)
			show_error('Error', "Required float parameter '$parameterName' missing.");
		return RequestUtils::getFloatParameter($request, $parameterName);
	}

	function getFloatParameters(&$request, $parameterName) {
		$values = $request->getParameter($parameterName);
		if(!is_array($values)) return array(RequestUtils::getFloatParameter($request, $parameterName));

		$result = array();
		foreach((array)$values as $key => $value) {
			if($value === null || !is_numeric($value))
				show_error('Error', "Parameter '{$parameterName}[$key]' with value of '{$value}' is not a valid number.");

			$result[$key] = floatVal($value);
		}
		return $result;
	}

	function getBooleanParameter(&$request, $parameterName, $defaultValue = null) {
		$val = $request->getParameter($parameterName);
		if($defaultValue === null && $val !== null && !str_is_bool($val))
			show_error('Error', "Parameter '$parameterName' with value of '{$val}' is not a valid boolean expression.");

		if($val === null || !str_is_bool($val))
			return $defaultValue;

		return str_bool($val);
	}

	function getRequiredBooleanParameter(&$request, $parameterName) {
		if($request->getParameter($parameterName) === null)
			show_error('Error', "Required boolean parameter '$parameterName' missing.");
		return RequestUtils::getBooleanParameter($request, $parameterName);
	}

	function getBooleanParameters(&$request, $parameterName) {
		$values = $request->getParameter($parameterName);
		if(!is_array($values)) return array(RequestUtils::getBooleanParameter($request, $parameterName));

		$result = array();
		foreach((array)$values as $key => $value) {
			if($value === null || !str_is_bool($value))
				show_error('Error', "Parameter '{$parameterName}[$key]' with value of '{$value}' is not a valid boolean expression.");

			$result[$key] = str_bool($value);
		}
		return $result;
	}


	function getStringParameter(&$request, $parameterName, $defaultValue = null) {
		$val = $request->getParameter($parameterName);
		return $val===null?$defaultValue:$val;
	}

	function getRequiredStringParameter(&$request, $parameterName) {
		if($request->getParameter($parameterName) === null)
			show_error('Error', "Required string parameter '$parameterName' missing.");
		return RequestUtils::getStringParameter($request, $parameterName);
	}

	function getStringParameters(&$request, $parameterName) {
		$values = $request->getParameter($parameterName);
		if(!is_array($values)) return array(RequestUtils::getStringParameter($request, $parameterName));

		return $values;
	}

}
?>
