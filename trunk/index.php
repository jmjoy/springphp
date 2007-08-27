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

$application_folder = 'app';
$system_folder = 'system';

// boost performance by compiling the entire system into 1 file
define('ONE_SYS_FILE', true);


// NO EDITING BEYOND THIS POINT
$timer_start = microtime();

$realpath = @realpath(dirname(__FILE__));
if($realpath !== FALSE)
	$system_folder = str_replace("\\", "/", $realpath.'/'.$system_folder);

define('EXT', '.'.pathinfo(__FILE__, PATHINFO_EXTENSION));
define('FCPATH', __FILE__);
define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('ROOTPATH', $realpath);
define('BASEPATH', $system_folder.'/');
define('APPPATH', $application_folder.'/');

//load the system
require(BASEPATH .'bootstrap'.EXT);

// The 3 GLOBALs
$request =& new Request();
$response =& new Response();
$appContext =& new AppContext();

//starts output buffering
$response->start();

//bootstrap the app if needed
if(file_exists(APPPATH.'bootstrap'.EXT))
	include(APPPATH.'bootstrap'.EXT);

//load the application
AppContext::load(APPPATH.'/config/app-context.yml');

//load any plugins? - NOTE: plugins can override core behavior and add new controllers
autodiscover_plugins();

//display cache after loading plugins but before calling Dispatcher
if($response->displayCache() !== TRUE)
{
	//handle the request
	$dispatcher =& AppContext::createAutowiredService('Dispatcher');
	$dispatcher->process(&$request, &$response);
}

//send the response to the browser
$response->flush();  // flushs output buffering

?>
