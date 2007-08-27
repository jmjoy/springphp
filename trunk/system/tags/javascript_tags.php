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
 * Javascript Helpers
 *
 * @package		Redstart
 * @subpackage  Javascript-Tags
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
function link_to_function($name, $function, $html_options = array ()) {
	$html_options['href'] = '#';
	$html_options['onclick'] = $function . '; return false;';

	return content_tag('a', $name, $html_options);
}

function javascript_tag($content) {
	return content_tag('script', javascript_cdata_section($content), array (
		'type' => 'text/javascript'
	));
}

function javascript_cdata_section($content) {
	return "\n//" . cdata_section("\n$content\n//") . "\n";
}


function _options_for_javascript($options) {
	$opts = array ();
	foreach ($options as $key => $value) {
		$opts[] = "$key:$value";
	}
	sort($opts);

	return '{' . join(', ', $opts) . '}';
}

function _array_or_string_for_javascript($option) {
	if (is_array($option)) {
		return "['" . join('\',\'', $option) . "']";
	} else
		if ($option) {
			return "'$option'";
		}
}



function _convert_options_to_javascript($html_options, $target = '')
{
  // confirm
  $confirm = isset($html_options['confirm']) ? $html_options['confirm'] : '';
  unset($html_options['confirm']);

  // popup
  $popup = isset($html_options['popup']) ? $html_options['popup'] : '';
  unset($html_options['popup']);

  // post
  $post = isset($html_options['post']) ? $html_options['post'] : '';
  unset($html_options['post']);

  $onclick = isset($html_options['onclick']) ? $html_options['onclick'] : '';

  if ($popup && $post)
  {
    show_error('You can\'t use "popup" and "post" in the same link');
  }
  else if ($confirm && $popup)
  {
    $html_options['onclick'] = $onclick.'if ('._confirm_javascript_function($confirm).') { '._popup_javascript_function($popup, $target).' };return false;';
  }
  else if ($confirm && $post)
  {
    $html_options['onclick'] = $onclick.'if ('._confirm_javascript_function($confirm).') { '._post_javascript_function().' };return false;';
  }
  else if ($confirm)
  {
    if ($onclick)
    {
      $html_options['onclick'] = 'if ('._confirm_javascript_function($confirm).') {'.$onclick.'}';
    }
    else
    {
      $html_options['onclick'] = 'return '._confirm_javascript_function($confirm).';';
    }
  }
  else if ($post)
  {
    $html_options['onclick'] = $onclick._post_javascript_function().'return false;';
  }
  else if ($popup)
  {
    $html_options['onclick'] = $onclick._popup_javascript_function($popup, $target).'return false;';
  }

  return $html_options;
}

function _confirm_javascript_function($confirm)
{
  return "confirm('".escape_javascript($confirm)."')";
}

function _popup_javascript_function($popup, $target = '')
{
  $url = $target == '' ? 'this.href' : "'".url_for($target)."'";

  if (is_array($popup))
  {
    if (isset($popup[1]))
    {
      return "window.open(".$url.",'".$popup[0]."','".$popup[1]."');";
    }
    else
    {
      return "window.open(".$url.",'".$popup[0]."');";
    }
  }
  else
  {
    return "window.open(".$url.");";
  }
}

function _post_javascript_function()
{
  return "f = document.createElement('form'); document.body.appendChild(f); f.method = 'POST'; f.action = this.href; f.submit();";
}

function _encodeText($text)
{
  $encoded_text = '';

  for ($i = 0; $i < strlen($text); $i++)
  {
    $char = $text{$i};
    $r = rand(0, 100);

    # roughly 10% raw, 45% hex, 45% dec
    # '@' *must* be encoded. I insist.
    if ($r > 90 && $char != '@')
    {
      $encoded_text .= $char;
    }
    else if ($r < 45)
    {
      $encoded_text .= '&#x'.dechex(ord($char)).';';
    }
    else
    {
      $encoded_text .= '&#'.ord($char).';';
    }
  }

  return $encoded_text;
}
?>