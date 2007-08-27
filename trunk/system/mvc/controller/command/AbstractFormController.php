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
 * AbstractFormController
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
class AbstractFormController extends BaseCommandController {

	var $bindOnNewForm = false;

	var $sessionForm = false;

	function AbstractFormController() {
		$this->cacheSeconds = 0;
	}

	function &isBindOnNewForm() {
		return $this->bindOnNewForm;
	}

	function &isSessionForm() {
		return $this->sessionForm;
	}

	function &handleRequestInternal(&$request, &$response) {

		// Form submission or new form to show?
		if ($this->isFormSubmission(&$request)==true) {
			// Fetch form object from HTTP session, bind, validate, process submission.
			$command =& $this->getCommand(&$request);
			if($this->isSessionForm()==true && $command == null){
				log_message(LOG_DEBUG, 'Invalid submit detected: no command in session');
				return $this->handleInvalidSubmit(&$request, &$response);
			}

			$binder =& $this->bindAndValidate(&$request, &$command);
			$bindingResult =& $binder->getBindingResult();
			return $this->processFormSubmission(&$request, &$response, &$command, &$bindingResult);
		}

		else {
			// New form to show: render form view.
			return $this->showNewForm(&$request,&$response);
		}
	}

	function  isFormSubmission(&$request) {
		return $request->getMethod() == "POST";
	}

	function getFormSessionAttributeName($request) {
		return get_class($this) . ".FORM." . $this->getCommandName();
	}

	function &showNewForm(&$request, &$response) {

		log_message(LOG_DEBUG,"Displaying new form");
		return $this->showForm(&$request, &$response, $this->getBindingResultForNewForm(&$request));
	}

	function &getBindingResultForNewForm(&$request) {
		// Create form-backing object for new form.
		$command =& $this->formBackingObject(&$request);
		if ($command == null) {
			show_error('Error', "Form object returned by formBackingObject() must not be null");
		}
		if (!$this->checkCommand(&$command)) {
			show_error('Error', "Form object returned by formBackingObject() must match commandClass");
		}

		// Bind without validation, to allow for prepopulating a form, and for
		// convenient error evaluation in views (on both first attempt and resubmit).
		$binder =& $this->createBinder(&$request, &$command);
		$bindingResult =& $binder->getBindingResult();
		if ($this->isBindOnNewForm()==true) {
			log_message(LOG_DEBUG, "Binding to new form");
			$binder->bind(&$request);
			$this->onBindOnNewForm(&$request, &$command,&$bindingResult);
		}

		// Return BindException object that resulted from binding.
		return $bindingResult;
	}

	function onBindOnNewForm(&$request, &$command, &$bindingResult) {}

	function &getCommand(&$request) {
		// If not in session-form mode, create a new form-backing object.
		if ($this->isSessionForm()!==true) {
			return $this->formBackingObject(&$request);
		}

		// Session-form mode: retrieve form object from HTTP session attribute.
		$session =& $request->getSession();
		$formAttrName = $this->getFormSessionAttributeName(&$request);
		$sessionFormObject = $session->getAttribute($formAttrName);
		if ($sessionFormObject == null) {
			return _null();
//			show_error('Error', "Form object not found in session (in session-form mode)");
		}

		// Remove form object from HTTP session: we might finish the form workflow
		// in this request. If it turns out that we need to show the form view again,
		// we'll re-bind the form object to the HTTP session.
		log_message(LOG_DEBUG,"Removing form session attribute [" . $formAttrName . "]");
		$session->removeAttribute($formAttrName);

		return $this->currentFormObject(&$request, $sessionFormObject);
	}

	function &formBackingObject(&$request) {
		return $this->createCommand();
	}

	function &currentFormObject(&$request, &$sessionFormObject) {
		return $sessionFormObject;
	}

	function &showFormSupport(&$request, &$bindingResult, $viewName, $controlModel = null) {

		// In session form mode, re-expose form object as HTTP session attribute.
		// Re-binding is necessary for proper state handling in a cluster,
		// to notify other nodes of changes in the form object.
		if ($this->isSessionForm()===true) {
			$formAttrName = $this->getFormSessionAttributeName(&$request);
			if(log_enabled(LOG_DEBUG))
				log_message(LOG_DEBUG,"Setting form session attribute [" . $formAttrName . "] to: " . get_class($bindingResult->getTarget()));
			$session =& $request->getSession();
			$session->setAttribute($formAttrName, $bindingResult->getTarget());
		}

		// Fetch errors model as starting point, containing form object under
		// "commandName", and corresponding Errors instance under internal key.
		$model =& $bindingResult->getModel();

		// Merge reference data into model, if any.
		$referenceData = $this->referenceData(&$request, $bindingResult->getTarget(), $bindingResult);
		if ($referenceData != null) {
			$model = array_merge($model, $referenceData);
		}

		// Merge control attributes into model, if any.
		if ($controlModel != null) {
			$model = array_merge($model, $controlModel);
		}

		// Trigger rendering of the specified view, using the final model.
		$mv =& new ModelAndView($viewName, &$model);
		return $mv;
	}

	function &referenceData(&$request, $command, $bindingResult = null) {
		return _null();
	}

	function &showForm(&$request, &$response, &$bindingResult) {}

	function &processFormSubmission(&$request, &$response, &$command, &$bindingResult) {}

	function &handleInvalidSubmit(&$request, &$response) {

		$command =& $this->formBackingObject(&$request);
		$binder =& $this->bindAndValidate(&$request, &$command);
		$bindingResult =& $binder->getBindingResult();
		return $this->processFormSubmission(&$request, &$response, &$command, &$bindingResult);
	}

}

?>