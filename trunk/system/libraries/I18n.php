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
 * I18n
 *
 * @package		Redstart
 * @subpackage  Libraries
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class I18n {

	var $textDomains = array();
	var $textReaders = array();
	var $defaultPath = '';
	var $defaultDomain = '';


	function I18n() {
		include (BASEPATH.'vendors/gettext/streams.php');
		include (BASEPATH.'vendors/gettext/gettext.php');

		$this->defaultPath = APPPATH . 'languages/';
		$this->defaultDomain = 'default';
	}

	function addTextDomain($domain = '', $path = '') {
		if(empty($path)) $path = $this->defaultPath;
		if(empty($domain)) $domain = $this->defaultDomain;
		$this->textDomains[$domain] = $path;
	}

	function _loadTextReader($domain, $mofile) {
		if (isset ($this->textReaders[$domain]))
			return;

		if (is_readable($mofile))
			$input = new CachedFileReader($mofile);
		else
			return;

		$this->textReaders[$domain] = new gettext_reader($input);
	}

	function loadTextDomains(&$locale) {
		assert_not_null($locale, 'cannot load text domains: locale is null');
		$variant = $locale->variant;
		foreach($this->textDomains as $domain => $path) {
			$mofile ="$path/$domain-$variant.mo";
			$this->_loadTextReader($domain, $mofile);
		}
	}

	function translate($text, $domain = '') {
		if(empty($domain)) $domain = $this->defaultDomain;

		if (isset($this->textReaders[$domain]))
			return $this->textReaders[$domain]->translate($text);
		else
			return $text;

	}

}



?>
