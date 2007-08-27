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
 * ModelAndView
 *
 * @package		Redstart
 * @subpackage  Model
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class ModelAndView {

	var $view;
	var $model;

	var $cleared;

	function ModelAndView($view = null, $model = null) {
		$this->view = $view;
		if($model != null && !is_array($model)) $model = array($model);
		$this->model = $model;
	}

	function hasView() {
		return ($this->view != null);
	}

	function viewIsReference() {
		return is_string($this->view);
	}

	function &getView() {
		return $this->view;
	}

	function getViewName() {
		return (is_string($this->view) ? $this->view : null);
	}

	function &getModel() {
		if($this->model == null) $this->model = array();
		return $this->model;
	}

	function addObject($name, &$value) {
		$this->model[$name] =& $value;
	}

	function addObjectOnly(&$value) {
		$name = get_qualified_type($value);
		$this->addObject($name, &$value);
	}

	function clear() {
		$this->view = null;
		$this->model = null;
		$this->cleared = true;
	}

	function isEmpty() {
		return ($this->view == null && $this->model == null);
	}

	function wasCleared() {
		return ($this->cleared && isEmpty());
	}

}

?>