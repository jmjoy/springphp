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
 * Cookie
 *
 * @package		Redstart
 * @subpackage  HTTP
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class Cookie {

	var $name = '';
	var $value = '';
	var $expire = 0;
	var $path = '/';
	var $domain = '';
	var $prefix = '';

	function Cookie($name = '', $value = '', $expire = '', $domain = '', $path = '/', $prefix = '') {
		$this->setName($name);
		$this->setValue($value);
		$this->setExpire($expire);
		$this->setPath($path);
		$this->setDomain($domain);
		$this->setPrefix($prefix);
	}

	function getName(){
		return $this->name;
	}

	function setName($value) {
		$this->name = $value;
	}

	function getValue() {
		return $this->value;
	}

	function setValue($value) {
		$this->value = $value;
	}

	function getExpire() {
		if ( ! is_numeric($this->expire))
		{
			$this->expire = time() - 86500;
		}
		else
		{
			if ($this->expire > 0)
			{
				$this->expire =  time() + $this->expire;
			}
			else
			{
				$this->expire = 0;
			}
		}
		return $this->expire;
	}

	function setExpire($expire) {
		$this->expire = $expire;
	}

	function getPath() {
		return $this->path;
	}

	function setPath($path) {
		$this->path = $path;
	}

	function getSecure() {
		return $this->secure;
	}

	function setSecure($secure) {
		$this->secure = $secure;
	}

	function getDomain() {
		return $this->domain;
	}

	function setDomain($domain) {
		$this->domain = $domain;
	}

	function getPrefix() {
		return $this->prefix;
	}

	function setPrefix($prefix) {
		$this->prefix = $prefix;
	}

	function toArray() {
		$array = array();
		foreach (array('name', 'value', 'expire', 'path', 'domain') as $item)
		{
			if($item == 'name')
				$array[] = $this->getPrefix().$this->getName();
			else
			{
				$method = 'get'.strtoupper($item);
				$array[] = $this->$method();
			}

		}
		return $array;
	}
}
?>
