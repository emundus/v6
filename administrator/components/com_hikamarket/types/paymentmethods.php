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
class hikamarketPaymentmethodsType {

	protected $values = array();

	protected function loadBackend($vendor_id) {
		if(!empty($this->values[$vendor_id]))
			return;

		$db = JFactory::getDBO();

		$this->values[$vendor_id] = array( 0 => array('type' => 'manual', 'name' => 'Manual') );

		$payments = array('paypal');
		foreach($payments as &$p) {
			$p = $db->Quote($p);
			unset($p);
		}
		$query = 'SELECT payment_id, payment_type, payment_name FROM ' . hikamarket::table('shop.payment') . ' WHERE payment_type IN (' . implode(',', $payments).')';

		$db->setQuery($query);
		$methods = $db->loadObjectList();
		foreach($methods as $m) {
			$this->values[$vendor_id][$m->payment_id] = array(
				'type' => $m->payment_type,
				'name' => $m->payment_name . ' ('.$m->payment_type.' / '.$m->payment_id.')'
			);
		}
	}

	protected function loadFrontend($form, $value = '', $vendor_id = 0) {
		if(!empty($this->values['front']))
			return;

		$this->values['front'] = array();

		$pluginsClass = hikashop_get('class.plugins');
		$methods = $pluginsClass->getMethods('payment');

		if(empty($methods))
			return;

		foreach($methods as $m) {
			if(isset($m->enabled) && !$m->enabled && $m->payment_id != $value)
				continue;
			if(isset($m->payment_published) && !$m->payment_published && $m->payment_id != $value)
				continue;
			if(isset($m->payment_vendor_id) && (int)$m->payment_vendor_id > 0 && (int)$m->payment_vendor_id != $vendor_id)
				continue;
			$this->values['front'][$m->payment_id] = $m->payment_name;
		}
	}

	public function get($vendor_id, $payment_id) {
		$this->loadBackend($vendor_id);
		if(!empty($this->values[$vendor_id][$payment_id]))
			return $this->values[$vendor_id][$payment_id]['type'];
		return null;
	}

	public function display($map, $values, $options = array()) {
		if(is_string($options))
			$options = array('attribute' => $options);

		$attribute = 'size="1"';
		if(isset($options['attribute']))
			$attribute = implode(' ', $options['attribute']);
		$vendor_id = 0;
		if(isset($options['vendor_id']))
			$vendor_id = (int)$options['vendor_id'];
		$form = false;
		if(isset($options['form']))
			$form = (int)$options['form'];

		$app = JFactory::getApplication();
		if(hikamarket::isAdmin())
			return $this->displayBackend($vendor_id, $map, $values);

		$this->loadFrontend($form, $values, $vendor_id);
		$items = array();
		if(!$form) {
			$attribute .= ' onchange="document.adminForm.submit();"';
			$items[] = JHTML::_('select.option', '', JText::_('ALL_PAYMENT_METHODS') );
		}
		foreach($this->values['front'] as $key => $text) {
			$items[] = JHTML::_('select.option', $key, $text);
		}
		return JHTML::_('select.genericlist', $items, $map, 'class="inputbox" '.$attribute, 'value', 'text', $values);
	}

	private function displayBackend($vendor_id, $map, $values, $attribute = 'size="1"') {
		$this->loadBackend($vendor_id);
		$items = array();
		foreach($this->values[$vendor_id] as $key => $text) {
			$items[] = JHTML::_('select.option', $key, $text['name']);
		}
		return JHTML::_('select.genericlist', $items, $map, 'class="inputbox" '.$attribute, 'value', 'text', $values);
	}
}
