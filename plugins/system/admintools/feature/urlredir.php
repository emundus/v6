<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

class AtsystemFeatureUrlredir extends AtsystemFeatureAbstract
{
	protected $loadOrder = 500;

	private static $siteTemplates = null;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		if (!$this->helper->isFrontend())
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
		$basepath = ltrim(JUri::base(true), '/');

		$myURL   = JUri::getInstance();
		$fullurl = ltrim($myURL->toString(array('path', 'query', 'fragment')), '/');
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

		$db = JFactory::getDbo();

		$sql = $db->getQuery(true)
			->select(array($db->qn('source'), $db->qn('keepurlparams')))
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
			list ($newURL, $keepQueryParams) = $newURLStruct;

			$new      = JUri::getInstance($newURL);
			$host     = $new->getHost();
			$fragment = $new->getFragment();
			$query    = $new->getQuery();

			if (empty($host))
			{
				$base = JUri::getInstance(JUri::base());
				$new->setHost($base->getHost());
				$new->setPort($base->getPort());
				$new->setScheme($base->getScheme());
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
				$myUrlParams = $myURL->getQuery(true);

				foreach ($myUrlParams as $k => $v)
				{
					if (!isset($newUrlParams[$k]))
					{
						$new->setVar($k, $v);
					}
				}

				$myFragment = $myURL->getFragment();
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

			if (class_exists('JApplicationCms') && class_exists('JApplicationWeb')
				&& ($this->app instanceof JApplicationCms)
				&& ($this->app instanceof JApplicationWeb))
			{
				/**
				 * Since Joomla! 3.3 or 3.4 JApplicationSite extends JApplicationCms and the redirect() method has two
				 * parameters, URL and HTTP code. In theory the method still supports calling it with four parameters
				 * for b/c but unfortunately the core code is unsurprisingly broken: the fourth parameter $moved is
				 * never translated from a boolean (moved yes/no) to an HTTP code integer (301 or 303). As a result
				 * JApplicationWeb ends up issuing a 303 temporary redirection instead of a 301 permanent redirection.
				 * The only way to work around this Joomla bug is to check if the application object extends
				 * JApplicationCms and JApplicationWeb which means that we can call the two-parameter version of the
				 * redirect method.
				 */
				$this->app->redirect($targetURL, 301);

				return;
			}

			// If you're here, you have an ancient Joomla version and we have to use the four parameter method...
			$this->app->redirect($targetURL, '', 'message', true);
		}
	}
}