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

function is_html_escape($htmlEscape = null) {

	if($htmlEscape == null)
	{
		$val = AppContext::property('config.defaultHtmlEscape');
	}else {
		$val = $htmlEscape;
	}
	if(!is_null($val) && is_bool($val)) return $val;
	return true;

}

function is_xss_clean($xssClean = null) {


	if($xssClean == null)
	{
		$val = AppContext::property('config.defaultXssClean');
	}else {
		$val = $xssClean;
	}
	if(!is_null($val) && is_bool($val)) return $val;
	return false;

}


function clean_input_data($str)
{
	if (is_array($str))
	{
		$new_array = array();
		foreach ($str as $key => $val)
		{
			$new_array[clean_input_keys($key)] = clean_input_data($val);
		}
		return $new_array;
	}

	// Standardize newlines
	return preg_replace("/\015\012|\015|\012/", "\n", $str);
}

// --------------------------------------------------------------------

/**
 * Clean Keys
 *
 * This is a helper function. To prevent malicious users
 * from trying to exploit keys we make sure that keys are
 * only named with alpha-numeric text and a few other items.
 *
 * @access	private
 * @param	string
 * @return	string
 */
function clean_input_keys($str)
{
	 if ( ! preg_match("/^[a-z0-9:_\/-]+$/i", $str))
	 {
		exit("Disallowed Key Characters: $str");
	 }

	if ( ! get_magic_quotes_gpc())
	{
	   return addslashes($str);
	}

	return $str;
}

function utf8_uri_encode($utf8_string) {
	$unicode = '';
	$values = array ();
	$num_octets = 1;

	for ($i = 0; $i < strlen($utf8_string); $i++) {

		$value = ord($utf8_string[$i]);

		if ($value < 128) {
			$unicode .= chr($value);
		} else {
			if (count($values) == 0)
				$num_octets = ($value < 224) ? 2 : 3;

			$values[] = $value;

			if (count($values) == $num_octets) {
				if ($num_octets == 3) {
					$unicode .= '%' . dechex($values[0]) . '%' . dechex($values[1]) . '%' . dechex($values[2]);
				} else {
					$unicode .= '%' . dechex($values[0]) . '%' . dechex($values[1]);
				}

				$values = array ();
				$num_octets = 1;
			}
		}
	}

	return $unicode;
}

