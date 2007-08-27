<?php

class Sort {

	var $column;
	var $direction;
	
	function Sort($column = '', $direction = '') {
		$this->column = $column;
		$this->direction = $direction;
	}
	
	function &getColumn() {
		return $this->column;
	}
	
	function &getDirection() {
		return $this->direction;
	}

}

?>