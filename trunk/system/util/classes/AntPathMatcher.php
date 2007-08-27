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

class AntPathMatcher {

	function AntPathMatcher() {}

	var $pathSeparator = DEFAULT_PATH_SEPARATOR;

	function isPattern($str) {
		return (strpos($str, '*') != -1 || strpos($str, '?')!= -1);
	}

	function match($pattern, $str) {
		if (str_starts_with($str, $this->pathSeparator) != str_starts_with($pattern, $this->pathSeparator)) {
			return false;
		}

		// quick equals match
		if($pattern == $str) return true;

		$sep = $this->pathSeparator;
		// if separator needs to be escaped... escape it
		if(preg_match('[\{\}\[\]\(\)\^\$\.\|\*\+\?]', $sep)) $sep = "\\$sep";

		// find one character not the separator
		$pattern = str_replace('?', "[^$sep.]", $pattern);

		// find any number of characters or empty string (disregard separators)
		$pattern = str_replace('**', '(.+|)', $pattern);

		// find any number of characters up to the separator (last separator is optional) or empty string
		$pattern = str_replace('*', "(([^$sep.]+|)$sep?|)", $pattern);

		return (preg_match('#^'.$pattern.'$#', $str) > 0);

	}

	/**
	 * Given a pattern and a full path, returns the non-pattern mapped part. E.g.:
	 * <ul>
	 * <li>'<code>/docs/*</code>' and '<code>/docs/cvs/commit</code> -> '<code>cvs/commit</code>'</li>
	 * <li>'<code>/docs/cvs/*.html</code>' and '<code>/docs/cvs/commit.html</code> -> '<code>commit.html</code>'</li>
	 * <li>'<code>/docs/**</code>' and '<code>/docs/cvs/commit</code> -> '<code>cvs/commit</code>'</li>
	 * <li>'<code>/docs/**\/*.html</code>' and '<code>/docs/cvs/commit</code> -> '<code>cvs/commit.html</code>'</li>
	 * </ul>
	 * <p>Assumes that {@link #match} returns <code>true</code> for '<code>pattern</code>'
	 * and '<code>path</code>', but does <strong>not</strong> enforce this.
	 */
	function extractPathWithinPattern($pattern, $path) {
		$patternParts = explode($this->pathSeparator,$pattern);
		$pathParts = explode($this->pathSeparator,$path);

		$buffer = '';

		// Add any path parts that have a wildcarded pattern part.
		$puts = 0;
		for ($i = 0; $i < count($patternParts); $i++) {
			$patternPart = $patternParts[$i];
			if ((strpos($patternPart,'*') !== FALSE || strpos($patternPart,'?') !== FALSE) && count($pathParts) >= $i + 1) {
				if ($puts > 0) {
					$buffer .= $this->pathSeparator;
				}
				$buffer .= $pathParts[$i];
				$puts++;
			}
		}

		// Append any trailing path parts.
		for ($i = count($patternParts); $i < count($pathParts); $i++) {
			if ($puts > 0 || $i > 0) {
				$buffer .= $this->pathSeparator;
			}
			$buffer .= $pathParts[$i];
		}

		return $buffer;
	}
}
?>