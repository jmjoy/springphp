<?php

class TagParser {
	var $functions = array();

	function TagParser() {

	}

	function register($tag, $callback)
	{
		$this->functions[$tag] = $callback;
	}

	function parse($xml, $namespace = 'tag') {
		// use the comment syntax
		$pat = '/<'.$namespace.':(\S+)\b(.*)(?:(?<!br )(\/))?'.chr(62).'(?(3)|(.+)<\/'.$namespace.':\1>)/sU';
		return preg_replace_callback($pat, array(&$this, '_process_tags'), $xml);
	}

	function _process_tags($matches) {
		$func = trim($matches[1]);

		$functions =& $this->functions;

		if(!isset($functions[strtolower($func)]))
			return '';
		$func = $functions[strtolower($func)];

			$atts = (isset($matches[2])) ? $this->_splat($matches[2]) : null;
			$content = (isset($matches[4])) ? $matches[4] : null;
			$out = '';
			$echo = false;

			ob_start();
			if ($atts) {
				if ($atts['parse']) {
					unset($atts['parse']);
					$content = $this->wp_parse($content);
				}
				if ($atts['echo']) {
					unset($atts['echo']);
					$echo = true;
				}
				elseif ($atts['string']) {
					unset($atts['string']);
					$ret = '';
					foreach($atts as $k => $v) {
						if ( $v != '' ) {
							if ( $ret != '' )
								$ret .= '&';
							$v = urlencode($v);
							$ret .= "$k=$v";
						}
					}
					$atts = $ret;
				}
			}

			if ($content) {
				if(is_array($atts))
					$atts['content'] = $content;
				else
				{
					if ( $atts != '' )
						$atts .= '&';
					$concent = urlencode($content);
					$atts .= "&content=$content";
				}
			}

			if(empty($atts))
				$atts = array();

			if ($echo) echo call_user_func($func,$atts);
			else call_user_func($func,$atts);

			$out = ob_get_clean();
		return $out;
	}

	function _splat($text) {
		$atts = array();
		if (preg_match_all('/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)/', $text, $match, PREG_SET_ORDER)) {
			foreach ($match as $m) {
				if (!empty($m[1]))
					$atts[strtolower($m[1])] = stripcslashes($m[2]);
				elseif (!empty($m[3]))
					$atts[strtolower($m[3])] = stripcslashes($m[4]);
				elseif (!empty($m[5]))
					$atts[strtolower($m[5])] = stripcslashes($m[6]);
			}
		}
		return $atts;
	}


}

?>