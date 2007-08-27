<?php

 class HomeController extends ParameterizableViewController{

	var $postDAO;
	
	function &handleRequestInternal(&$request, &$response) {
	
		$model['posts'] = $this->postDAO->getAllPosts();

 		$mv = parent::handleRequestInternal($request, $response);
 		$mv->model = ($model);
		return $mv;
	}
 }
 
 ?>