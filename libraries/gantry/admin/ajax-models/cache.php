<?php
/**
 * @version   $Id: cache.php 30234 2016-03-30 07:30:17Z matias $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('JPATH_BASE') or die();

/** @var $gantry Gantry */
global $gantry;

$action = JFactory::getApplication()->input->getString('action');


switch ($action) {
	case 'clear':
		echo gantryAjaxClearGantryCache();
		break;
	default:
		echo "error";
}

function gantryAjaxClearGantryCache()
{
	/** @var $gantry Gantry */
	global $gantry;
	$admincache = GantryCache::getCache(GantryCache::ADMIN_GROUP_NAME, null, true);
	$admincache->clearGroupCache();
	$sitecache = GantryCache::getCache(GantryCache::GROUP_NAME, null, true);
	$sitecache->getCacheLib()->getDriver()->getCache()->cache->_options['cachebase'] = JPATH_ROOT.'/cache';
	$sitecache->clearGroupCache();
	$sitelesscache = GantryCache::getCache('GantryLess', null, true);
	$sitelesscache->getCacheLib()->getDriver()->getCache()->cache->_options['cachebase'] = JPATH_ROOT.'/cache';
	$sitelesscache->clearGroupCache();
	return JText::_('Gantry caches cleared.');
}
