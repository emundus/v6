<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(empty($this->html)) {
	echo JText::_('THANK_YOU_FOR_PURCHASE');
	if(!empty($this->url))
		echo '<br/>'.JText::sprintf('YOU_CAN_NOW_ACCESS_YOUR_ORDER_HERE', $this->url);
} else {
	echo $this->html;
}
$this->nextButton = false;
