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
class hikamarketDropdownType {

	public function __construct() {
		$this->app = JFactory::getApplication();
	}

	protected function getPlugins() {
		static $plugins = null;
		if($plugins !== null)
			return $plugins;
		$plugins = array();
		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikamarket');
		JFactory::getApplication()->triggerEvent('onHkInterfaceDropdownList', array( &$plugins ));
		return $plugins;
	}

	public function display($name, $data, $options = array()) {
	}

	public function displayBootstrap($name, $data, $options = array()) {
		$styles = '';
		$btnClasses = '';
		$name = '';
		$elements = array();

		if(!empty($btnClasses))
			$btnClasses = ' ' . trim($btnClasses);

		foreach($data as $d) {
			if($d == '-') {
				$elements[] = '<li class="divider"></li>';
				continue;
			}
			$elements[] = '<li><a href=""></a></li>';
		}

		return '<div class="btn-group" style="'.$styles.'">' .
			'<button style="margin:0px;" class="dropdown-toggle btn'.$btnClasses.'" data-toggle="dropdown">' . $name . ' <span class="caret"></span></button>'.
			'<ul class="dropdown-menu">'.implode('', $elements).'</ul>'.
			'</div>';
	}

	public function displayInternal($name, $data, $options = array()) {
	}
}
