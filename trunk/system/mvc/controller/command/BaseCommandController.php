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
 * BaseCommandController
 *
 * @abstract
 * @package		Redstart
 * @subpackage  Controllers
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class BaseCommandController extends Controller {

	var $commandName = DEFAULT_COMMAND_NAME;

	var $commandClass;

	var $validators;

	var $validateOnBinding = true;

	var $messageCodesResolver;
	var $bindingErrorProcessor;
	var $propertyEditorRegistrars;

	function &getCommandName() {
		return $this->commandName;
	}

	function &getCommandClass() {
		return $this->commandClass;
	}

	function &getValidator() {
		if(!is_array($this->validators)) return $this->validators;
		return ($this->validators==null || empty($this->validators))?_null():$this->validators[0];
	}

	function &getValidators() {
		return $this->validators;
	}

	function &isValidateOnBinding() {
		return $this->validateOnBinding;
	}

	function &getMessageCodesResolver() {
		return $this->messageCodesResolver;
	}

	function &getBindingErrorProcessor() {
		return $this->bindingErrorProcessor;
	}

	function &getPropertyEditorRegistrar() {
		if(!is_array($this->propertyEditorRegistrars)) return $this->propertyEditorRegistrars;
		return ($this->propertyEditorRegistrars==null || empty($this->propertyEditorRegistrars))?_null():$this->propertyEditorRegistrars[0];

	}

	function &getPropertyEditorRegistrars() {
		return $this->propertyEditorRegistrars;
	}

	function &getCommand(&$request) {
		return $this->createCommand();
	}

	function &createCommand() {
		if ($this->commandClass == null) {
			show_error('BaseCommandController',"Cannot create command without commandClass being set - " .
					"either set commandClass or (in a form controller) override formBackingObject");
		}
		log_message(LOG_DEBUG, "Creating new command of class [" . $this->commandClass . "]");
		$clazz = $this->commandClass;
		$obj =& new $clazz;
		return $obj;
	}

	function checkCommand(&$command) {
		return ($this->commandClass == null || is_subclass(get_class($command), $this->commandClass));
	}

	function &bindAndValidate(&$request, &$command) {

		$binder =& $this->createBinder(&$request, &$command);
		$bindingResult =& $binder->getBindingResult();
		if (!$this->suppressBinding(&$request)) {
			$binder->bind(&$request);
			$this->onBind(&$request, &$command, &$bindingResult);
			if($this->validators != null && $this->isValidateOnBinding() && !$this->suppressValidation(&$request, &$command)) {
				foreach((array)$this->validators as $validator) {
					ValidationUtils::invokeValidator(&$validator, &$command, &$bindingResult);
				}
			}
			$this->onBindAndValidate(&$request, &$command, &$bindingResult);
		}
		return $binder;
	}

	function suppressBinding(&$request) {
		return false;
	}

	function suppressValidation(&$request, &$command) {
		return false;
	}


	function &createBinder(&$request, &$command) {

		$binder = new RequestDataBinder(&$command, $this->getCommandName());
		$this->prepareBinder(&$binder);
		$this->initBinder(&$request, &$binder);
		return $binder;
	}

	function prepareBinder(&$binder) {
		if ($this->messageCodesResolver != null) {
			$binder->setMessageCodesResolver($this->messageCodesResolver);
		}
		if ($this->bindingErrorProcessor != null) {
			$binder->setBindingErrorProcessor($this->bindingErrorProcessor);
		}
		if($this->propertyEditorRegistrars != null) {
			foreach((array)$this->propertyEditorRegistrars as $per) {
				$per->registerPropertyEditors(&$binder);
			}
		}
	}

	function initBinder(&$request, &$binder){}

	function onBind(&$request, &$command, &$bindingResult) {}

	function onBindAndValidate(&$request, &$command, &$bindingResult) {}

}

?>