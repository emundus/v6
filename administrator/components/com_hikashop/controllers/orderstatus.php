<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class OrderstatusController extends hikashopController {
	var $type = 'orderstatus';
	var $pkey = 'orderstatus_id';
	var $table = 'orderstatus';
	var $groupMap = '';
	var $orderingMap = 'orderstatus_ordering';
	var $groupVal = 0;


	function __construct() {
		parent::__construct();
		$this->display[] = 'findList';
	}

	public function findList() {
		$search = hikaInput::get()->getVar('search', '');
		$start = hikaInput::get()->getInt('start', 0);
		$displayFormat = hikaInput::get()->getVar('displayFormat', '');

		$options = array();

		if(!empty($displayFormat))
			$options['displayFormat'] = $displayFormat;
		if($start > 0)
			$options['page'] = $start;

		$nameboxType = hikashop_get('type.namebox');
		$elements = $nameboxType->getValues($search, 'order_status', $options);
		echo json_encode($elements);
		exit;
	}
}
