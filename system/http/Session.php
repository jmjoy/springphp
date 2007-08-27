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
 * Session
 *
 * @package		Redstart
 * @subpackage  HTTP
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class Session {

	var $_flash = array();

	function Session() {
		session_start();

        $this->_flash = $this->getAttribute(null, 'flash');
        $this->removeAttribute(null, 'flash');
	}

	function getAttribute($name = null, $namespace = 'default') {
	    if(isset($name))
	        return isset($_SESSION[$namespace][$name]) ? $_SESSION[$namespace][$name] : null;
        else
            return isset($_SESSION[$namespace]) ? $_SESSION[$namespace] : null;
	}

	function getAttributeNames($namespace = 'default') {
		if(isset($namespace) && $namespace == null)
			return array_keys($_SESSION);
		else
			return isset($_SESSION[$namespace])?array_keys($_SESSION[$namespace]):null;
	}

	function removeAttribute($name = null, $namespace = 'default') {
        if(isset($name) && ($name !== null))
            unset($_SESSION[$namespace][$name]);
        else
            unset($_SESSION[$namespace]);
	}

	function setAttribute($name, $value, $namespace = 'default') {
		if ($name == null) {
            $_SESSION[$namespace] = $value;
        } else {
            $_SESSION[$namespace][$name] = $value;
        }
	}

    function setFlashAttribute($name, $val) {
        $this->setAttribute($name, $val, 'flash');
    }

	function getFlashAttribute($name) {
        if (isset($this->_flash[$name])) {
            return $this->_flash[$name];
        } else {
            return null;
        }
    }

    function keepFlashAttribute($name = null) {
        if ($name != null) {
            $this->setFlashAttribute($name, $this->getFlashAttribute($name));
        } else {
            $this->setAttribute(null, $this->_flash, 'flash');
        }
    }

    function removeFlashAttribute($name) {
    	$this->removeAttribute($name, 'flash');
    }


	function getID() {
		return session_id();
	}

	function regenerateID() {
		session_regenerate_id();
	}

	function getMaxInactiveInterval() {
		return session_cache_expire();
	}

	function setMaxInactiveInterval($minutes) {
		session_cache_expire($minutes);
	}

	function invalidate() {
		session_destroy();
	}



}
?>
