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

if ( ! defined('E_STRICT'))
{
	define('E_STRICT', 2048);
}

error_reporting( E_ALL );//E_ERROR | E_WARNING | E_PARSE);
set_magic_quotes_runtime(0); // Kill magic quotes

include(BASEPATH .'errors/Exceptions.php');

$all_system_files = array(

// Utility Functions & Classes
'util/constants.php',
'util/functions.php',
'util/compat.php',
'util/string.php',
'util/patterns.php',
'util/locale.php',
'util/files.php',
'util/filters.php',
'util/libraries.php',
'util/dates.php',
'util/url.php',
'util/yaml.php',
'util/validation.php',
'util/classes/AntPathMatcher.php',
'util/classes/Timestamp.php',
'util/classes/Date.php',
'util/classes/Time.php',
'util/classes/SelectedValueComparator.php',
'util/classes/Pagination.php',
'util/classes/Sort.php',

// Taglibs
//'helpers/prototype_helper.php',
//'helpers/scriptaculous_helper.php',
'tags/tags.php',
'tags/asset_tags.php',
'tags/javascript_tags.php',
'tags/form_tags.php',
'tags/bind_tags.php',
'tags/pagination_tags.php',


// HTTP Classes
'http/Request.php',
'http/Response.php',
'http/Cookie.php',
'http/Locale.php',
'http/Session.php',
'http/UserAgent.php',
'http/UploadedFile.php',
'http/RequestUtils.php',

// Context
'context/drip/Configuration.php',
'context/drip/Instantiator.php',
'context/drip/PropertyPoint.php',
'context/drip/ServiceModel.php',
'context/drip/ServicePoint.php',
'context/AppContext.php',


// Theme Resolvers
//'theme/ThemeResolver.php',
//'theme/FixedThemeResolver.php',

// Locale
//'locale/Locale.php',

// Locale Resolvers
//'locale/LocaleResolver.php',
//'locale/AcceptHeaderLocaleResolver.php',



// Model & Views
'mvc/model/ModelAndView.php',
'mvc/view/View.php',
'mvc/view/RedirectView.php',
'mvc/view/PHPView.php',

// Handler
'mvc/handler/HandlerExecutionChain.php',

// Handler Interceptor
'mvc/handler/interceptor/HandlerInterceptor.php',

// Handler Mappings
'mvc/handler/mapping/HandlerMapping.php',
'mvc/handler/mapping/AbstractUrlHandlerMapping.php',
'mvc/handler/mapping/SimpleUrlHandlerMapping.php',
'mvc/handler/mapping/ServiceNameUrlHandlerMapping.php',

// View Name Translator
'mvc/view_name_translator/RequestToViewNameTranslator.php',

// View Resolvers
'mvc/view_resolver/ViewResolver.php',
'mvc/view_resolver/UrlBasedViewResolver.php',
'mvc/view_resolver/BeanNameViewResolver.php',

// Dispatcher
'mvc/Dispatcher.php',

// Controllers
'mvc/controller/Controller.php',
'mvc/controller/view/AbstractUrlViewController.php',
'mvc/controller/view/UrlFilenameViewController.php',
'mvc/controller/view/ParameterizableViewController.php',


// Beans
'beans/PropertyEditor.php',
'beans/propertyeditors/BooleanEditor.php',
'beans/propertyeditors/IntEditor.php',
'beans/propertyeditors/FloatEditor.php',
'beans/propertyeditors/StringArrayEditor.php',
'beans/propertyeditors/DateEditor.php',
//'beans/propertyeditors/TimeEditor.php',
//'beans/propertyeditors/TimestampEditor.php',
'beans/propertyeditors/LocaleEditor.php',
'beans/propertyeditors/SortEditor.php',
'beans/propertyeditors/PaginationEditor.php',
'beans/BeanWrapperUtils.php',
'beans/ClassInfo.php',
'beans/PropertyDescriptor.php',
'beans/BeanWrapper.php',
'beans/PropertyEditorRegistrar.php',
'beans/IllegalArgumentException.php',
'beans/InvalidPropertyException.php',
'beans/NotReadablePropertyException.php',
'beans/NotWriteablePropertyException.php',
'beans/NullValueInNestedPathException.php',
'beans/TypeMismatchException.php',

// Validation
'validation/BindingErrorProcessor.php',
'validation/BindingResult.php',
'validation/BindingResultUtils.php',
'validation/DataBinder.php',
'validation/MessageSourceResolvable.php',
'validation/ObjectError.php',
'validation/FieldError.php',
'validation/MessageCodesResolver.php',
'validation/WebDataBinder.php',
'validation/RequestDataBinder.php',
'validation/ValidationUtils.php',
'validation/Validator.php',
'validation/BindStatus.php',

//Command Controllers
'mvc/controller/command/BaseCommandController.php',
'mvc/controller/command/AbstractFormController.php',
'mvc/controller/command/SimpleFormController.php',

);

$one_system_file = BASEPATH.'cache/system.php';

if(defined('ONE_SYS_FILE') && ONE_SYS_FILE == true) {

	if(file_exists($one_system_file)){
		include($one_system_file);
	}else{
		if(! is_writable(dirname($one_system_file)) ) show_error('System Error', 'System path not writeable: '.dirname($one_system_file));
		if (!$fp = @ fopen($one_system_file, 'wb')) show_error('System Error', 'Can\'t write system file');

		$sys_file_contents = '';
		foreach ($all_system_files as $file) {
			if (function_exists('file_get_contents'))
				$data = file_get_contents(BASEPATH.$file);
			else {
				if ( ! $fp = @fopen(BASEPATH.$file, 'rb'))
					show_error('System Error', 'Can\'t read system file: '.BASEPATH.$file);

				flock($fp, LOCK_SH);

				$data = '';
				if (filesize($file) > 0)
				{
					$data =& fread($fp, filesize($file));
				}

				flock($fp, LOCK_UN);
				fclose($fp);
			}
			$sys_file_contents .= "<?php\n// ".$file."\n?>\n";
			$sys_file_contents .= $data;
			include(BASEPATH.$file);
		}

		flock($fp, LOCK_EX);
		fwrite($fp, $sys_file_contents);
		flock($fp, LOCK_UN);
		fclose($fp);
		@ chmod($one_system_file, 0777);
	}

}else{

	foreach($all_system_files as $file) {
		include(BASEPATH.$file);
	}

}


?>