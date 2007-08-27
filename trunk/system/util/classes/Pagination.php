<?php

class Pagination {

	var $page;
	var $itemsPerPage;
	var $totalItems;
	
	function Pagination($page = '', $itemsPerPage = '', $totalItems = 0) {
		$this->page = $page;
		$this->itemsPerPage = $itemsPerPage;
		$this->totalItems = $totalItems;
	}
	
	function &getPage() {
		return $this->page;
	}
	
	function &getItemsPerPage() {
		return $this->itemsPerPage;
	}
	
	function &getTotalItems() {
		return $this->totalItems;
	}

}

?>