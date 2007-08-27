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

function visual_effect($name, $element_id = false, $js_options = array ()) {
	$obj =& get_instance();
	$obj->response->enqueue_js(array('prototype', 'builder', 'effects'));

	$element = $element_id ? "'$element_id'" : 'element';

	if (in_array($name, array (
			'toggle_appear',
			'toggle_blind',
			'toggle_slide'
		))) {
		return "new Effect.toggle($element, " . substr($name, 7) . ", " . _options_for_javascript($js_options) . ");";
	} else {
		return "new Effect." . Inflector :: camelize($name) . "($element, " . _options_for_javascript($js_options) . ");";
	}
}

function sortable_element($element_id, $options = array ()) {
	$obj =& get_instance();
	$obj->response->enqueue_js(array('prototype', 'builder', 'effects', 'dragdrop'));

	if (!isset ($options['with'])) {
		$options['with'] = "Sortable.serialize('$element_id')";
	}

	if (!isset ($options['onUpdate'])) {
		$options['onUpdate'] = "function(){" . remote_function($options) . "}";
	}

	foreach (get_ajax_options() as $key) {
		unset ($options[$key]);
	}

	foreach (array (
			'tag',
			'overlap',
			'constraint',
			'handle'
		) as $option) {
		if (isset ($options[$option])) {
			$options[$option] = "'{$options[$option]}'";
		}
	}

	if (isset ($options['containment'])) {
		$options['containment'] = _array_or_string_for_javascript($options['containment']);
	}

	if (isset ($options['hoverclass'])) {
		$options['hoverclass'] = "'{$options['hoverclass']}'";
	}

	if (isset ($options['only'])) {
		$options['only'] = _array_or_string_for_javascript($options['only']);
	}

	return javascript_tag("Sortable.create('$element_id', " . _options_for_javascript($options) . ")");
}

function draggable_element($element_id, $options = array ()) {
	$obj =& get_instance();
	$obj->response->enqueue_js(array('prototype', 'builder', 'effects', 'dragdrop'));

	return javascript_tag("new Draggable('$element_id', " . _options_for_javascript($options) . ")");
}

function drop_receiving_element($element_id, $options = array ()) {
	$obj =& get_instance();
	$obj->response->enqueue_js(array('prototype', 'builder', 'effects', 'dragdrop'));

	if (!isset ($options['with'])) {
		$options['with'] = "'id=' + encodeURIComponent(element.id)";
	}
	if (!isset ($options['onDrop'])) {
		$options['onDrop'] = "function(element){" . remote_function($options) . "}";
	}

	foreach (get_ajax_options() as $key) {
		unset ($options[$key]);
	}

	if (isset ($options['accept'])) {
		$options['accept'] = _array_or_string_for_javascript($options['accept']);
	}

	if (isset ($options['hoverclass'])) {
		$options['hoverclass'] = "'{$options['hoverclass']}'";
	}

	return javascript_tag("Droppables.add('$element_id', " . _options_for_javascript($options) . ")");
}

function input_auto_complete_tag($name, $value, $url, $tag_options = array (), $completion_options = array ()) {
	$obj =& get_instance();
	$obj->response->enqueue_js(array('prototype', 'controls', 'effects'));

	$comp_options = _convert_options($completion_options);
	if (isset ($comp_options['use_style']) && $comp_options['use_style'] == 'true') {
		$response->addStylesheet('/sf/css/sf_helpers/input_auto_complete_tag');
	}

	$javascript = input_tag($name, $value, $tag_options);
	$javascript .= content_tag('div', '', array (
		'id' => "{$name}_auto_complete",
		'class' => 'auto_complete'
	));
	$javascript .= _auto_complete_field($name, $url, $comp_options);

	return $javascript;
}

function input_in_place_editor_tag($name, $url, $editor_options = array ()) {
	$obj =& get_instance();
	$obj->response->enqueue_js(array('prototype', 'controls', 'effects'));

	$editor_options = _convert_options($editor_options);
	$default_options = array (
		'tag' => 'span',
		'id' => '\'' . $name . '_in_place_editor',
		'class' => 'in_place_editor_field'
	);

	return _in_place_editor($name, $url, array_merge($default_options, $editor_options));
}

