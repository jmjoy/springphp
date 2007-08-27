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
 * CancellableFormController
 *
 * @package		Redstart
 * @subpackage  Controllers
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class CancellableFormController extends SimpleFormController {

	var $cancelParamKey = '_cancel';

	var $cancelView;

	function CancellableFormController() {
		parent::SimpleFormController();
	}

	function &getCancelView() {
		return $this->cancelView;
	}

	function &getCancelParamKey() {
		return $this->cancelParamKey;
	}

	function isFormSubmission(&$request) {
		return parent::isFormSubmission(&$request) || $this->isCancelRequest(&$request);
	}

	function &processFormSubmission(&$request, &$response, &$command, &$bindingResult) {

		if ($this->isCancelRequest(&$request)) {
			return $this->onCancel(&$request, &$response, &$command);
		}
		else {
			return parent::processFormSubmission(&$request, &$response, &$command, &$errors);
		}

	}

	function isCancelRequest(&$request) {
		return RequestUtils::hasSubmitParameter(&$request, $this->getCancelParamKey());
	}

	function &onCancel(&$request, &$response, &$command) {
		$mv =& new ModelAndView($this->getCancelView());
		return $mv;
	}
}

?>