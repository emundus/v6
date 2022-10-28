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
class documentationMarketController extends hikashopBridgeController {
	const name = 'DOCUMENTATION';
	const ctrl = 'documentation';
	const icon = 'life-ring';

	protected $rights = array(
		'display' => array('listing'),
		'add' => array(),
		'edit' => array(),
		'modify' => array(),
		'delete' => array()
	);

	public function __construct($config = array()) {
		parent::__construct($config);
		$this->registerDefaultTask('listing');
	}

	function listing() {
		hikamarket::setTitle(JText::_(self::name), self::icon, self::ctrl);

		$bar = JToolBar::getInstance('toolbar');
		$bar->appendButton('Link', HIKAMARKET_LNAME, JText::_('HIKASHOP_CPANEL'), hikamarket::completeLink('dashboard'));
		$config = hikamarket::config();
		$level = $config->get('level');
		$url = HIKAMARKET_HELPURL.'documentation&level='.$level;
		echo '<div id="hikamarket_div"><iframe allowtransparency="true" scrolling="auto" height="450px" frameborder="0" width="100%" name="hikamarket_frame" id="hikamarket_frame" src="'.$url.'"></iframe></div>';
	}
}
