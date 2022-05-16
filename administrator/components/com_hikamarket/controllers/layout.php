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

class layoutMarketController extends hikamarketController {

	protected $type = 'layout';
	protected $toggle = array('layout_published' => 'layout_id');
	protected $rights = array(
		'display' => array('display', 'show', 'listing'),
		'add' => array('add'),
		'edit' => array('edit', 'toggle', 'publish', 'unpublish'),
		'modify' => array('save', 'apply'),
		'delete' => array('delete')
	);

	public function __construct($config = array())	{
		parent::__construct($config);
		$this->registerDefaultTask('listing');
	}

	public function store() {
		return parent::adminStore();
	}
}
