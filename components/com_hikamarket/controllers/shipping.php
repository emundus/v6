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
class shippingMarketController extends hikamarketController {
	protected $rights = array(
		'display' => array(),
		'add' => array(),
		'edit' => array('toggle'),
		'modify' => array(),
		'delete' => array('delete')
	);
	protected $type = 'shipping';
	protected $config = null;

	public function __construct($config = array(), $skip = false) {
		parent::__construct($config, $skip);
		$this->config = hikamarket::config();
	}

	public function authorize($task) {
		if(!in_array($task, array('toggle', 'delete')))
			return parent::authorize($task);

		$completeTask = hikaInput::get()->getCmd('task', '');
		$value = hikaInput::get()->getCmd('value', '');
		if(strrpos($completeTask, '-') !== false)
			$plugin_id = (int)substr($completeTask, strrpos($completeTask, '-') + 1);
		else
			$plugin_id = (int)substr($value, 0, strpos($value, '-'));

		if(!hikamarket::loginVendor())
			return false;
		if(!$this->config->get('frontend_edition',0))
			return false;
		if(!JSession::checkToken('request'))
			return false;
		if($task == 'toggle' && !hikamarket::acl('shippingplugin/edit/published'))
			return false;
		if($task == 'delete' && !hikamarket::acl('shippingplugin/delete'))
			return false;
		if(!hikamarket::isVendorPlugin($plugin_id, 'shipping'))
			return false;
		return true;
	}
}
