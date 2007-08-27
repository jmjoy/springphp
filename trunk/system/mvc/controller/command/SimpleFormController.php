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
 * SimpleFormController
 *
 * @package		Redstart
 * @subpackage  Controllers
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class SimpleFormController extends AbstractFormController {

	var $formView;
	var $successView;

	function SimpleFormController() {
		parent::AbstractFormController();
	}

	function &getFormView() {
		return $this->formView;
	}

	function &getSuccessView() {
		return $this->successView;
	}

	function &showForm(&$request, &$response, &$bindingResult, $controlModel = null) {

		return $this->showFormSupport(&$request, &$bindingResult, $this->getFormView(), $controlModel);
	}

	function &processFormSubmission(&$request, &$response, &$command, &$bindingResult) {

		if ($bindingResult->hasErrors()) {
			log_message(LOG_DEBUG,"Data binding errors: " . $bindingResult->getErrorCount());
			return $this->showForm(&$request, &$response, &$bindingResult);
		}
		else if ($this->isFormChangeRequest(&$request, &$command)) {
			log_message(LOG_DEBUG,"Detected form change request -> routing request to onFormChange");
			$this->onFormChange(&$request, &$response, &$command, &$bindingResult);
			return $this->showForm(&$request, &$response, &$bindingResult);
		}
		else {
			log_message(LOG_DEBUG,"No errors -> processing submit");
			return $this->onSubmit(&$request, &$response, &$command, &$bindingResult);
		}
	}

	function suppressValidation(&$request, &$command) {
		return $this->isFormChangeRequest(&$request, &$command);
	}

	function isFormChangeRequest(&$request, &$command) {
		return false;
	}

	function onFormChange(&$request, &$response, &$command, &$bindingResult) {

	}

	function &onSubmit(&$request,	&$response, &$command,	&$bindingResult) {
		$this->doSubmitAction(&$command);

		// default behavior: render success view
		if ($this->getSuccessView() == null) {
			show_error('SimpleFormController', "successView isn't set");
		}
		$mv =& new ModelAndView($this->getSuccessView(), array_merge($bindingResult->getModel(), $this->getSuccessModel()));
		return $mv;
	}

	function doSubmitAction(&$command) {}
	
	function &getSuccessModel() {
		$arr = array();
		return $arr;
	}
}

?>