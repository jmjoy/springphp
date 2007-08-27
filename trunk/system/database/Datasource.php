<?php
/*
 * Copyright 2007 the original author or authors.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Datasource
 *
 * @package		Redstart
 * @subpackage  Database
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @since		Version 1.0
 * @filesource
 */
//TODO: db caching
//TODO: db utility
class Datasource {

	var $db = null;

	var $hostname;
	var $username;
	var $password;
	var $database;
	var $connectionId;
	var $port;
	var $dbDriver;
	var $tablePrefix;
	var $useActiveRecord = TRUE;
	var $usePersistentConnection = TRUE;
	var $debugMode = TRUE;
//	var $cacheOn = FALSE;
//	var $cacheDir = "";

	function Datasouce() {

	}

	function load() {

		if(empty($this->dbDriver)) show_error('Datasource', 'You have not specified a database driver to use.');

		require_once(BASEPATH.'vendors/database/DB_driver'.EXT);

		if ( !empty($this->useActiveRecord) && $this->useActiveRecord == TRUE)
		{

			require_once(BASEPATH.'vendors/database/DB_active_rec'.EXT);

			if ( ! class_exists('CI_DB'))
			{
				eval('class CI_DB extends CI_DB_active_record { }');
			}
		}
		else
		{
			if ( ! class_exists('CI_DB'))
			{
				eval('class CI_DB extends CI_DB_driver { }');
			}
		}

		$dbdriver = $this->dbDriver;

		require_once(BASEPATH.'vendors/database/drivers/'.$dbdriver.'/'.$dbdriver.'_driver'.EXT);

		// Instantiate the DB adapter
		$driver = 'CI_DB_'.$dbdriver.'_driver';

		$params = array(

			'hostname' => $this->hostname,
			'username' => $this->username,
			'password' => $this->password,
			'database' => $this->database,
			'conn_id'  => $this->connectionId,
			'port'     => $this->port,
			'dbdriver' => $this->dbDriver,
			'dbprefix' => $this->tablePrefix,
			'active_r' => $this->useActiveRecord,
			'pconnect' => $this->usePersistentConnection,
			'db_debug' => $this->debugMode
//			'cache_on' => $this->cacheOn,
//			'cachedir' => $this->cacheDir

		);

		$this->db =& new $driver($params);
	}

}


?>
