<?php


class SortEditor extends PropertyEditor {

	function setAsText($text) {
		list($column, $direction) = explode(',', $text);
		$this->setValue(new Sort($column, $direction));
	}

	function getAsText() {
		$val = $this->getValue();
		return $val->column.','.$val->direction;
	}
	
	
	function parseFromParameter($name = SORTBY_NVP, $defaultColumn = '', $defaultDirection = 'ASC') {
		global $request;
		
		$sortParam = $request->getParameter($name);
		$sort = null;
		if(!is_null($sortParam)){
			$editor = new SortEditor();
			$editor->setAsText($sortParam);
			
			$sort = $editor->getValue();
		}else{
			$sort = new Sort($defaultColumn, $defaultDirection);
		}
		return $sort;
	}
	
	
}
?>