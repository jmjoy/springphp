<?php

class IllegalArgumentException {

	var $message;

	function IllegalArgumentException($message) {
		$this->message = $message;
	}

	function getMessage() {
		return $this->message;
	}
}

?>