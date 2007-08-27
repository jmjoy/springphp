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
 * UploadedFile
 *
 * @package		Redstart
 * @subpackage  HTTP
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
class UploadedFile {

	var $name;
	var $size;
	var $tempName;
	var $mimetype;
	var $error;

	function UploadedFile($name, $tempName, $size, $mimetype, $error) {

		$this->name = $name;
		$this->tempName = $tempName;
		$this->size = $size;
		$this->mimetype = $mimetype;
		$this->error = $error;

	}

	function getName() {
		return $this->name;
	}

	function getTemporaryName() {
		return $this->tempName;
	}

	function getSize() {
		return $this->size;
	}

	function getMimetype() {
		return $this->mimetype;
	}

	function getError() {
		return $this->error;
	}

	function isEmpty() {
		return !($this->error === UPLOAD_ERR_OK && $this->size > 0);
	}

	function transferTo($filename) {
		if( !$this->isEmpty() && is_uploaded_file($this->tempName)) {
			if ( @copy($this->tempName, $filename)) return true;
			if ( @move_uploaded_file($this->tempName, $filename)) return true;
		}
		return FALSE;
	}

}
?>
