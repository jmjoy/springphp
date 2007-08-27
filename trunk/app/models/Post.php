<?php

class Post {

	var $id;
	var $title;
	var $content;
	var $creationDate;
		
	function Post() {}
	
	function &getId() { return $this->id;}
	function &getTitle() { return $this->title;}
	function &getContent() { return $this->content;}
	function &getCreationDate() { return $this->creationDate;}

}


class PostClassInfo {
	function &getPropertyDescriptors() {
		$pds = array(
			new PropertyDescriptor('id', 			'int'),
			new PropertyDescriptor('title',			'string'),
			new PropertyDescriptor('content', 		'string'),
			new PropertyDescriptor('creationDate', 	'timestamp'),
		);
		return $pds;
	}

}

?>