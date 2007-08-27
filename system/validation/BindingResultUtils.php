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
 * BindingResultUtils
 *
 * @package		Redstart
 * @subpackage  Validation
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class BindingResultUtils {

	function getBindingResult(&$model, $name) {
		assert_not_null($model, "Model map must not be null");
		assert_not_null($name, "Name must not be null");
		$attrname = BINDING_RESULT_MODEL_KEY_PREFIX . $name;
		$attr = $model[$attrname];
		if ($attr != null && !(is_a($attr,'BindingResult'))) {
			show_error('Illegal State', "BindingResult attribute is not of type BindingResult: " . $attr);
		}
		return $attr;
	}


	function getRequiredBindingResult(&$model, $name) {
		$bindingResult = $this->getBindingResult(&$model, $name);
		if ($bindingResult == null) {
			show_error('Illegal State', "No BindingResult attribute found for name '" . $name .
					"'- have you exposed the correct model?");
		}
		return $bindingResult;
	}

}
?>
