<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')) {
	echo 'This module can not work without the Hikashop Component';
	return;
};

$moduleclass_sfx = $params->get('moduleclass_sfx','');

$statisticsClass = hikashop_get('class.statistics');
$statistics = $statisticsClass->getDashboard('joomla_dashboard');

$statistics_slots = array();
foreach($statistics as $key => &$stat) {
	$slot = (int)@$stat['slot'];
	$stat['slot'] = $slot;
	$stat['key'] = $key;
	$statistics_slots[ $slot ] = $slot;
}
unset($stat);
asort($statistics_slots);

require(JModuleHelper::getLayoutPath('mod_hikashop_dashboard','default'));
