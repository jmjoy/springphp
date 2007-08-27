<?php


function &yaml_load($filename) {

	//$filename = realpath($filename);
	$cache_dir = BASEPATH . '/cache/yaml/';
	$cache_file = $cache_dir . md5($filename) . '.php';

	if (file_exists($cache_file)) {
		$yaml = null;
		$mytime = null;
		include ($cache_file);
		if ($mytime != filemtime($filename)) {
			unlink($cache_file);
			return yaml_load($filename);
		}
		return $yaml;
	} else {

		//only include spyc when we need it
		include_once (BASEPATH.'vendors/spyc/spyc.php');
		if(!file_exists($filename)) show_error('yaml_load error', 'YAML file does not exist: '.$filename);

		$yaml =& Spyc :: YAMLLoad($filename);

		if(is_null($yaml) || empty($yaml))
			show_error('yaml_load error', 'Spyc::YAMLLoad returned empty array, is file empty? - '.$filename);

		if (!is_dir($cache_dir) && mkdir($cache_dir) == false)
			show_error('yaml_load error', 'Cannot make cache directory, please check permissions on base directory: '.$cache_dir);

		if (!$fp = @ fopen($cache_file, 'wb'))
			show_error('yaml_load error', 'Cannot write cache file, please check permissions on cache directory: '.$cache_dir);

		$oldtime = filemtime($filename);

		flock($fp, LOCK_EX);
		fwrite($fp, '<?php $yaml = ' . var_export($yaml, true) . ';
						$mytime = ' . $oldtime . ';
					?>');
		flock($fp, LOCK_UN);
		fclose($fp);
		@ chmod($cache_file, 0777);

		return $yaml;
	}

}
?>
