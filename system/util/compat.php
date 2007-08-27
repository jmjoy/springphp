<?php

/* Functions missing from older PHP versions */


/* Added in PHP 4.2.0 */

if (!function_exists('floatval')) {
	function floatval($string) {
		return ((float) $string);
	}
}

if (!function_exists('is_a')) {
	function is_a($object, $class) {
		// by Aidan Lister <aidan@php.net>
		if (get_class($object) == strtolower($class)) {
			return true;
		} else {
			return is_subclass_of($object, $class);
		}
	}
}

if (!function_exists('ob_clean')) {
	function ob_clean() {
		// by Aidan Lister <aidan@php.net>
		if (@ob_end_clean()) {
			return ob_start();
		}
		return false;
	}
}


/* Added in PHP 4.3.0 */

function printr($var, $do_not_echo = false) {
	// from php.net/print_r user contributed notes
	ob_start();
	print_r($var);
	$code =  htmlentities(ob_get_contents());
	ob_clean();
	if (!$do_not_echo) {
	  echo "<pre>$code</pre>";
	}
	ob_end_clean();
	return $code;
}

/* compatibility with PHP versions older than 4.3 */
if ( !function_exists('file_get_contents') ) {
	function file_get_contents( $file ) {
		$file = file($file);
		return !$file ? false : implode('', $file);
	}
}

if (!defined('CASE_LOWER')) {
    define('CASE_LOWER', 0);
}

if (!defined('CASE_UPPER')) {
    define('CASE_UPPER', 1);
}


/**
 * Replace array_change_key_case()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.array_change_key_case
 * @author      Stephan Schmidt <schst@php.net>
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 3862 $
 * @since       PHP 4.2.0
 * @require     PHP 4.0.0 (user_error)
 */
if (!function_exists('array_change_key_case')) {
    function array_change_key_case($input, $case = CASE_LOWER)
    {
        if (!is_array($input)) {
            user_error('array_change_key_case(): The argument should be an array',
                E_USER_WARNING);
            return false;
        }

        $output   = array ();
        $keys     = array_keys($input);
        $casefunc = ($case == CASE_LOWER) ? 'strtolower' : 'strtoupper';

        foreach ($keys as $key) {
            $output[$casefunc($key)] = $input[$key];
        }

        return $output;
    }
}

// From php.net
if(!function_exists('http_build_query')) {
   function http_build_query( $formdata, $numeric_prefix = null, $key = null ) {
       $res = array();
       foreach ((array)$formdata as $k=>$v) {
           $tmp_key = urlencode(is_int($k) ? $numeric_prefix.$k : $k);
           if ($key) $tmp_key = $key.'['.$tmp_key.']';
           $res[] = ( ( is_array($v) || is_object($v) ) ? http_build_query($v, null, $tmp_key) : $tmp_key."=".urlencode($v) );
       }
       $separator = ini_get('arg_separator.output');
       return implode($separator, $res);
   }
}


if (!function_exists('array_intersect_key')) {
   function array_intersect_key()
   {
       $arrs = func_get_args();
       $result = array_shift($arrs);
       foreach ($arrs as $array) {
           foreach ($result as $key => $v) {
               if (!array_key_exists($key, $array)) {
                   unset($result[$key]);
               }
           }
       }
       return $result;
   }
}

if (!function_exists('array_diff_key')) {
function array_diff_key()
{
    $args = func_get_args();
    if (count($args) < 2) {
        user_error('Wrong parameter count for array_diff_key()', E_USER_WARNING);
        return;
    }

    // Check arrays
    $array_count = count($args);
    for ($i = 0; $i !== $array_count; $i++) {
        if (!is_array($args[$i])) {
            user_error('array_diff_key() Argument #' .
                ($i + 1) . ' is not an array', E_USER_WARNING);
            return;
        }
    }

    $result = $args[0];
    if (function_exists('array_key_exists')) {
        // Optimize for >= PHP 4.1.0
        foreach ($args[0] as $key => $value) {
            for ($i = 1; $i !== $array_count; $i++) {
                if (array_key_exists($key,$args[$i])) {
                    unset($result[$key]);
                    break;
                }
            }
        }
    } else {
        foreach ($args[0] as $key1 => $value1) {
            for ($i = 1; $i !== $array_count; $i++) {
                foreach ($args[$i] as $key2 => $value2) {
                    if ((string) $key1 === (string) $key2) {
                        unset($result[$key2]);
                        break 2;
                    }
                }
            }
        }
    }
    return $result;
}
}

