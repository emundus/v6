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
class dashboardmarketViewdashboardmarket extends hikamarketView {

	const ctrl = 'dashboard';
	const name = HIKAMARKET_NAME;
	const icon = 'store';

	public function display($tpl = null, $params = null) {
		$this->paramBase = HIKAMARKET_COMPONENT.'.'.$this->getName();
		$fct = $this->getLayout();
		if(method_exists($this, $fct))
			$this->$fct($params);
		parent::display($tpl);
	}

	public function listing() {
		hikamarket::setTitle(JText::_(self::name), self::icon, self::ctrl);

		$statisticsClass = hikamarket::get('class.statistics');
		$statistics = $statisticsClass->getDashboard();

		$statistics_slots = array();
		foreach($statistics as $key => &$stat) {
			$slot = (int)@$stat['slot'];
			$stat['slot'] = $slot;
			$stat['key'] = $key;
			$statistics_slots[ $slot ] = $slot;
		}
		unset($stat);
		asort($statistics_slots);

		$this->assignRef('statisticsClass', $statisticsClass);
		$this->assignRef('statistics', $statistics);
		$this->assignRef('statistics_slots', $statistics_slots);

		$config = hikamarket::config();
		$use_approval = $config->get('product_approval', 0);

		$buttons = array(
			'vendors' => array(
				'name' => JText::_('HIKA_VENDORS'),
				'url' => hikamarket::completeLink('vendor'),
				'icon' => 'fas fa-user-tie'
			),
			'approval' => array(
				'name' => JText::_('WAITING_APPROVAL_LIST'),
				'url' => hikamarket::completeLink('product&task=waitingapproval'),
				'icon' => 'fas fa-thumbs-up'
			),
			'plugins' => array(
				'name' => JText::_('PLUGINS'),
				'url' => hikamarket::completeLink('plugins'),
				'icon' => 'fas fa-puzzle-piece'
			),
			'config' => array(
				'name' => JText::_('HIKA_CONFIGURATION'),
				'url' => hikamarket::completeLink('config'),
				'icon' => 'fas fa-wrench'
			),
			'acl' => array(
				'name' => JText::_('HIKAM_ACL'),
				'url' => hikamarket::completeLink('config&task=acl'),
				'icon' => 'fas fa-unlock-alt'
			),
			'update' => array(
				'name' => JText::_('UPDATE_ABOUT'),
				'url' => hikamarket::completeLink('update'),
				'icon' => 'fas fa-sync'
			),
			'help' => array(
				'name' => JText::_('HIKA_HELP'),
				'url' => hikamarket::completeLink('documentation'),
				'icon' => 'fas fa-life-ring'
			)
		);
		if(!$use_approval)
			unset($buttons['approval']);

		$this->assignRef('buttons', $buttons);

		if(JFactory::getUser()->authorise('core.admin', 'com_hikamarket')) {
			$this->toolbar[] = array('name' => 'preferences', 'component' => 'com_hikamarket');
		}
		$this->toolbar[] = array('name' => 'pophelp', 'target' => 'welcome');
	}
}
