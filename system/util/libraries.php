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


function autodiscover_plugins() {
	//discover plugins
	$plugins =& AppContext::service('PluginManager');
	if($plugins != null) $plugins->autodiscover();
}


function &load_theme($name) {

	$tm =& AppContext::service('ThemeManager');
	if($tm != null)
		return $tm->loadTheme($name);
	else
		return _null();

}



function bench_mark($name, $time = 0) {
	$benchmarker =& AppContext::service('Benchmark');
	if($benchmarker != null)
		$benchmarker->mark($name, $time);
}

function bench_elapsed($point1 = '', $point2 = '', $decimals = 4) {
	$benchmarker =& AppContext::service('Benchmark');
	if($benchmarker != null)
		return $benchmarker->elapsed($point1, $point2, $decimals);
}



function i18n_add_text_domain($domain = '', $path = '') {
	$i18n =& AppContext::service('I18n');
	if($i18n != null) $i18n->addTextDomain($domain, $path);
}

function i18n_load_text_domains(&$locale) {
	$i18n =& AppContext::service('I18n');
	if($i18n != null) $i18n->loadTextDomains($locale);
}

function __($text, $domain = '') {
	$i18n =& AppContext::service('I18n');
	if($i18n != null) return $i18n->translate($text, $domain);
	else return $text;
}

function _e($text, $domain = '') {
	echo __($text, $domain);
}



function log_message($level = LOG_ERR, $message)
{
	$log =& AppContext::service('Log');
	if($log != null)
		$log->message($level, $message);
}

function log_enabled($level) {
	$log =& AppContext::service('Log');
	if($log != null)
		return $log->enabled($level);
	else return false;
}

 ?>
