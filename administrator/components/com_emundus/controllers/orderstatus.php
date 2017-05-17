<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
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


	public function findList() {
		$search = JRequest::getVar('search', '');
		$start = JRequest::getInt('start', 0);
		$displayFormat = JRequest::getVar('displayFormat', '');

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
