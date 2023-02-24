<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Joomla\CMS\Uri\Uri;

defined('_JEXEC') || die;

class AtsystemFeatureUrlredir extends AtsystemFeatureAbstract
{
	private static $siteTemplates = null;

	protected $loadOrder = 500;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		if (!$this->container->platform->isFrontend())
		{
			return false;
		}

		return ($this->cparams->getValue('urlredirection', 1) == 1);
	}

	/**
	 * Performs custom redirections defined in the back-end of the component.
	 */
	public function onAfterInitialise()
	{
		// Get the base path
		$basepath = ltrim(Uri::base(true), '/');

		$myURL   = Uri::getInstance();
		$fullurl = ltrim($myURL->toString(['path', 'query', 'fragment']), '/');
		$path    = ltrim($myURL->getPath(), '/');

		$pathLength = strlen($path);
		$baseLength = strlen($basepath);

		if ($baseLength != 0)
		{
			if ($pathLength > $baseLength)
			{
				$path = ltrim(substr($path, $baseLength), '/');
			}
			elseif ($pathLength == $baseLength)
			{
				$path = '';
			}
		}

		$pathLength = strlen($fullurl);

		if ($baseLength != 0)
		{
			if ($pathLength > $baseLength)
			{
				$fullurl = ltrim(substr($fullurl, $baseLength), '/');
			}
			elseif ($pathLength = $baseLength)
			{
				$fullurl = '';
			}
		}

		$db = $this->container->db;

		$sql = $db->getQuery(true)
			->select([$db->qn('source'), $db->qn('keepurlparams')])
			->from($db->qn('#__admintools_redirects'))
			->where(
				'((' . $db->qn('dest') . ' = ' . $db->q($path) . ')' .
				' OR ' .
				'(' . $db->qn('dest') . ' = ' . $db->q($fullurl) . ')' .
				' OR ' .
				'(' . $db->q($fullurl) . ' LIKE ' . $db->qn('dest') . '))'
			)->where($db->qn('published') . ' = ' . $db->q('1'))
			->order($db->qn('ordering') . ' DESC');
		$db->setQuery($sql, 0, 1);

		try
		{
			$newURLStruct = $db->loadRow();
		}
		catch (Exception $e)
		{
			$newURLStruct = null;
		}

		if (!empty($newURLStruct))
		{
			[$newURL, $keepQueryParams] = $newURLStruct;

			$new  = Uri::getInstance($newURL);
			$host = $new->getHost();

			if ((substr($newURL, 0, 1) !== '/') && (strpos($newURL, '://') === false))
			{
				$newURL = '/' . $newURL;
			}

			if (empty($host))
			{
				$base = Uri::getInstance(Uri::base());
				$new->setHost($base->getHost());
				$new->setPort($base->getPort());
				$new->setScheme($base->getScheme());
				$new->setPath($base->getPath() . $new->getPath());
				$new->setFragment($new->getFragment());
			}

			// Keep URL Params == 1 (override all)
			if ($keepQueryParams == 1)
			{
				$myUrlParams = $myURL->getQuery(true);

				foreach ($myUrlParams as $k => $v)
				{
					$new->setVar($k, $v);
				}

				$myFragment = $myURL->getFragment();
				if (!empty($myFragment))
				{
					$new->setFragment($myURL->getFragment());
				}

				$new->setScheme($myURL->getScheme());
			}
			// Keep URL Params == 2 (add only)
			elseif ($keepQueryParams == 2)
			{
				$newUrlParams = $new->getQuery(true);
				$myUrlParams  = $myURL->getQuery(true);

				foreach ($myUrlParams as $k => $v)
				{
					if (!isset($newUrlParams[$k]))
					{
						$new->setVar($k, $v);
					}
				}

				$myFragment  = $myURL->getFragment();
				$newFragment = $new->getFragment();

				if (!empty($myFragment) && empty($newFragment))
				{
					$new->setFragment($myURL->getFragment());
				}

				$new->setScheme($myURL->getScheme());
			}

			$path = $new->getPath();

			if (!empty($path))
			{
				if (substr($path, 0, 1) != '/')
				{
					$new->setPath('/' . rtrim($basepath, '/') . '/' . $path);
				}
				elseif (strlen($path) > 1)
				{
					$new->setPath('/' . $path);
				}
			}

			$targetURL = $new->toString();

			$this->container->platform->redirect($targetURL, 301);
		}
	}
}
