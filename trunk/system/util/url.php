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

function system_url($uri = '')
{
	global $request;

	if ($uri == '') return $request->getSystemURL();

	if(strpos($uri, '://') !== FALSE) return $uri;

	$sysUrl = $request->getSystemURL();

	// add leading slash to uri
	if(substr($uri, 0, 1) !== '/') $uri = '/'.$uri;

	// drop trailing slash from system
	if(substr($sysUrl, -1, 1) == '/') $sysUrl = substr($sysUrl, 0, -1);

	return $sysUrl.$uri;
}

function base_url() {
	global $request;
	return $request->getFullContextRoot();
}

function trailslash($item = '') {
	if (ereg("/$", $item) === FALSE)
	{
		$item .= '/';
	}
	return $item;
}


/*
add_query_arg: Returns a modified querystring by adding
a single key & value or an associative array.
Setting a key value to emptystring removes the key.
Omitting oldquery_or_uri uses the $_SERVER value.

Parameters:
add_query_arg(newkey, newvalue, oldquery_or_uri) or
add_query_arg(associative_array, oldquery_or_uri)
*/
function add_query_arg() {
	$ret = '';
	if ( is_array(func_get_arg(0)) ) {
		if ( @func_num_args() < 2 || '' == @func_get_arg(1) )
			$uri = $_SERVER['REQUEST_URI'];
		else
			$uri = @func_get_arg(1);
	} else {
		if ( @func_num_args() < 3 || '' == @func_get_arg(2) )
			$uri = $_SERVER['REQUEST_URI'];
		else
			$uri = @func_get_arg(2);
	}

	if ( preg_match('|^https?://|i', $uri, $matches) ) {
		$protocol = $matches[0];
		$uri = substr($uri, strlen($protocol));
	} else {
		$protocol = '';
	}

	if ( strstr($uri, '?') ) {
		$parts = explode('?', $uri, 2);
		if ( 1 == count($parts) ) {
			$base = '?';
			$query = $parts[0];
		} else {
			$base = $parts[0] . '?';
			$query = $parts[1];
		}
	} else if ( !empty($protocol) || strstr($uri, '/') ) {
		$base = $uri . '?';
		$query = '';
	} else {
		$base = '';
		$query = $uri;
	}
	$qs = null;
	parse_str($query, $qs);
	if ( is_array(func_get_arg(0)) ) {
		$kayvees = func_get_arg(0);
		$qs = array_merge($qs, $kayvees);
	} else {
		$qs[func_get_arg(0)] = func_get_arg(1);
	}

	foreach($qs as $k => $v) {
		if ( $v != '' ) {
			if ( $ret != '' )
				$ret .= '&';
			$ret .= "$k=$v";
		}
	}
	$ret = $protocol . $base . $ret;
	if ( get_magic_quotes_gpc() )
		$ret = stripslashes($ret); // parse_str() adds slashes if magicquotes is on.  See: http://php.net/parse_str
	return trim($ret, '?');
}

/*
remove_query_arg: Returns a modified querystring by removing
a single key or an array of keys.
Omitting oldquery_or_uri uses the $_SERVER value.

Parameters:
remove_query_arg(removekey, [oldquery_or_uri]) or
remove_query_arg(removekeyarray, [oldquery_or_uri])
*/

function remove_query_arg($key, $query='') {
	if ( is_array($key) ) { // removing multiple keys
		foreach ( (array) $key as $k )
			$query = add_query_arg($k, '', $query);
		return $query;
	}
	return add_query_arg($key, '', $query);
}

function determine_domain($url) {
	$url2 = parse_url($url);

	$host = $url2['host'];

	$host = str_replace(".","/",$host);

	$domain = dirname($host).'.'.basename($host);
	$domain = basename($domain);

	return $domain;
}


?>
