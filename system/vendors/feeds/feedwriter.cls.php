<?php
// $Id: feedwriter.cls.php,v 1.2 2005/11/06 22:13:16 gr Exp $

/**
 * Feed Writer base class
 *
 * @author Gerd Riesselmann http://www.gerd-riesselmann.net
 */

/*
Copyright (C) 2005 Gerd Riesselmann

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

http://www.gnu.org/licenses/gpl.html
*/


/**
 * Simple Date Structure holding feed data
 */
class FeedWriterTitle
{
	var $title = "";
	var $lastUpdated = "";
	var $description = "";
	var $link = "";
	var $imageURL = "";
	var $imageWidth = "";
	var $imageHeight = "";
	var $generator = "";
	var $encoding = "ISO-8859-1";
	var $language="en";
	var $thisURL = "";
	
	function validate()
	{
		$this->title = check_plain(strip_tags($this->title));
		$this->description = check_plain(strip_tags($this->description));
		$this->link = check_url($this->link);
		$this->imageURL = check_url($this->imageURL);
		$this->imageWidth = check_plain($this->imageWidth);
		$this->imageHeight = check_plain($this->imageHeight);
		$this->generator = check_plain($this->generator);
		$this->encoding = check_plain($this->encoding);
		$this->language = check_plain($this->language);
		$this->thisURL = check_url($this->thisURL);	
	}
}

/**
 * Simple Date Structure holding feed item data
 */
class FeedWriterItem
{
	var $description = "";
	var $title = "";
	var $link = "";
	var $pubDate = "";
	var $lastUpdate = "";
	var $guid = "";
	var $authorName = "";
	var $authorEmail = "";
	var $content = "";
	var $categories = array();
	var $enclosures = array();
	
	var $baseURL = "";
	
	function validate()
	{
		$this->title = check_plain(strip_tags($this->title));
		$this->link = check_url($this->link);
		$this->guid = check_plain($this->guid);
		$this->authorName = check_plain($this->authorName);
		$this->authorEmail = check_plain($this->authorEmail);
	}
}

/**
 *  Data Structure holding a category
 */
class FeedWriterCategory
{
	var $domain = "";
	var $title = "";
	
	function validate()
	{
		$this->domain = check_url($this->domain);
		$this->title = check_plain($this->title);
	}
}

/**
 * Date Structure for enclosures
 */
class FeedWriterEnclosures
{
	var $url = "";
	var $length = 0;
	var $type = "";
	
	function validate()
	{
		$this->url = check_url($this->url);
		$this->length = check_plain($this->length);
		$this->type = check_plain($this->type);
	}
}

class FeedWriter
{
	// Template method
	function main(&$arrItems, $title)
	{
		// Send headers
		if (headers_sent() === false)
		{
			header("Last-Modified: " . date("r", $title->lastUpdated));
			header("ETag: " . '"' . md5($title->lastUpdated) . '"');
			
			$this->sendHeader();
		}

		$title->validate();
		$this->printTitle($title);

//		$it =& new ArrayIterator($arrItems);
//		while($item =& $it->next())
//		{
//			$this->printItem($item);
//		}
		
		foreach($arrItems as $item)
		{
			$item->validate();
			$this->printItem($item);
		}
		
		$this->printEnd();
	}

	function sendHeader()
	{
		header("Content-Type: text/xml");
	}

	function printItem(&$item)
	{
		die("Not implemented");
	}

	function printTitle(&$title)
	{
		die("Not implemented");
	}

	function printEnd()
	{
		die("Not implemented");
	}
	
	function clear($text)
	{
		return htmlspecialchars(html_entity_decode(str_replace("&nbsp;", "", strip_tags($text)), ENT_QUOTES), ENT_QUOTES);
	}
	
	function relToAbs($text, $base)
	{
		if (empty($base))
			return $text;
			
		if (substr($base, -1, 1) != "/")
			$base .= "/";
		
		$pattern = 	"/<a([^>]*) href=\"[^http|ftp|https]([^\"]*)\"/";
		$replace = "<a\${1} href=\"" . $base . "\${2}\"";
		$text = preg_replace($pattern, $replace, $text);
		
		$pattern = 	'/<img([^>]*) src="[^http|ftp|https]([^"]*)"/';
		$replace = '<img\${1} src="' . $base . '\${2}"';
		$text = preg_replace($pattern, $replace, $text);
		
		return $text;
	}
}
?>