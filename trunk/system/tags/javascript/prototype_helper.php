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

function get_callbacks() {
	static $callbacks;
	if (!$callbacks) {
		$callbacks = array_merge(array (
			'uninitialized',
			'loading',
			'loaded',
			'interactive',
			'complete',
			'failure',
			'success'
		), range(100, 599));
	}

	return $callbacks;
}

function get_ajax_options() {
	static $ajax_options;
	if (!$ajax_options) {
		$ajax_options = array_merge(array (
			'before',
			'after',
			'condition',
			'url',
			'asynchronous',
			'method',
			'insertion',
			'position',
			'form',
			'with',
			'update',
			'script'
		), get_callbacks());
	}

	return $ajax_options;
}

function link_to_remote($name, $options = array (), $html_options = array ()) {
	return link_to_function($name, remote_function($options), $html_options);
}

function periodically_call_remote($options = array ()) {
	$frequency = isset ($options['frequency']) ? $options['frequency'] : 10; // every ten seconds by default
	$code = 'new PeriodicalExecuter(function() {' . remote_function($options) . '}, ' . $frequency . ')';

	return javascript_tag($code);
}

function form_remote_tag($options = array (), $options_html = array ()) {
	$options = _parse_attributes($options);
	$options_html = _parse_attributes($options_html);

	$options['form'] = true;

	$options_html['onsubmit'] = remote_function($options) . '; return false;';
	$options_html['action'] = isset ($options_html['action']) ? $options_html['action'] : system_url($options['url']);
	$options_html['method'] = isset ($options_html['method']) ? $options_html['method'] : 'post';

	return tag('form', $options_html, true);
}

function submit_to_remote($name, $value, $options = array ()) {
	if (!isset ($options['with'])) {
		$options['with'] = 'Form.serialize(this.form)';
	}

	if (!isset ($options['html'])) {
		$options['html'] = array ();
	}
	$options['html']['type'] = 'button';
	$options['html']['onclick'] = remote_function($options) . '; return false;';
	$options['html']['name'] = $name;
	$options['html']['value'] = $value;

	return tag('input', $options['html'], false);
}


function update_element_function($element_id, $options = array ()) {
	$obj =& get_instance();
	$obj->response->enqueue_js('prototype');

	$content = escape_javascript(isset ($options['content']) ? $options['content'] : '');

	$value = isset ($options['action']) ? $options['action'] : 'update';
	switch ($value) {
		case 'update' :
			if ($options['position']) {
				$javascript_function = "new Insertion." . Inflector::camelize($options['position']) . "('$element_id','$content')";
			} else {
				$javascript_function = "\$('$element_id').innerHTML = '$content'";
			}
			break;

		case 'empty' :
			$javascript_function = "\$('$element_id').innerHTML = ''";
			break;

		case 'remove' :
			$javascript_function = "Element.remove('$element_id')";
			break;

		default :
			show_error('Invalid action, choose one of update, remove, empty');
	}

	$javascript_function .= ";\n";

	return (isset ($options['binding']) ? $javascript_function . $options['binding'] : $javascript_function);
}

function evaluate_remote_response() {
	return 'eval(request.responseText)';
}

function remote_function($options) {
	$obj =& get_instance();
	$obj->response->enqueue_js('prototype');

	$javascript_options = _options_for_ajax($options);

	$update = '';
	if (isset ($options['update']) && is_array($options['update'])) {
		$update = array ();
		if (isset ($options['update']['success'])) {
			$update[] = "success:'" . $options['update']['success'] . "'";
		}
		if (isset ($options['update']['failure'])) {
			$update[] = "failure:'" . $options['update']['failure'] . "'";
		}
		$update = '{' . join(',', $update) . '}';
	} else
		if (isset ($options['update'])) {
			$update .= "'" . $options['update'] . "'";
		}

	$function = !$update ? "new Ajax.Request(" : "new Ajax.Updater($update, ";

	$function .= '\'' . system_url($options['url']) . '\'';
	$function .= ', ' . $javascript_options . ')';

	if (isset ($options['before'])) {
		$function = $options['before'] . '; ' . $function;
	}
	if (isset ($options['after'])) {
		$function = $function . '; ' . $options['after'];
	}
	if (isset ($options['condition'])) {
		$function = 'if (' . $options['condition'] . ') { ' . $function . '; }';
	}
	if (isset ($options['confirm'])) {
		$function = "if (confirm('" . escape_javascript($options['confirm']) . "')) { $function; }";
		if (isset ($options['cancel'])) {
			$function = $function . ' else { ' . $options['cancel'] . ' }';
		}
	}

	return $function;
}

function observe_field($field_id, $options = array ()) {
	$obj =& get_instance();
	$obj->response->enqueue_js('prototype');

	if (isset ($options['frequency']) && $options['frequency'] > 0) {
		return _build_observer('Form.Element.Observer', $field_id, $options);
	} else {
		return _build_observer('Form.Element.EventObserver', $field_id, $options);
	}
}

function observe_form($form_id, $options = array ()) {
	$obj =& get_instance();
	$obj->response->enqueue_js('prototype');

	if (isset ($options['frequency']) && $options['frequency'] > 0) {
		return _build_observer('Form.Observer', $form_id, $options);
	} else {
		return _build_observer('Form.EventObserver', $form_id, $options);
	}
}



function _options_for_ajax($options) {
	$js_options = _build_callbacks($options);

	$js_options['asynchronous'] = (isset ($options['type'])) ? ($options['type'] != 'synchronous') : 'true';
	if (isset ($options['method']))
		$js_options['method'] = _method_option_to_s($options['method']);
	if (isset ($options['position']))
		$js_options['insertion'] = "Insertion." . Inflector::camelize($options['position']);
	$js_options['evalScripts'] = (!isset ($options['script']) || $options['script'] == '0' || $options['script'] == 'false') ? 'false' : 'true';

	if (isset ($options['form'])) {
		$js_options['parameters'] = 'Form.serialize(this)';
	} else
		if (isset ($options['submit'])) {
			$js_options['parameters'] = "Form.serialize(document.getElementById('{$options['submit']}'))";
		} else
			if (isset ($options['with'])) {
				$js_options['parameters'] = $options['with'];
			}

	return _options_for_javascript($js_options);
}


function _method_option_to_s($method) {
	return (is_string($method) && $method[0] != "'") ? $method : "'$method'";
}

function _build_observer($klass, $name, $options = array ()) {
	if (!isset ($options['with']) && isset($options['update'])) {
		$options['with'] = 'value';
	}

	if(isset($options['function']))
		$callback = $options['function'];
	else
		$callback = remote_function($options);

	$javascript = 'new ' . $klass . '("' . $name . '", ';
	if (isset ($options['frequency'])) {
		$javascript .= $options['frequency'] . ", ";
	}
	$javascript .= "function(element, value) {";
	$javascript .= $callback . '});';

	return javascript_tag($javascript);
}

function _build_callbacks($options) {
	$callbacks = array ();
	foreach (get_callbacks() as $callback) {
		if (isset ($options[$callback])) {
			$name = 'on' . ucfirst($callback);
			$code = $options[$callback];
			$callbacks[$name] = 'function(request, json){' . $code . '}';
		}
	}

	return $callbacks;
}


?>
