<?php

class PostDAO {

 	var $datasource;
 	var $sqlMapper;
 	
 	var $tableName = 'post';
 	var $objectName = 'Post';
	 	
	function insertPost(&$post) {

		$values = $this->sqlMapper->mapObjectToArray($post, $this->objectName);

		$values['post_creationdate'] = $this->datasource->db->formatTimestamp(time());
		
		$this->datasource->db->set($values);
		$this->datasource->db->insert($this->tableName);

		$post_id = $this->datasource->db->insert_id();

		return $post_id;
	}

	function deletePost(&$post) {
		assert_not_null($post->id, 'PostDAO::deletePost: post->id cannot be null');
		$this->datasource->db->delete($this->tableName, array('post_id'=>$post->id));
	}

	function updatePost(&$post) {
		$values = $this->sqlMapper->mapObjectToArray($post, $this->objectName);
		$post_id = $values['post_id'];

		$this->datasource->db->where('post_id',$post_id);
		$this->datasource->db->set($values);
		$this->datasource->db->update($this->tableName);
	}
	
	function &getPostById($post_id) {
	
		$this->datasource->db->select('*');
		$this->datasource->db->from($this->tableName);
		$this->datasource->db->where(array('post_id'=>$post_id));
		$query = $this->datasource->db->get();

		$post = null;
		if($query->num_rows() > 0) {
			$row = $query->first_row('array');
			$post = $this->sqlMapper->mapArrayToObject($row, $this->objectName);
		}
				
		return $post;	
	}
	
	function getAllPosts($type = null) {
		$this->datasource->db->select('*');
		$this->datasource->db->from($this->tableName);
		$this->datasource->db->orderby('post_creationdate', 'DESC');
		$query = $this->datasource->db->get();

		$result = array();
		if($query->num_rows() > 0) {
			foreach($query->result('array') as $row) {
				$post = $this->sqlMapper->mapArrayToObject($row, $this->objectName);
				$result[] = $post;
			}
		}
		return $result;	
	}

}

?>