/**
 * Replace array_combine()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.array_combine
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.22 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_array_combine($keys, $values)
{
    if (!is_array($keys)) {
        user_error('array_combine() expects parameter 1 to be array, ' .
            gettype($keys) . ' given', E_USER_WARNING);
        return;
    }

    if (!is_array($values)) {
        user_error('array_combine() expects parameter 2 to be array, ' .
            gettype($values) . ' given', E_USER_WARNING);
        return;
    }

    $key_count = count($keys);
    $value_count = count($values);
    if ($key_count !== $value_count) {
        user_error('array_combine() Both parameters should have equal number of elements', E_USER_WARNING);
        return false;
    }

    if ($key_count === 0 || $value_count === 0) {
        user_error('array_combine() Both parameters should have number of elements at least 0', E_USER_WARNING);
        return false;
    }

    $keys    = array_values($keys);
    $values  = array_values($values);

    $combined = array();
    for ($i = 0; $i < $key_count; $i++) {
        $combined[$keys[$i]] = $values[$i];
    }

    return $combined;
}


// Define
if (!function_exists('array_combine')) {
    function array_combine($keys, $values)
    {
        return php_compat_array_combine($keys, $values);
    }
}


// Define
if (version_compare(phpversion(), '5.0') === -1) {
	// Needs to be wrapped in eval as clone is a keyword in PHP5
	eval('
	
		function php_compat_clone($object)
		{
			// Sanity check
			if (!is_object($object)) {
				user_error(\'clone() __clone method called on non-object\', E_USER_WARNING);
				return;
			}
		
			// Use serialize/unserialize trick to deep copy the object
			$object = unserialize(serialize($object));
		
			// If there is a __clone method call it on the "new" class
			if (method_exists($object, \'__clone\')) {
				$object->__clone();
			}
			
			return $object;    
		}
	
		function clone($object)
		{
			return php_compat_clone($object);
		}
	');
}

/**
 * Replace mkdir()
 *
 * Stream contexts aren't supported prior to PHP 5, reverts
 * to native function (to support contexts) in PHP 5+.
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        http://php.net/function.mkdir
 * @author      Arpad Ray <arpad@php.net>
 * @version     $Revision: 1.3 $
 * @since       PHP 5.0.0 (Added optional recursive and context parameters)
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_mkdir($pathname, $mode = 0777, $recursive = true, $context = null) {
    if (version_compare(PHP_VERSION, '5.0.0', 'gte')) {

        // revert to native function
        return (func_num_args() > 3)
            ? mkdir($pathname, $mode, $recursive, $context)
            : mkdir($pathname, $mode, $recursive);
    }

    if (!strlen($pathname)) {
        user_error('No such file or directory', E_USER_WARNING);
        return false;
    }

    if (is_dir($pathname)) {
        if (func_num_args() == 5) {
            // recursive call
            return true;
        }
        user_error('File exists', E_USER_WARNING);
        return false;
    }

    $parent_is_dir = php_compat_mkdir(dirname($pathname), $mode, $recursive, null, 0);

    if ($parent_is_dir) {
        return mkdir($pathname, $mode);
    }

    user_error('No such file or directory', E_USER_WARNING);
    return false;
}
// Define
if (!function_exists('mkdir')) {

    function mkdir($pathname, $mode, $recursive = false, $context = null)
    { 
        return php_compat_mkdir($pathname, $mode, $recursive, $context);
    }
}

?>