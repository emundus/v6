<?php
/**
 * @version   $Id: example.php 2752 2012-08-27 17:22:29Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('GANTRY_VERSION') or die();
/** @var $gantry Gantry */
		global $gantry;

/*
	new Request({
		url: '/index.php?option=com_gantry&task=ajax&format=raw&template=gantry_site'
	}).post({
		model: 'example'
	});
*/
print_r(JRequest::get('post'));
