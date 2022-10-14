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

class dashboardMarketController extends hikamarketController {
	protected $rights = array(
		'display' => array('listing','cpanel'),
		'add' => array(),
		'edit' => array(),
		'modify' => array(),
		'delete' => array()
	);

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function cpanel() {
		hikaInput::get()->set('layout', 'cpanel');
		return $this->display();
	}
}
