<?php


class PaginationEditor extends PropertyEditor {

	function setAsText($text) {
		list($page, $itemsPerPage) = explode(',', $text);
		$this->setValue(new Pagination($page, $itemsPerPage));
	}

	function getAsText() {
		$val = $this->getValue();
		return $val->page.','.$val->itemsPerPage;
	}
	
	function parseFromParameter($name = PAGINATION_NVP, $defaultPage = '1', $defaultItemsPerPage = '10') {
		global $request;
		
		$page = $request->getParameter($name);
		$pagination = null;
		if(!is_null($page)){
			$editor = new PaginationEditor();
			$editor->setAsText($page);
			
			$pagination = $editor->getValue();
		}else{
			$pagination = new Pagination($defaultPage, $defaultItemsPerPage);
		}
		return $pagination;
	}
	
}
?>