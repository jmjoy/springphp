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
 *  Dispatcher
 *
 *  <table>
 *  <tr>
 *      <td><b>name</b></th>
 *      <td><b>default</b></td>
 *      <td><b>description</b></td>
 *  </tr>
 *  <tr>
 *      <td>localeResolver</td>
 *      <td>null</td>
 *      <td>instance of the LocaleResolver, no default strategy</td>
 *  </tr>
 *  <tr>
 *      <td>themeResolver</td>
 *      <td>null</td>
 *      <td>instance of the ThemeResolver, no default strategy</td>
 *  </tr>
 *  <tr>
 *      <td>handlerMapping</td>
 *      <td>null</td>
 *      <td>instance of the HandlerMapping, default strategy is {@link ServiceNameUrlHandlerMapping}</td>
 *  </tr>
 *  <tr>
 *      <td>viewNameTranslator</td>
 *      <td>null</td>
 *      <td>instance of the RequestToViewNameTranslator, default strategy is {@link RequestToViewNameTranslator}</td>
 *  </tr>
 *  <tr>
 *      <td>viewResolver</td>
 *      <td>null</td>
 *      <td>instance of the ViewResolver, default strategy is {@link UrlBasedViewResolver}</td>
 *  </tr>
 *  </table>
 *
 * @package		Redstart
 * @subpackage  Dispatcher
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class Dispatcher {

	var $localeResolver;
	var $themeResolver;
	var $handlerMapping;
	var $viewNameTranslator;
 	var $viewResolver;

	var $detectFlags = array(
		'handlerMapping'=> true,
		'viewResolver'=> true
	);

	var $defaultStrategies = array(
//TODO: no locale resolver = ignore
//		LOCALE_RESOLVER_SERVICE => 'AcceptHeaderLocaleResolver',
//TODO: no theme resolver = ignore
//		THEME_RESOLVER_SERVICE => 'FixedThemeResolver',
		HANDLER_MAPPING_SERVICE => 'ServiceNameUrlHandlerMapping',
		REQUEST_TO_VIEW_NAME_TRANSLATOR_SERVICE => 'RequestToViewNameTranslator',
		VIEW_RESOLVER_SERVICE => 'UrlBasedViewResolver'
	);


	function Dispatcher() {}

	function initDispatcher() {
		$this->_resolveService(LOCALE_RESOLVER_SERVICE);
		$this->_resolveService(THEME_RESOLVER_SERVICE);
		$this->_resolveServices(HANDLER_MAPPING_SERVICE);
		$this->_resolveService(REQUEST_TO_VIEW_NAME_TRANSLATOR_SERVICE);
		$this->_resolveServices(VIEW_RESOLVER_SERVICE);
	}

	function &_getDefaultStrategy($className) {
		$strategyClass = isset($this->defaultStrategies[$className])?$this->defaultStrategies[$className]:null;
		$obj = _null();
		if(!is_null($strategyClass) && !empty($strategyClass))
			$obj =& AppContext::createAutowiredService($strategyClass);
		return $obj;
	}

	function _resolveService($serviceName) {
		if($this->$serviceName == NULL)
		{
			$this->$serviceName =& AppContext::service($serviceName);
			if($this->$serviceName == NULL){
				$this->$serviceName =& $this->_getDefaultStrategy($serviceName);
			}
		}
		if(log_enabled(LOG_DEBUG))
			log_message(LOG_DEBUG, 'Using '.$serviceName.' ['.get_class($this->$serviceName).']');
	}

	function _resolveServices($serviceName) {
		if($this->$serviceName == NULL)
		{
			if(isset($this->detectFlags[$serviceName]) && $this->detectFlags[$serviceName] == true)
			{
				//$results =& AppContext::getServicesByNameMatchEnd($serviceName);
				$results =& AppContext::getServicesOfType($serviceName);
				usort(&$results, 'order_comparator');
				$this->$serviceName =& $results;
			}
			if($this->$serviceName == NULL || empty($this->$serviceName)){
				$this->$serviceName =& $this->_getDefaultStrategy($serviceName);
				if(log_enabled(LOG_DEBUG))
					log_message(LOG_DEBUG, 'Using '.$serviceName.' ['.get_class($this->$serviceName).']');
			}
		}
		if(!is_array($this->$serviceName)) {
			$array = array();
			$array[] = $this->$serviceName;
			$this->$serviceName =& $array;
		}
	}

	function process(&$request, &$response) {

		//init all the resolvers, mappings, handlers, and translators
		$this->initDispatcher();

		//resolve the locale
		if(!is_null($this->localeResolver))
			$request->locale =& $this->localeResolver->resolveLocale(&$request, &$response);

		//themes are loaded after initializing the Dispatcher
		//so they can't override core functionality with their context (only plugins can do that)
		if(!is_null($this->themeResolver))
			$request->theme =& load_theme($this->themeResolver->resolveThemeName(&$request, &$response));

		//load text domains AFTER the app, plugins, and theme has been loaded (so they can add their domains)
		if(!is_null($request->locale))
			i18n_load_text_domains($request->locale);

		//do it
		$this->_dispatch($request, $response);

	}


	function _dispatch(&$request, &$response) {

		$mv = null;
		$interceptorIndex = -1;

		//TODO: expose localeResolver, themeResolver, handlerMapping, viewNameTranslator, etc to request attributes

		$hec =& $this->_getHandlerExecutionChain(&$request);
		if($hec == null) $handler = null;
		else $handler =& $hec->getHandler();

		if($hec == null || $handler == null) {
			$this->_noHandlerFound(&$request, &$response);
			return;
		}

		$interceptors =& $hec->getInterceptors();
		if($interceptors != null) {
			for($i = 0; $i < count($interceptors); $i++) {
				$interceptor =& $interceptors[$i];
				if(!$interceptor->preHandle(&$request, &$response, &$handler)) {
					$this->_triggerAfterCompletion(&$hec, $interceptorIndex, &$request, &$response, null);
					return;
				}
				$interceptorIndex = $i;
			}

		}

		//$ha =& $this->_getHandlerAdapter(&$handler);
		//$mv =& $ha->handle(&$request, &$response, &$handler);
		$mv =& $handler->handleRequest(&$request, &$response);
		if($interceptors != null) {
			for($i = 0; $i < count($interceptors); $i++) {
				$interceptor =& $interceptors[$i];
				$interceptor->postHandle(&$request, &$response, &$handler, &$mv);
			}
		}


		// Did the handler return a view to render?
		if ($mv != null && !$mv->wasCleared()) {
			$viewResult = $this->_render(&$mv, &$request, &$response);
			if($viewResult === FALSE) show_error('Dispatcher Error', 'Failed rendering view: ['.$mv->getViewName() .']');
		}
		else {
			if(log_enabled(LOG_DEBUG))
				log_message(LOG_DEBUG, "Null ModelAndView returned to Dispatcher: assuming HandlerAdapter completed request handling");
		}

		// Trigger after-completion for successful outcome.
		$this->_triggerAfterCompletion(&$hec, $interceptorIndex, &$request, &$response, null);

	}

	function &_getHandlerExecutionChain(&$request) {
		foreach($this->handlerMapping as $hm) {
			assert_not_null($hm, 'HandlerMapping was null');
			if(log_enabled(LOG_DEBUG))
				log_message(LOG_DEBUG, "Testing handler map [".get_class($hm)."]");
			$handler =& $hm->getHandler(&$request);
			if($handler != null)
				return $handler;
		}
		return _null();
	}

	function _noHandlerFound(&$request, &$response) {
		if(log_enabled(LOG_WARNING))
			log_message(LOG_WARNING, "No mapping for [" . $request->getContextPath() . "]");
		$response->setStatus(SC_NOT_FOUND);
		include(BASEPATH.'errors/views/error_404'.EXT);
	}

	function _render(&$mv, &$request, &$response) {

		$view = null;
		// Do we need view name translation?
		if (!$mv->hasView()) {
			$mv->view =& $this->_getDefaultViewName($request);
		}

		if ($mv->viewIsReference()) {

			// We need to resolve the view name.
			$view =& $this->_resolveViewName($mv->getViewName(), $request->locale, &$request);
			if ($view == null) show_error('Dispatcher Error', 'Could not resolve view with name "'. $mv->getViewName().'"');
		}
		else {
			// No need to lookup: the ModelAndView object contains the actual View object.
			$view =& $mv->getView();
			if ($view == null) show_error('Dispatcher Error', 'ModelAndView lacks a view name or a View Object');
		}

		// Delegate to the View object for rendering.
		if(log_enabled(LOG_DEBUG))
			log_message(LOG_DEBUG, "Rendering view [" . get_class($view) .  "]");
		return $view->render($mv->getModel(), &$request, &$response);
	}

	function &_getDefaultViewName(&$request) {
		$viewName = $this->viewNameTranslator->getViewName(&$request);
		if ($viewName == null) show_error('Dispatcher Error', 'Could not translate request into view name using ['.get_class($this->viewNameTranslator).']');
		return $viewName;
	}

	function &_resolveViewName($viewName, &$locale, &$request) {
		foreach($this->viewResolver as $vr) {
			$view =& $vr->resolveViewName($viewName, &$locale);
			if($view != null) return $view;
		}
		return _null();
	}

	function _triggerAfterCompletion(&$hec, $interceptorIndex,&$request, &$response) {
		if ($hec != null) {
			$interceptors =& $hec->getInterceptors();
			if($interceptors != null) {
				for($i = $interceptorIndex; $i >= 0;  $i--) {
					$interceptor =& $interceptors[$i];
					$interceptor->afterCompletion(&$request, &$response, $hec->getHandler());
				}
			}
		}
	}
}

?>