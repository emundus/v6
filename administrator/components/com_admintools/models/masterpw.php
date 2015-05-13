<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

JLoader::import('joomla.application.component.model');

class AdmintoolsModelMasterpw extends F0FModel
{
	var $views = array(
		'adminpw', 'badwords', 'dbtools', 'eom', 'fixperms',
		'fixpermsconfig', 'htmaker', 'ipbl', 'ipwl',
		'log', 'redirs', 'masterpw',
		'update', 'waf', 'wafconfig', 'cleantmp', 'dbchcol', 'seoandlink',
		'dbprefix',
	);

	/**
	 * Checks if the user should be granted access to the current view,
	 * based on his Master Password setting.
	 *
	 * @param string view Optional. The string to check. Leave null to use the current view.
	 *
	 * @return bool
	 */
	public function accessAllowed($view = null)
	{
		if (interface_exists('JModel'))
		{
			$params = JModelLegacy::getInstance('Storage', 'AdmintoolsModel');
		}
		else
		{
			$params = JModel::getInstance('Storage', 'AdmintoolsModel');
		}

		if (empty($view))
		{
			$view = $this->input->get('view', 'cpanel');
		}

		$altView = F0FInflector::isPlural($view) ? F0FInflector::singularize($view) : F0FInflector::pluralize($view);

		if (!in_array($view, $this->views) && !in_array($altView, $this->views))
		{
			return true;
		}

		$masterHash = $params->getValue('masterpassword', '');

		if (!empty($masterHash))
		{
			$masterHash = md5($masterHash);

			// Compare the master pw with the one the user entered
			$session = JFactory::getSession();
			$userHash = $session->get('userpwhash', '', 'admintools');

			if ($userHash != $masterHash)
			{
				// The login is invalid. If the view is locked I'll have to kick the user out.
				$lockedviews_raw = $params->getValue('lockedviews', '');

				if (!empty($lockedviews_raw))
				{
					$lockedViews = explode(",", $lockedviews_raw);

					if (in_array($view, $lockedViews) || in_array($altView, $lockedViews))
					{
						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Compares the user-supplied password against the master password
	 *
	 * @return bool True if the passwords match
	 */
	public function hasValidPassword()
	{
		if (interface_exists('JModel'))
		{
			$params = JModelLegacy::getInstance('Storage', 'AdmintoolsModel');
		}
		else
		{
			$params = JModel::getInstance('Storage', 'AdmintoolsModel');
		}
		$masterHash = $params->getValue('masterpassword', '');

		if (empty($masterHash))
		{
			return true;
		}

		$masterHash = md5($masterHash);
		$session = JFactory::getSession();
		$userHash = $session->get('userpwhash', '', 'admintools');

		return ($masterHash == $userHash);
	}

	/**
	 * Stores the hash of the user's password in the session
	 *
	 * @param $passwd string The password supplied by the user
	 */
	public function setUserPassword($passwd)
	{
		$session = JFactory::getSession();
		$userHash = md5($passwd);
		$session->set('userpwhash', $userHash, 'admintools');
	}

	/**
	 * Saves the Master Password and the proteected views list
	 *
	 * @param string $masterPassword The new master password
	 * @param array  $protectedViews A list of the views to protect
	 */
	public function saveSettings($masterPassword, array $protectedViews)
	{
		if (interface_exists('JModel'))
		{
			$params = JModelLegacy::getInstance('Storage', 'AdmintoolsModel');
		}
		else
		{
			$params = JModel::getInstance('Storage', 'AdmintoolsModel');
		}

		// Add the new master password
		$params->setValue('masterpassword', $masterPassword);

		// Add the protected views
		if (!in_array('masterpw', $protectedViews))
		{
			$protectedViews[] = 'masterpw';
		}
		$params->setValue('lockedviews', implode(',', $protectedViews));

		$params->save();
	}

	/**
	 * Get a list of the views which can be locked down and their lockdown status
	 *
	 * @return array
	 */
	public function &getItemList($overrideLimits = false, $group = '')
	{
		$lockedViews = array();

		if (interface_exists('JModel'))
		{
			$params = JModelLegacy::getInstance('Storage', 'AdmintoolsModel');
		}
		else
		{
			$params = JModel::getInstance('Storage', 'AdmintoolsModel');
		}
		$lockedviews_raw = $params->getValue('lockedviews', '');
		if (!empty($lockedviews_raw))
		{
			$lockedViews = explode(",", $lockedviews_raw);
		}

		$views = array();
		foreach ($this->views as $view)
		{
			$views[$view] = in_array($view, $lockedViews);
		}

		return $views;
	}

	public function getPagination()
	{
		return null;
	}

	/**
	 * Returns the stored master password
	 *
	 * @return string
	 */
	public function getMasterPassword()
	{
		if (interface_exists('JModel'))
		{
			$params = JModelLegacy::getInstance('Storage', 'AdmintoolsModel');
		}
		else
		{
			$params = JModel::getInstance('Storage', 'AdmintoolsModel');
		}

		return $params->getValue('masterpassword', '');
	}
}