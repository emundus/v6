<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(empty($this->vendor))
	return;

hikaInput::get()->set('hikashop_front_end_main', 1);

if(empty($this->vendor_layout) || (substr($this->vendor_layout, 0, 14) != 'showcontainer_' && substr($this->vendor_layout, 0, 7) != 'layout:'))
	$this->vendor_layout = 'showcontainer_default';

if(substr($this->vendor_layout, 0, 14) == 'showcontainer_') {
	$this->setLayout($this->vendor_layout);
	echo $this->loadTemplate();
	return;
}
