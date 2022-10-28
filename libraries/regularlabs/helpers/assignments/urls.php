<?php
/**
 * @package         Regular Labs Library
 * @version         22.4.18687
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/* @DEPRECATED */

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri as JUri;

if (is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';
}

require_once dirname(__FILE__, 2) . '/assignment.php';
require_once dirname(__FILE__, 2) . '/text.php';

class RLAssignmentsURLs extends RLAssignment
{
	public function passURLs()
	{
		$regex = $this->params->regex ?? 0;

		if ( ! is_array($this->selection))
		{
			$this->selection = explode("\n", $this->selection);
		}

		if (count($this->selection) == 1)
		{
			$this->selection = explode("\n", $this->selection[0]);
		}

		$url = JUri::getInstance();
		$url = $url->toString();

		$urls = [
			RLText::html_entity_decoder(urldecode($url)),
			urldecode($url),
			RLText::html_entity_decoder($url),
			$url,
		];
		$urls = array_unique($urls);

		$pass = false;
		foreach ($urls as $url)
		{
			foreach ($this->selection as $s)
			{
				$s = trim($s);
				if ($s == '')
				{
					continue;
				}

				if ($regex)
				{
					$url_part = str_replace(['#', '&amp;'], ['\#', '(&amp;|&)'], $s);
					$s        = '#' . $url_part . '#si';
					if (@preg_match($s . 'u', $url) || @preg_match($s, $url))
					{
						$pass = true;
						break;
					}

					continue;
				}

				if (strpos($url, $s) !== false)
				{
					$pass = true;
					break;
				}
			}

			if ($pass)
			{
				break;
			}
		}

		return $this->pass($pass);
	}
}
