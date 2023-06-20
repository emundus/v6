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
class FilterController extends hikashopController{
	var $type='filter';
	var $pkey = 'filter_id';
	var $table = 'filter';
	var $orderingMap ='filter_ordering';

	public function __construct($config = array(), $skip = false) {
		parent::__construct($config, $skip);
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
		$elements = $nameboxType->getValues($search, 'filter', $options);
		echo json_encode($elements);
		exit;
	}
}
