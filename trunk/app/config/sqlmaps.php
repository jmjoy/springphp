<?php
// this file is used by the SQLMapper
// it maps primitive datatypes from DB columns to Object properties

$properties['sqlmaps'] = array(
		'Post' => array(
			// db column => object property
		
			'post_id' => 'id',
			'post_title' => 'title',
			'post_content' => 'content',
			'post_creationdate' => 'creationDate',
		)	
	);
?>