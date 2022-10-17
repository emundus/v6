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
class hikamarketToolbarClass extends hikamarketClass {

	protected $tables = array();
	protected $pkeys = array();
	protected $toggle = array();

	public function processView(&$view) {
		if(empty($view->toolbar))
			return;

		if(!empty($view->ctrl))
			$ctrl = $view->ctrl;
		else
			$ctrl = hikaInput::get()->getCmd('ctrl', '');
		$task = $view->getLayout();

		if($ctrl == 'order' && ($task == 'form' || $task == 'show') && !empty($view->order) && !empty($view->order->order_parent_id)) {
			$order_id = $view->order->order_id;
			$order_parent_id = $view->order->order_parent_id;
			array_unshift(
				$view->toolbar,
				array(
					'name' => 'link',
					'url' => hikamarket::completeLink('shop.order&task=edit&cid='.$order_parent_id),
					'icon' => 'tree-2',
					'alt' => JText::_('HIKAM_PARENT_ORDER')
				)
			);
		}
	}
}
