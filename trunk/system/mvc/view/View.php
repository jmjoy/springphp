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
 * View
 *
 * @abstract
 * @package		Redstart
 * @subpackage  View
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class View {

	var $serviceName;
	var $contentType = DEFAULT_CONTENT_TYPE;
	var $requestContextAttribute;
	var $staticAttributes = array();
	var $url = null;

	function View() {}

	function render(&$model, &$request, &$response) {
		if(log_enabled(LOG_DEBUG))
			log_message(LOG_DEBUG,"Rendering view with name '" .$this->serviceName . "' with model " . var_export($model, true) .
				" and static attributes " . var_export($this->staticAttributes, true));

		// Consolidate static and dynamic model attributes.
		$mergedModel = array_merge($this->staticAttributes, $model);

		return $this->renderMergedOutputModel($mergedModel, $request, $response);
	}



}


?>