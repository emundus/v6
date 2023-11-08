<?php
/**
 * @version        $Id: query.php 14401 2010-01-26 14:10:00Z guillossou $
 * @package        Joomla
 * @subpackage     Emundus
 * @copyright      Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license        GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');

/**
 * Content Component Query Helper
 *
 * @static
 * @package        Joomla
 * @subpackage     Content
 * @since          1.5
 */
class EmundusHelperModule
{
	static function getParams($moduleid)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		try {
			$query->select('params')
				->from($db->quoteName('#__modules'))
				->where($db->quoteName('id') . ' = ' . $db->quote($moduleid))
				->andWhere($db->quoteName('published') . ' = 1');
			$db->setQuery($query);

			return json_decode($db->loadResult());
		}
		catch (Exception $e) {
			JLog::add('Problem to get params of module ' . $moduleid . ' : ' . $e->getMessage(), JLog::ERROR, 'com_emundus.');

			return array();
		}
	}
}
