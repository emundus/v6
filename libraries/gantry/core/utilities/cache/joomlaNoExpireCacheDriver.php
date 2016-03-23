<?php
/**
 * @version   $Id: joomlaNoExpireCacheDriver.php 2325 2012-08-13 17:46:48Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class JoomlaNoExpireCacheDriver extends JoomlaCacheDriver
{
	public function __construct($groupName)
	{
		$this->cache = JFactory::getCache($groupName, 'output');
		$handler     = 'output';
		$options     = array(
			'storage'      => 'file',
			'defaultgroup' => $groupName,
			'locking'      => true,
			'locktime'     => 15,
			'checkTime'    => false,
			'caching'      => true
		);
		$this->cache = JCache::getInstance($handler, $options);
	}
}
