<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

class plgHikashopEmail_history extends JPlugin {

	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
	}

	private function init() {
		if(class_exists('hikashopPlg_email_historyClass'))
			return true;
		$file = dirname(__FILE__) . DS . 'email_history_class.php';
		if(file_exists($file))
			include_once $file;
		return class_exists('hikashopPlg_email_historyClass');
	}

	public function onBeforeMailSend(&$mail, &$mailer) {
		if(!$this->init())
			return false;
		$emailHistoryClass = new hikashopPlg_email_historyClass();
		$emailHistoryClass->beforeMailSend($mail, $mailer);
	}

	public function onHikashopBeforeCheckDB(&$createTable, &$custom_fields, &$structure, &$helper) {
		if(!$this->init())
			return;
		$emailHistoryClass = new hikashopPlg_email_historyClass();
		$emailHistoryClass->beforeCheckDB($createTable, $custom_fields, $structure, $helper);
	}

	public function onHikashopPluginController($ctrl) {
		if($ctrl != 'email_history')
			return;

		$app = JFactory::getApplication();
		if(!hikashop_isClient('administrator'))
			return;

		if(!$this->init())
			return;

		return array(
			'type' => 'hikashop',
			'name' => 'email_history',
			'prefix' => 'ctrl'
		);
	}

	public function onHikashopBeforeDisplayView(&$viewObj) {
		$app = JFactory::getApplication();
		if(!hikashop_isClient('administrator'))
			return;

		$viewName = $viewObj->getName();

		if(!in_array($viewName, array('menu', 'config')))
			return;
		switch($viewName) {
			case 'menu':
				return $this->hikashopProcessMenu($viewObj);
			case 'config':
				if($viewObj->getLayout() == 'config')
					return $this->hikashopProcessConfig($viewObj);
				return;
		}
	}
	private function hikashopProcessConfig(&$view) {
		if(empty($view->aclcats))
			return;

		$view->aclcats['email_log'] = array('view','manage','delete');
		$view->acltrans['email_log'] = 'email_history';
	}

	private function hikashopProcessMenu(&$view) {
		if(empty($view->menus))
			return;

		$view->menus['customers']['children'][] = array(
			'name' => JText::_('EMAIL_HISTORY'),
			'check' => 'ctrl=email_history',
			'icon' => 'fa fa-envelope',
			'url' => hikashop_completeLink('email_history'),
			'acl' => 'email_log',
		);
	}
}