function xss_clean($str, $charset = 'ISO-8859-1')
{
	/*
	 * Remove Null Characters
	 *
	 * This prevents sandwiching null characters
	 * between ascii characters, like Java\0script.
	 *
	 */
	$str = preg_replace('/\0+/', '', $str);
	$str = preg_replace('/(\\\\0)+/', '', $str);

	/*
	 * Validate standard character entities
	 *
	 * Add a semicolon if missing.  We do this to enable
	 * the conversion of entities to ASCII later.
	 *
	 */
	$str = preg_replace('#(&\#*\w+)[\x00-\x20]+;#u',"\\1;",$str);

	/*
	 * Validate UTF16 two byte encoding (x00)
	 *
	 * Just as above, adds a semicolon if missing.
	 *
	 */
	$str = preg_replace('#(&\#x*)([0-9A-F]+);*#iu',"\\1\\2;",$str);

	/*
	 * URL Decode
	 *
	 * Just in case stuff like this is submitted:
	 *
	 * <a href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">Google</a>
	 *
	 * Note: Normally urldecode() would be easier but it removes plus signs
	 *
	 */
	$str = preg_replace("/%u0([a-z0-9]{3})/i", "&#x\\1;", $str);
	$str = preg_replace("/%([a-z0-9]{2})/i", "&#x\\1;", $str);

	/*
	 * Convert character entities to ASCII
	 *
	 * This permits our tests below to work reliably.
	 * We only convert entities that are within tags since
	 * these are the ones that will pose security problems.
	 *
	 */
	if (preg_match_all("/<(.+?)>/si", $str, $matches))
	{
		for ($i = 0; $i < count($matches['0']); $i++)
		{
			$str = str_replace($matches['1'][$i],
								html_entity_decode2($matches['1'][$i], $charset),
								$str);
		}
	}

	/*
	 * Not Allowed Under Any Conditions
	 */
	$bad = array(
					'document.cookie'	=> '[removed]',
					'document.write'	=> '[removed]',
					'window.location'	=> '[removed]',
					"javascript\s*:"	=> '[removed]',
					"Redirect\s+302"	=> '[removed]',
					'<!--'				=> '&lt;!--',
					'-->'				=> '--&gt;'
				);

	foreach ($bad as $key => $val)
	{
		$str = preg_replace("#".$key."#i", $val, $str);
	}

	/*
	 * Convert all tabs to spaces
	 *
	 * This prevents strings like this: ja	vascript
	 * Note: we deal with spaces between characters later.
	 *
	 */
	$str = preg_replace("#\t+#", " ", $str);

	/*
	 * Makes PHP tags safe
	 *
	 *  Note: XML tags are inadvertently replaced too:
	 *
	 *	<?xml
	 *
	 * But it doesn't seem to pose a problem.
	 *
	 */
	$str = str_replace(array('<?php', '<?PHP', '<?', '?>'),  array('&lt;?php', '&lt;?PHP', '&lt;?', '?&gt;'), $str);

	/*
	 * Compact any exploded words
	 *
	 * This corrects words like:  j a v a s c r i p t
	 * These words are compacted back to their correct state.
	 *
	 */
	$words = array('javascript', 'vbscript', 'script', 'applet', 'alert', 'document', 'write', 'cookie', 'window');
	foreach ($words as $word)
	{
		$temp = '';
		for ($i = 0; $i < strlen($word); $i++)
		{
			$temp .= substr($word, $i, 1)."\s*";
		}

		$temp = substr($temp, 0, -3);
		$str = preg_replace('#'.$temp.'#s', $word, $str);
		$str = preg_replace('#'.ucfirst($temp).'#s', ucfirst($word), $str);
	}

	/*
	 * Remove disallowed Javascript in links or img tags
	 */
	 $str = preg_replace("#<a.+?href=.*?(alert\(|alert&\#40;|javascript\:|window\.|document\.|\.cookie|<script|<xss).*?\>.*?</a>#si", "", $str);
	 $str = preg_replace("#<img.+?src=.*?(alert\(|alert&\#40;|javascript\:|window\.|document\.|\.cookie|<script|<xss).*?\>#si", "", $str);
	 $str = preg_replace("#<(script|xss).*?\>#si", "", $str);

	/*
	 * Remove JavaScript Event Handlers
	 *
	 * Note: This code is a little blunt.  It removes
	 * the event handler and anything up to the closing >,
	 * but it's unlikely to be a problem.
	 *
	 */
	 $str = preg_replace('#(<[^>]+.*?)(onblur|onchange|onclick|onfocus|onload|onmouseover|onmouseup|onmousedown|onselect|onsubmit|onunload|onkeypress|onkeydown|onkeyup|onresize)[^>]*>#iU',"\\1>",$str);

	/*
	 * Sanitize naughty HTML elements
	 *
	 * If a tag containing any of the words in the list
	 * below is found, the tag gets converted to entities.
	 *
	 * So this: <blink>
	 * Becomes: &lt;blink&gt;
	 *
	 */
	$str = preg_replace('#<(/*\s*)(alert|applet|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|layer|link|meta|object|plaintext|style|script|textarea|title|xml|xss)([^>]*)>#is', "&lt;\\1\\2\\3&gt;", $str);

	/*
	 * Sanitize naughty scripting elements
	 *
	 * Similar to above, only instead of looking for
	 * tags it looks for PHP and JavaScript commands
	 * that are disallowed.  Rather than removing the
	 * code, it simply converts the parenthesis to entities
	 * rendering the code un-executable.
	 *
	 * For example:	eval('some code')
	 * Becomes:		eval&#40;'some code'&#41;
	 *
	 */
	$str = preg_replace('#(alert|cmd|passthru|eval|exec|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2&#40;\\3&#41;", $str);

	/*
	 * Final clean up
	 *
	 * This adds a bit of extra precaution in case
	 * something got through the above filters
	 *
	 */
	$bad = array(
					'document.cookie'	=> '[removed]',
					'document.write'	=> '[removed]',
					'window.location'	=> '[removed]',
					"javascript\s*:"	=> '[removed]',
					"Redirect\s+302"	=> '[removed]',
					'<!--'				=> '&lt;!--',
					'-->'				=> '--&gt;'
				);

	foreach ($bad as $key => $val)
	{
		$str = preg_replace("#".$key."#i", $val, $str);
	}

	return $str;
}

function stripslashes_deep($value) {
	$value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
	return $value;
}


function specialchars($text, $quotes = 0) {
	// Like htmlspecialchars except don't double-encode HTML entities
	$text = preg_replace('/&([^#])(?![a-z1-4]{1,8};)/', '&#038;$1', $text);
	$text = str_replace('<', '&lt;', $text);
	$text = str_replace('>', '&gt;', $text);
	if ('double' === $quotes) {
		$text = str_replace('"', '&quot;', $text);
	}
	elseif ('single' === $quotes) {
		$text = str_replace("'", '&#039;', $text);
	}
	elseif ($quotes) {
		$text = str_replace('"', '&quot;', $text);
		$text = str_replace("'", '&#039;', $text);
	}
	return $text;
}

?>
