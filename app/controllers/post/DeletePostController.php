<?php

class DeletePostController extends Controller {

	var $postDAO;
	var $successView;

	function &handleRequestInternal(&$request, &$response) {
	
		$post_id = RequestUtils::getPathWithinHandlerMapping();
				
 		if(is_null($post_id) || $post_id == '')
 			show_error('DeletePost', 'No post id specified.');
 		$post = new Post();
 		$post->id = $post_id;
 		
 		$this->postDAO->deletePost($post);
 		
 		$mv = new ModelAndView($this->successView);
 		return $mv;
	}


}

?>