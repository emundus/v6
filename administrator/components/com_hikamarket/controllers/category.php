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
class categoryMarketController extends hikamarketController {
	protected $type = 'category';

	protected $rights = array(
		'display' => array('gettree'),
		'add' => array(),
		'edit' => array(),
		'modify' => array(),
		'delete' => array()
	);

	public function getTree() {
		$category_id = hikaInput::get()->getInt('category_id', 0);
		$displayFormat = hikaInput::get()->getString('displayFormat', '');
		$search = hikaInput::get()->getVar('search', null);

		$nameboxType = hikamarket::get('type.namebox');
		$options = array(
			'start' => $category_id,
			'displayFormat' => $displayFormat
		);
		$ret = $nameboxType->getValues($search, 'category', $options);
		if(!empty($ret)) {
			echo json_encode($ret);
			exit;
		}
		echo '[]';
		exit;
	}

	public function findList() {
		$search = hikaInput::get()->getVar('search', '');
		$start = hikaInput::get()->getInt('start', 0);
		$type = hikaInput::get()->getString('category_type', '');
		$displayFormat = hikaInput::get()->getString('displayFormat', '');

		$types = array(
			'manufacturer' => 'brand',
			'order_status' => 'order_status'
		);
		if(!isset($types[$type])) {
			echo '[]';
			exit;
		}
		$type = $types[$type];
		$options = array();

		if(!empty($displayFormat))
			$options['displayFormat'] = $displayFormat;
		if($start > 0)
			$options['page'] = $start;

		$nameboxType = hikamarket::get('type.namebox');
		$elements = $nameboxType->getValues($search, $type, $options);
		echo json_encode($elements);
		exit;
	}
}
