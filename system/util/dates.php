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

function rfc_date($timestamp, $type = 'rfc1123') {
	$type = strtolower($type);

	if ($type == 'rfc1123') {
		return substr(gmdate('r', $timestamp), 0, -5) . 'GMT';
	} else
		if ($type == 'rfc1036') {
			return gmdate('l, d-M-y H:i:s ', $timestamp) . 'GMT';
		} else
			if ($type == 'asctime') {
				return gmdate('D M j H:i:s', $timestamp);
			} else {
				$error = 'The second get_date() method parameter must be one of: rfc1123, rfc1036 or asctime';
				show_error('rfc_date', $error);
			}
}

function date_i18n($dateformatstring, $unixtimestamp) {
	$i = $unixtimestamp;
	$month = get_month_names('long');
	$month_abbrev = get_month_names('short');
	$weekday = get_day_names('long');
	$weekday_abbrev = get_day_names('short');
	if ( (!empty($month)) && (!empty($weekday)) ) {
		$datemonth = $month[intVal(date('m', $i))];
		$datemonth_abbrev = $month_abbrev[intVal(date('m', $i))];
		$dateweekday = $weekday[intVal(date('w', $i))];
		$dateweekday_abbrev = $weekday_abbrev[intVal(date('m', $i))];
		$dateformatstring = ' '.$dateformatstring;
		$dateformatstring = preg_replace("/([^\\\])D/", "\\1".backslashit($dateweekday_abbrev), $dateformatstring);
		$dateformatstring = preg_replace("/([^\\\])F/", "\\1".backslashit($datemonth), $dateformatstring);
		$dateformatstring = preg_replace("/([^\\\])l/", "\\1".backslashit($dateweekday), $dateformatstring);
		$dateformatstring = preg_replace("/([^\\\])M/", "\\1".backslashit($datemonth_abbrev), $dateformatstring);
		$dateformatstring = substr($dateformatstring, 1, strlen($dateformatstring)-1);
	}
	$j = @date($dateformatstring, $i);
	return $j;
}


function human_time_diff( $from, $to = '' ) {
	if ( empty($to) )
		$to = time();
	$diff = (int) abs($to - $from);
	if ($diff <= 3600) {
		$mins = round($diff / 60);
		if ($mins <= 1)
			$since = __('1 min');
		else
			$since = sprintf( __('%s mins'), $mins);
	} else if (($diff <= 86400) && ($diff > 3600)) {
		$hours = round($diff / 3600);
		if ($hours <= 1)
			$since = __('1 hour');
		else
			$since = sprintf( __('%s hours'), $hours );
	} elseif ($diff >= 86400) {
		$days = round($diff / 86400);
		if ($days <= 1)
			$since = __('1 day');
		else
			$since = sprintf( __('%s days'), $days );
	}
	return $since;
}

// computes an offset in seconds from an iso8601 timezone
function iso8601_timezone_to_offset($timezone) {
  // $timezone is either 'Z' or '[+|-]hhmm'
  if ($timezone == 'Z') {
    $offset = 0;
  } else {
    $sign    = (substr($timezone, 0, 1) == '+') ? 1 : -1;
    $hours   = intval(substr($timezone, 1, 2));
    $minutes = intval(substr($timezone, 3, 4)) / 60;
    $offset  = $sign * 3600 * ($hours + $minutes);
  }
  return $offset;
}

?>
