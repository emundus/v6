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
class hikamarketAclClass extends hikamarketClass {
	protected $tables = array();
	protected $pkeys = array();
	protected $toggle = array();

	public function saveForm() {
		$config = hikamarket::config();
		$formData = hikaInput::get()->get('data', array(), 'array');
		$market_type = hikaInput::get()->getCmd('acl_type', '');

		if(empty($market_type))
			return false;

		$data = array();

		if(!empty($formData['acl'])) {
			foreach($formData['acl'] as $group => $d) {
				$data_array = explode(',', $d);
				foreach($data_array as &$d) {
					$d = trim($d);
				}
				unset($d);
				sort($data_array, SORT_STRING);

				$data[$market_type.'_acl_'.$group] = implode(',', $data_array);
			}
		} else if($market_type == 'vendor_options') {
			foreach($formData as $group => $options) {
				if(!is_array($options))
					continue;
				$data_array = array();
				foreach($options as $k => $v) {
					if(!empty($v))
						$data_array[$k] = $v;
				}
				$data[$market_type.'_acl_'.$group] = serialize($data_array);
			}
		}

		$status = false;
		if(!empty($data))
			$status = $config->save($data);

		return $status;
	}
}
