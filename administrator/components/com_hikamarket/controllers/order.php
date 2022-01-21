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
class orderMarketController extends hikamarketController {
	protected $type = 'order';

	protected $rights = array(
		'display' => array('show', 'checkstatus'),
		'add' => array(),
		'edit' => array(),
		'modify' => array(),
		'delete' => array()
	);

	public function show() {
		hikaInput::get()->set('layout', 'show_order_back_show');

		$tmpl = hikaInput::get()->getString('tmpl', '');
		if($tmpl === 'component') {
			ob_end_clean();
			parent::display();
			exit;
		}
		return parent::display();
	}

	public function checkstatus() {
		hikamarket::headerNoCache();

		$order_id = hikamarket::getCID('order_id');
		$order_status = '';

		if(!empty($order_id)) {
			$orderClass = hikamarket::get('class.order');
			$order = $orderClass->getRaw($order_id);
			$order_status = $order->order_status;
		}

		$tmpl = hikaInput::get()->getString('tmpl', '');
		if($tmpl === 'component' || $tmpl == 'json') {
			ob_end_clean();
			echo $order_status;
			exit;
		}
		echo $order_status;
		return false;
	}
}
