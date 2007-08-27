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

function str_ends_with($haystack, $needle) {
	$hl = strlen($haystack);
	$nl = strlen($needle);
	if(substr($haystack, $hl-$nl, $hl) == $needle)
		return true;

	return false;
}

function str_starts_with($haystack, $needle) {
	return strpos($haystack, $needle) === 0;

}

function str_char_at($string, $index) {
	return substr($string, $index, 1);
}

function substr2($string, $startIndex, $endIndex) {
	$temp = substr($string, 0, $endIndex);
	return substr($temp, $startIndex);
}

function str_equals_icase($string1, $string2) {
	if($string1 == null && $string2 == null) return true;
	if($string1 == null || $string2 == null) return false;
	return strtolower(trim($string1)) == strtolower(trim($string2));
}

function str_is_bool($text) {
	return str_bool($text) !== null;
}

function str_bool($text) {
	if($text == null) return null;

	if(	str_equals_icase($text,'true') || str_equals_icase($text, 'on') ||
				str_equals_icase($text, 'yes') || str_equals_icase($text, '1'))
		return true;
	else if (str_equals_icase($text,'false') || str_equals_icase($text, 'off') ||
				str_equals_icase($text, 'no') || str_equals_icase($text, '0'))
		return false;

	return null;
}

function strip_filename_extension($path) {
	if ($path == null) {
		return null;
	}
	$sepIndex = strrpos($path,EXTENSION_SEPARATOR);
	return $sepIndex !== FALSE ? substr($path,0, $sepIndex) : $path;
}

function backslashit($string) {
	$string = preg_replace('/^([0-9])/', '\\\\\\\\\1', $string);
	$string = preg_replace('/([a-z])/i', '\\\\\1', $string);
	return $string;
}

function excerpt($excerpt, $word_length) {

	$new = ltrim(strip_tags($excerpt));
	$words = explode(" ", $new);
	$new = join(" ", array_slice($words, 0, $word_length));
	if (count($words) > $word_length	) $new .= "...";

	return $new;
}


function readmore($content, $word_length, $link = null, $readmore_text = 'read more') 
{
	$new = ltrim(strip_tags($content));
	$words = explode(" ", $new);
	$new = join(" ", array_slice($words, 0, $word_length));
	if (count($words) > $word_length	) {
		$new .= "...";
		if($link != null) {
			$new .= ' <a href="'.$link.'">'.$readmore_text.'</a>';
		}
	}

	return $new;
}

function underscore($word)
{
	$word = strtolower($word);	
	return preg_replace('/[^A-Z^a-z^0-9^\/]+/','_',
	preg_replace('/([a-z\d])([A-Z])/','\1_\2',
	preg_replace('/([A-Z]+)([A-Z][a-z])/','\1_\2',
	preg_replace('/&.+?;/', '',
	preg_replace('/[^%a-z0-9 _-]/', '', 
	preg_replace('/\s+/', '-', 
	preg_replace('|-+|', '-',
	preg_replace('/::/', '/',$word))))))));
}

function dasherize($word)
{
	return  str_replace('_', '-', underscore($word));
}


function pluralize($word)
{
	$plural = array(
	'/(quiz)$/i' => '\1zes',
	'/^(ox)$/i' => '\1en',
	'/([m|l])ouse$/i' => '\1ice',
	'/(matr|vert|ind)ix|ex$/i' => '\1ices',
	'/(x|ch|ss|sh)$/i' => '\1es',
	'/([^aeiouy]|qu)ies$/i' => '\1y',
	'/([^aeiouy]|qu)y$/i' => '\1ies',
	'/(hive)$/i' => '\1s',
	'/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
	'/sis$/i' => 'ses',
	'/([ti])um$/i' => '\1a',
	'/(buffal|tomat)o$/i' => '\1oes',
	'/(bu)s$/i' => '\1ses',
	'/(alias|status)/i'=> '\1es',
	'/(octop|vir)us$/i'=> '\1i',
	'/(ax|test)is$/i'=> '\1es',
	'/s$/i'=> 's',
	'/$/'=> 's');

	$uncountable = array('equipment', 'information', 'rice', 'money', 'species', 'series', 'fish', 'sheep');

	$irregular = array(
	'person' => 'people',
	'man' => 'men',
	'child' => 'children',
	'sex' => 'sexes',
	'move' => 'moves');

	$lowercased_word = strtolower($word);

	foreach ($uncountable as $_uncountable){
		if(substr($lowercased_word,(-1*strlen($_uncountable))) == $_uncountable){
			return $word;
		}
	}

	foreach ($irregular as $_plural=> $_singular){
		if (preg_match('/('.$_plural.')$/i', $word, $arr)) {
			return preg_replace('/('.$_plural.')$/i', substr($arr[0],0,1).substr($_singular,1), $word);
		}
	}

	foreach ($plural as $rule => $replacement) {
		if (preg_match($rule, $word)) {
			return preg_replace($rule, $replacement, $word);
		}
	}
	return false;

}

function singularize($word)
{
	$singular = array (
	'/(quiz)zes$/i' => '\\1',
	'/(matr)ices$/i' => '\\1ix',
	'/(vert|ind)ices$/i' => '\\1ex',
	'/^(ox)en/i' => '\\1',
	'/(alias|status)es$/i' => '\\1',
	'/([octop|vir])i$/i' => '\\1us',
	'/(cris|ax|test)es$/i' => '\\1is',
	'/(shoe)s$/i' => '\\1',
	'/(o)es$/i' => '\\1',
	'/(bus)es$/i' => '\\1',
	'/([m|l])ice$/i' => '\\1ouse',
	'/(x|ch|ss|sh)es$/i' => '\\1',
	'/(m)ovies$/i' => '\\1ovie',
	'/(s)eries$/i' => '\\1eries',
	'/([^aeiouy]|qu)ies$/i' => '\\1y',
	'/([lr])ves$/i' => '\\1f',
	'/(tive)s$/i' => '\\1',
	'/(hive)s$/i' => '\\1',
	'/([^f])ves$/i' => '\\1fe',
	'/(^analy)ses$/i' => '\\1sis',
	'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\\1\\2sis',
	'/([ti])a$/i' => '\\1um',
	'/(n)ews$/i' => '\\1ews',
	'/s$/i' => '',
	);

	$uncountable = array('equipment', 'information', 'rice', 'money', 'species', 'series', 'fish', 'sheep','sms');

	$irregular = array(
	'person' => 'people',
	'man' => 'men',
	'child' => 'children',
	'sex' => 'sexes',
	'move' => 'moves');

	$lowercased_word = strtolower($word);
	foreach ($uncountable as $_uncountable){
		if(substr($lowercased_word,(-1*strlen($_uncountable))) == $_uncountable){
			return $word;
		}
	}

	foreach ($irregular as $_singular => $_plural){
		if (preg_match('/('.$_plural.')$/i', $word, $arr)) {
			return preg_replace('/('.$_plural.')$/i', substr($arr[0],0,1).substr($_singular,1), $word);
		}
	}

	foreach ($singular as $rule => $replacement) {
		if (preg_match($rule, $word)) {
			return preg_replace($rule, $replacement, $word);
		}
	}

	return $word;
}

?>
