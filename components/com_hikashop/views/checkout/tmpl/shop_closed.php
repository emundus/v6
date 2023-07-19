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
if(empty($this->messages))
	return;

foreach($this->messages as $msg) {
	if(is_array($msg))
		hikashop_display($msg[0], $msg[1]);
	else
		hikashop_display($msg);
}
