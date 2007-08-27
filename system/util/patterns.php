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

function simple_pattern_match($patterns, $str) {
	if(!is_array($patterns)) $patterns = array($patterns);

	foreach($patterns as $pattern) {

		if ($pattern == $str || "*" == $pattern) {
			return true;
		}
		if ($pattern == null || $str == null) {
			return false;
		}
		if (str_starts_with($pattern,"*") && str_starts_with($pattern,"*") &&
				strpos($str,(substr($pattern, 1, strlen($pattern - 1)))) != -1) {
			return true;
		}
		if (str_starts_with($pattern,"*") && str_ends_with($str,substr($pattern,1, strlen($pattern)))) {
			return true;
		}
		if (str_ends_with($pattern,"*") && str_starts_with($str,substr($pattern,0, strlen($pattern - 1)))) {
			return true;
		}

	}

	return false;

}
?>
