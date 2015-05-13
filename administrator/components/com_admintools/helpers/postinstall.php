<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

/**
 * Should I show the Joomla! Update notification email activation message?
 *
 * @return bool
 */
function com_admintools_postinstall_autojupdate_condition()
{
	$db = JFactory::getDbo();

	$query = $db->getQuery(true)
		->select($db->qn('enabled'))
		->from($db->qn('#__extensions'))
		->where($db->qn('element') . ' = ' . $db->q('atoolsjupdatecheck'))
		->where($db->qn('folder') . ' = ' . $db->q('system'));
	$db->setQuery($query);

	$enabledAutoJoomlaUpdateEmail = $db->loadResult();

	return !$enabledAutoJoomlaUpdateEmail;
}

/**
 * Activate the Joomla! Update notification email feature
 */
function com_admintools_postinstall_autojupdate_action()
{
	$db = JFactory::getDBO();

	$query = $db->getQuery(true)
		->update($db->qn('#__extensions'))
		->set($db->qn('enabled') . ' = ' . $db->q('1'))
		->where($db->qn('element') . ' = ' . $db->q('atoolsjupdatecheck'))
		->where($db->qn('folder') . ' = ' . $db->q('system'));
	$db->setQuery($query);
	$db->execute();
}