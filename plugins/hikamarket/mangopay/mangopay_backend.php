<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

include_once dirname(__FILE__) . DS . 'mangopay_class.php';

class mangopayMarketController extends hikamarketController {
	protected $rights = array(
		'display' => array(),
		'add' => array(),
		'edit' => array(),
		'modify' => array(),
		'delete' => array()
	);
	protected $pluginCtrl = array('hikamarket', 'mangopay');
	protected $type = 'plg_mangopay';
}
