<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$app = JFactory::getApplication();
$app->enqueueMessage( JText::_('THANK_YOU_FOR_PURCHASE') );
$user = JFactory::getUser();
if(!$user->guest){
	$url = hikashop_completeLink('order&task=show&cid='.$this->order->order_id);
	$app->enqueueMessage(JText::sprintf('YOU_CAN_NOW_ACCESS_YOUR_ORDER_HERE',$url));
}
