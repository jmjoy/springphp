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
 * Asset Helpers
 *
 * @package		Redstart
 * @subpackage  Asset-Tags
 * @author		Ryan Scheuermann
 * @link		http://www.concept64.com/
 * @link		http://www.springframework.org/
 * @since		Version 1.0
 * @filesource
 */
function auto_discovery_link_tag($type = 'rss', $url, $tag_options = array())
{
  return tag('link', array(
    'rel'   => isset($tag_options['rel']) ? $tag_options['rel'] : 'alternate',
    'type'  => isset($tag_options['type']) ? $tag_options['type'] : 'application/'.$type.'+xml',
    'title' => isset($tag_options['title']) ? $tag_options['title'] : ucfirst($type),
    'href'  => sytem_url($url)
  ));
}


function image_tag($source, $options = array())
{
  if (!$source)
  {
    return '';
  }

  $options = _parse_attributes($options);

  $absolute = false;
  if (isset($options['absolute']))
  {
    unset($options['absolute']);
    $absolute = true;
  }

  $options['src'] = system_url($source);

  if (!isset($options['alt']))
  {
    $path_pos = strrpos($source, '/');
    $dot_pos = strrpos($source, '.');
    $begin = $path_pos ? $path_pos + 1 : 0;
    $nb_str = ($dot_pos ? $dot_pos : strlen($source)) - $begin;
    $options['alt'] = ucfirst(substr($source, $begin, $nb_str));
  }

  if (isset($options['size']))
  {
    list($options['width'], $options['height']) = split('x', $options['size'], 2);
    unset($options['size']);
  }

  return tag('img', $options);
}

function javascript_include_tag()
{
  $html = '';
  foreach (func_get_args() as $source)
  {
  	$source = system_url($source);
    $html .= content_tag('script', '', array('type' => 'text/javascript', 'src' => $source))."\n";
  }

  return $html;
}


function stylesheet_tag()
{
  $sources = func_get_args();
  $sourceOptions = (func_num_args() > 1 && is_array($sources[func_num_args() - 1])) ? array_pop($sources) : array();

  $html = '';
  foreach ($sources as $source)
  {
  	$source = system_url($source);
    $options = array_merge(array('rel' => 'stylesheet', 'type' => 'text/css', 'media' => 'screen', 'href' => $source), $sourceOptions);
    $html   .= tag('link', $options)."\n";
  }

  return $html;
}

?>
