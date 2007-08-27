<?php

class CreatePostController extends SimpleFormController {

 	var $postDAO;
	
	function doSubmitAction(&$post) {
		$this->postDAO->insertPost($post);
	}

 }?>