<?php

$rssurl = 'https://huntsville.craigslist.org/search/rea?format=rss';
//$rssurl = 'http://huntsville.backpage.com/online/exports/Rss.xml?section=4375';
//$rssDoc = JSimplepieFactory::getFeedParser($rssurl, 86400);
//jimport('joomla.feed.factory');
//$feed   = new JFeedFactory;
//$feed->registerParser('rdf:RDF', 'JFeedParserRss');
//$rssDoc = $feed->getFeed($rssurl);

$rssDoc = JSimplepieFactory::getFeedParser($rssurl, 86400);

if ($rssDoc == false)
{
	$output = FText::_('Error: Feed not retrieved');
}
else
{
	// Channel header and link
	$title = $rssDoc->get_title();
	$link = $rssDoc->get_link();
	$output = '<table class="adminlist">';
	$output .= '<tr><th colspan="3"><a href="' . $link . '" target="_blank">' . FText::_($title) . '</th></tr>';
	$items = array_slice($rssDoc->get_items(), 0, 3);
	$numItems = count($items);

	if ($numItems == 0)
	{
		$output .= '<tr><th>' . FText::_('No news items found') . '</th></tr>';
	}
	else
	{
		$k = 0;

		for ($j = 0; $j < $numItems; $j++)
		{
			$item = $items[$j];
			$output .= '<tr><td class="row' . $k . '">';
			$output .= '<a href="' . $item->get_link() . '" target="_blank">' . $item->get_title() . '</a>';
			$output .= '<br />' . $item->get_date('Y-m-d');

			if ($item->get_description())
			{
				$description = $item->get_description();
				$output .= '<br />' . $description;
			}

			$output .= '</td></tr>';
			$k = 1 - $k;
		}
	}

	$output .= '</table>';
}

$foo = 1;
