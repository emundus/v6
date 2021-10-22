<?php
/**
 * @package         Regular Labs Library
 * @version         21.9.16879
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2021 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library\Condition;

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri as JUri;
use RegularLabs\Library\Condition;
use RegularLabs\Library\RegEx;
use RegularLabs\Library\StringHelper;

/**
 * Class Url
 * @package RegularLabs\Library\Condition
 */
class Url extends Condition
{
	public function pass()
	{
		$regex         = $this->params->regex ?? false;
		$casesensitive = $this->params->casesensitive ?? false;

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
			StringHelper::html_entity_decoder(urldecode($url)),
			urldecode($url),
			StringHelper::html_entity_decoder($url),
			$url,
		];
		$urls = array_unique($urls);

		$pass = false;
		foreach ($urls as $url)
		{
			if ( ! $casesensitive)
			{
				$url = StringHelper::strtolower($url);
			}

			foreach ($this->selection as $selection)
			{
				$selection = trim($selection);
				if ($selection == '')
				{
					continue;
				}

				if ($regex)
				{
					$url_part = str_replace(['#', '&amp;'], ['\#', '(&amp;|&)'], $selection);
					if (@RegEx::match($url_part, $url, $match, $casesensitive ? 's' : 'si'))
					{
						$pass = true;
						break;
					}

					continue;
				}

				if ( ! $casesensitive)
				{
					$selection = StringHelper::strtolower($selection);
				}

				if (strpos($url, $selection) !== false)
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

		return $this->_($pass);
	}
}