/*
 * Makes an HTML element specified by the DOM ID '$field_id' become an in-place
 * editor of a property.
 *
 * A form is automatically created and displayed when the user clicks the element,
 * something like this:
 * <form id="myElement-in-place-edit-form" target="specified url">
 *   <input name="value" text="The content of myElement"/>
 *   <input type="submit" value="ok"/>
 *   <a onclick="javascript to cancel the editing">cancel</a>
 * </form>
 *
 * The form is serialized and sent to the server using an AJAX call, the action on
 * the server should process the value and return the updated value in the body of
 * the reponse. The element will automatically be updated with the changed value
 * (as returned from the server).
 *
 * Required '$options' are:
 * 'url'                 Specifies the url where the updated value should
 *                       be sent after the user presses "ok".
 *
 * Addtional '$options' are:
 * 'rows'                Number of rows (more than 1 will use a TEXTAREA)
 * 'cancel_text'         The text on the cancel link. (default: "cancel")
 * 'save_text'           The text on the save link. (default: "ok")
 * 'external_control'    The id of an external control used to enter edit mode.
 * 'options'             Pass through options to the AJAX call (see prototype's Ajax.Updater)
 * 'with'                JavaScript snippet that should return what is to be sent
 *                       in the AJAX call, 'form' is an implicit parameter
 */
function _in_place_editor($field_id, $url, $options = array ()) {
	$javascript = "new Ajax.InPlaceEditor(";

	$javascript .= "'$field_id', ";
	$javascript .= "'" . system_url($url) . "'";

	$js_options = array ();

	if (isset ($options['tokens']))
		$js_options['tokens'] = _array_or_string_for_javascript($options['tokens']);

	if (isset ($options['cancel_text'])) {
		$js_options['cancelText'] = "'" . $options['cancel_text'] . "'";
	}
	if (isset ($options['save_text'])) {
		$js_options['okText'] = "'" . $options['save_text'] . "'";
	}
	if (isset ($options['cols'])) {
		$js_options['cols'] = $options['cols'];
	}
	if (isset ($options['rows'])) {
		$js_options['rows'] = $options['rows'];
	}
	if (isset ($options['external_control'])) {
		$js_options['externalControl'] = $options['external_control'];
	}
	if (isset ($options['options'])) {
		$js_options['ajaxOptions'] = $options['options'];
	}
	if (isset ($options['with'])) {
		$js_options['callback'] = "function(form) { return" . $options['with'] . "}";
	}
	if (isset ($options['highlightcolor'])) {
		$js_options['highlightcolor'] = "'" . $options['highlightcolor'] . "'";
	}
	if (isset ($options['highlightendcolor'])) {
		$js_options['highlightendcolor'] = "'" . $options['highlightendcolor'] . "'";
	}
	if (isset ($options['loadTextURL'])) {
		$js_options['loadTextURL'] = "'" . $options['loadTextURL'] . "'";
	}

	$javascript .= ', ' . _options_for_javascript($js_options);
	$javascript .= ');';

	return javascript_tag($javascript);
}

function _auto_complete_field($field_id, $url, $options = array ()) {
	$javascript = "new Ajax.Autocompleter(";

	$javascript .= "'$field_id', ";
	if (isset ($options['update'])) {
		$javascript .= "'" . $options['update'] . "', ";
	} else {
		$javascript .= "'{$field_id}_auto_complete', ";
	}

	$javascript .= "'" . system_url($url) . "'";

	$js_options = array ();
	if (isset ($options['tokens'])) {
		$js_options['tokens'] = _array_or_string_for_javascript($options['tokens']);
	}
	if (isset ($options['with'])) {
		$js_options['callback'] = "function(element, value) { return" . $options['with'] . "}";
	}
	if (isset ($options['indicator'])) {
		$js_options['indicator'] = "'" . $options['indicator'] . "'";
	}
	if (isset ($options['on_show'])) {
		$js_options['onShow'] = $options['on_show'];
	}
	if (isset ($options['on_hide'])) {
		$js_options['onHide'] = $options['on_hide'];
	}
	if (isset ($options['min_chars'])) {
		$js_options['min_chars'] = $options['min_chars'];
	}

	$javascript .= ', ' . _options_for_javascript($js_options) . ');';

	return javascript_tag($javascript);
}


?>