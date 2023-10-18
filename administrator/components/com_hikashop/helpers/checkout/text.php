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
include_once HIKASHOP_HELPER . 'checkout.php';

class hikashopCheckoutTextHelper extends hikashopCheckoutHelperInterface {
	protected $params = array(
		'text' => array(
			'name' => 'CONTENT',
			'type' => 'textarea',
			'default' => ''
		),
	);
	public function getParams() {
		$this->params['text']['attributes'] = 'rows="3" cols="30" placeholder="'.htmlentities(JText::_('WRITE_TEXT_HTML_HERE'), ENT_COMPAT, 'UTF-8').'"';
		return parent::getParams();
	}

	public function display(&$view, &$params) {
		if(empty($params['text'])) {
			$params['text'] = '';
			return;
		}

		$params['text'] = JHTML::_('content.prepare', hikashop_translate($params['text']));
	}

	public function haveEmptyContent(&$controller, &$params) {
		if(empty($params['text']))
			return true;
		$text = JHTML::_('content.prepare', hikashop_translate($params['text']));
		if(empty($text))
			return true;
		return false;

	}
}
