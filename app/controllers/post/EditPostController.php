<?php

 class EditPostController extends SimpleFormController {

 	var $postDAO;

 	function &formBackingObject(&$request) {
 		$post_id = RequestUtils::getPathWithinHandlerMapping();
 		if(is_null($post_id) || $post_id == '')
 			show_error('EditPost', 'No post id specified.');
 		$post = $this->postDAO->getPostById($post_id); 		
 		if(is_null($post))
 			show_error('EditPost', 'Post not found.');
		return $post;
	}
	
	function doSubmitAction(&$post) {
		$this->postDAO->updatePost($post);
	}

 }
 ?>
