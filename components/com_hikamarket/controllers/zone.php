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
class zoneMarketController extends hikamarketController {
	protected $rights = array(
		'display' => array('gettree'),
		'add' => array(),
		'edit' => array(),
		'modify' => array(),
		'delete' => array()
	);

	protected $type = 'zone';
	protected $config = null;

	public function __construct($config = array(), $skip = false) {
		parent::__construct($config, $skip);
		$this->config = hikamarket::config();
	}

	public function getTree() {
		while(ob_get_level())
			@ob_end_clean();

		if(!hikamarket::loginVendor() || !$this->config->get('frontend_edition',0)) {
			echo '[]';
			exit;
		}

		$zone_key = hikaInput::get()->getString('zone_key', null);
		$displayFormat = hikaInput::get()->getString('displayFormat', '');
		$search = hikaInput::get()->getString('search', null);

		$nameboxType = hikamarket::get('type.namebox');
		$options = array(
			'zone_key' => $zone_key,
			'displayFormat' => $displayFormat
		);
		$ret = $nameboxType->getValues($search, 'zone', $options);
		if(!empty($ret)) {
			echo json_encode($ret);
			exit;
		}
		echo '[]';
		exit;
	}
}
