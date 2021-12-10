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
class plgHikashopMarket_vendorselectfield extends JPlugin
{
	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
	}

	public function onFieldsLoad(&$fields, &$options) {
		$me = new stdClass();
		$me->name = 'market_vendorselectfield';
		$me->text = JText::_('VENDOR_SELECTION');
		$me->options = array('required', 'default', 'columnname', 'pleaseselect', 'market_vendorselect_type');

		$fields[] = $me;

		$opt = new stdClass();
		$opt->name = 'market_vendorselect_type';
		$opt->text = JText::_('VENDOR_SELECTION_TYPE');
		$opt->obj = 'fieldOpt_market_vendorselect_type';

		$options[$opt->name] = $opt;
	}
}

if(defined('HIKASHOP_COMPONENT')) {
	require_once( dirname(__FILE__).DS.'market_vendorselectfield_class.php' );
}
