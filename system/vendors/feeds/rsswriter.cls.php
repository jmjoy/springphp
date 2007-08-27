<?php
// $Id: rsswriter.cls.php,v 1.2 2005/11/06 22:13:16 gr Exp $

/**
 * Build RSS file, targeted by FeedWriter class
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

require_once dirname(__FILE__) . "/feedwriter.cls.php";

class RSSWriter extends FeedWriter
{
	function sendHeader()
	{
		header("Content-Type: application/xml");
	}

	function printItem(&$item)
	{
	?>
	<item>
		<title><?php print $item->title; ?></title>
		<link><?php print $item->link; ?></link>
		<description><?php print $this->clear($item->description); ?></description>
		<content:encoded><![CDATA[<?php print $this->relToAbs($item->content, $item->baseURL); ?>]]></content:encoded>
		<pubDate><?php print date("D, d M Y H:i:s O", $item->pubDate); ?></pubDate>
		<dc:creator><?php print (empty($item->authorName)) ? t("Unknown") : $item->authorName; ?></dc:creator>
		<guid isPermaLink="true"><?php print $item->guid; ?></guid>
		<?php
		if (!empty($item->authorEmail))
			print "<author>" . $item->authorEmail . "</author>";
		?>
		<?php
		foreach($item->categories as $cat)
		{
			$cat->validate();
			?>
			<category domain="<?php print $cat->domain; ?>"><?print $cat->title; ?></category>
			<?php
		}
		?>
		<?php
		foreach($item->enclosures as $enc)
		{
			$enc->validate();
			?>
			<enclosure url="<?php print $enc->url; ?>" length="<?print $enc->length; ?>" type="<?php print $enc->type; ?>" />
			<?php
		}
		?>
	</item>
	<?php
	}

	function printTitle(&$title)
	{
	print '<?xml version="1.0" encoding="' . $title->encoding . '"?>';
	?>
	<rss version="2.0"
		xmlns:content="http://purl.org/rss/1.0/modules/content/"
		xmlns:wfw="http://wellformedweb.org/CommentAPI/"
		xmlns:dc="http://purl.org/dc/elements/1.1/"
	>
	<channel>
		<title><?php print $title->title; ?></title>
		<link><?php print $title->link; ?></link>
		<description><?php print $title->description; ?></description>
		<language><?php print $title->language; ?></language>
		<pubDate><?php print date("r", $title->lastUpdated); ?></pubDate>
		<generator><?php print $title->generator; ?></generator>
		<?php
		if (!empty($title->imageURL))
		{
		?>
		<image>
			<title><?php print $title->title; ?></title>
			<link><?php print $title->link; ?></link>
			<url><?php print $title->imageURL; ?></url>
			<?php if (!empty($title->imageWidth) && !empty($title->imageHeight))
			{
			?>
			<width><?php print $title->imageWidth; ?></width>
			<height><?php print $title->imageHeight; ?></height>
			<?php
			}
			?>
		</image>
		<?php
		}
	}

	function printEnd()
	{
	?>
	</channel>
	</rss>
	<?php
	}
}
?>