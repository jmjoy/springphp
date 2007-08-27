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
 * Encryption
 *
 * @package		Redstart
 * @subpackage	Libraries
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @since		Version 1.0
 * @filesource
 */

 class Encryption {

	var $key;
	var $_mcryptExists = false;
	var $_mcrypt_cipher;
	var $_mcrypt_mode;
	
	function Encryption(){
		$this->_mcryptExists = ( ! function_exists('mcrypt_encrypt')) ? FALSE : TRUE;
	}
	
	function encode($string, $key = '')
	{
		$key = $this->getKey($key);
		$enc = $this->_xor_encode($string, $key);
		
		if ($this->_mcryptExists === TRUE)
		{
			$enc = $this->mcryptEncode($enc, $key);
		}
		return base64_encode($enc);		
	}

	function decode($string, $key = '')
	{
		$key = $this->getKey($key);
		$dec = base64_decode($string);
		
		 if ($dec === FALSE)
		 {
		 	return FALSE;
		 }
		
		if ($this->_mcryptExists === TRUE)
		{
			$dec = $this->mcryptDecode($dec, $key);
		}
		
		return $this->_xor_decode($dec, $key);
	}
	
	function getKey($key = '') {
		if ($key == '')
		{	
			$key = $this->key;
			if(is_null($key))
				show_error('Encryption', 'In order to use the encryption class, you must set an encryption key in your config file.');
		}
		
		return md5($key);	
	}
	
	function _xor_encode($string, $key)
	{
		$rand = '';
		while (strlen($rand) < 32) 
		{    
			$rand .= mt_rand(0, mt_getrandmax());
		}
	
		$rand = md5($rand);
		
		$enc = '';
		for ($i = 0; $i < strlen($string); $i++)
		{			
			$enc .= substr($rand, ($i % strlen($rand)), 1).(substr($rand, ($i % strlen($rand)), 1) ^ substr($string, $i, 1));
		}
				
		return $this->_xor_merge($enc, $key);
	}

	function _xor_decode($string, $key)
	{
		$string = $this->_xor_merge($string, $key);
		
		$dec = '';
		for ($i = 0; $i < strlen($string); $i++)
		{
			$dec .= (substr($string, $i++, 1) ^ substr($string, $i, 1));
		}
	
		return $dec;
	}

	function _xor_merge($string, $key)
	{
		$hash = md5($key);
		$str = '';
		for ($i = 0; $i < strlen($string); $i++)
		{
			$str .= substr($string, $i, 1) ^ substr($hash, ($i % strlen($hash)), 1);
		}
		
		return $str;
	}

	function mcryptEncode($data, $key) 
	{	
		$this->_get_mcrypt();
		$init_size = mcrypt_get_iv_size($this->_mcrypt_cipher, $this->_mcrypt_mode);
		$init_vect = mcrypt_create_iv($init_size, MCRYPT_RAND);
		return mcrypt_encrypt($this->_mcrypt_cipher, $key, $data, $this->_mcrypt_mode, $init_vect);
	}

	function mcryptDecode($data, $key) 
	{
		$this->_get_mcrypt();
		$init_size = mcrypt_get_iv_size($this->_mcrypt_cipher, $this->_mcrypt_mode);
		$init_vect = mcrypt_create_iv($init_size, MCRYPT_RAND);
		return rtrim(mcrypt_decrypt($this->_mcrypt_cipher, $key, $data, $this->_mcrypt_mode, $init_vect), "\0");
	}

	function setCypher($cypher)
	{
		$this->_mcrypt_cipher = $cypher;
	}

	function setMode($mode)
	{
		$this->_mcrypt_mode = $mode;
	}

	function _get_mcrypt()
	{
		if ($this->_mcrypt_cipher == '') 
		{
			$this->_mcrypt_cipher = MCRYPT_RIJNDAEL_256;
		}
		if ($this->_mcrypt_mode == '') 
		{
			$this->_mcrypt_mode = MCRYPT_MODE_ECB;
		}
	}

 }
 ?>
