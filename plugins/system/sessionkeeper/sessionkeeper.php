<?php

/**
 * @package plugin SessionKeeper
 * @copyright (C) 2010-2012 RicheyWeb - www.richeyweb.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * SessionKeeper Copyright (c) 2011 Michael Richey.
 * SessionKeeper is licensed under the http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 *
 * SessionKeeper version 1.2 for Joomla 1.6.x devloped by RicheyWeb
 *
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

/**
 * SessionKeeper system plugin
 */
class plgSystemSessionKeeper extends JPlugin {
	
	private $_modal = false;

	function onBeforeCompileHead() {
		$doc = JFactory::getDocument();
		$user = JFactory::getUser();
		if (!$user->id || $doc->getType() != 'html') {
			return;
		}
		if(count(array_intersect($user->groups,$this->params->get('usergroups',array())))) {
		    JHtml::_('behavior.keepalive');
		    return;
		}
		if($this->params->get('showwarning',1)) {
			$this->_setWarning();
			if ($this->params->get('messagetype', 'js') === 'modal' && $this->params->get('styletoggle', 1))
			{
				$doc->addStyleDeclaration($this->params->get('modalstyle', ''));
			}
		}
	}
	
	function onAfterRender() {
		if(!$this->_modal) {
			return;
		}
		$app = JFactory::getApplication();
		$warning = $this->_setupWarningModal();
		$expired = $this->_setupExpiredModal();
		$body = $app->getBody();
		$app->setBody(str_replace('</body>',$warning.$expired.'</body>',$body));		
	}

	private function _getRedirect() {
		$app = JFactory::getApplication();
		$menu = $app->getMenu();
		$redirect = $app->isAdmin() ? '' : $this->params->get('redirectitemid', false);
		if (is_numeric($redirect))
		{
			$item = $menu->getItem($redirect);
			return JRoute::_('index.php?' . $item->link . '&Itemid=' . $redirect, false);
		}
		return false;
	}

	private function _setWarning() {
		$app = JFactory::getApplication();
		JHtml::_('jquery.framework',true);
		$globalconfig = JFactory::getConfig();
		$doc = JFactory::getDocument();
		$lang = JFactory::getLanguage();
		$lang->load('plg_system_sessionkeeper', JPATH_ADMINISTRATOR);
		$debug = $globalconfig->get('debug',false);
		$config = array('timeout' => (int) $globalconfig->get('lifetime'));
		$config['warning'] = (int) $this->params->get('advancewarning', 2);
		$config['messagetype'] = $this->params->get('messagetype', 'js');
                if($app->isAdmin() && $config['messagetype'] !== 'js') {
                    $config['messagetype'] = 'modal';
                }
		$config['redirect'] = $this->_getRedirect();
		$config['strings'] = array('CONSOLE_INACTIVE'=>JText::_('PLG_SYS_SESSIONKEEPER_CONSOLE_INACTIVE'));
		JText::script('PLG_SYS_SESSIONKEEPER_CONSOLE_INACTIVE',true,true);
		switch($config['messagetype']) {
			case 'modal':
				$this->_modal = true;
				break;
			case 'js':
				JText::script('PLG_SYS_SESSIONKEEPER_JSWARNINGMESSAGE',true,true);
				JText::script('PLG_SYS_SESSIONKEEPER_EXPIREDMESSAGE',true,true);
				$config['strings']['JSWARNINGMESSAGE'] = JText::_('PLG_SYS_SESSIONKEEPER_JSWARNINGMESSAGE');
				$config['strings']['EXPIREDMESSAGE'] = JText::_('PLG_SYS_SESSIONKEEPER_EXPIREDMESSAGE');
				break;
			case 'event':
				JText::script('PLG_SYS_SESSIONKEEPER_WARNINGMESSAGE',true,true);
				JText::script('PLG_SYS_SESSIONKEEPER_EXPIREDMESSAGE',true,true);
				JText::script('PLG_SYS_SESSIONKEEPER_ABANDON',true,true);
				JText::script('PLG_SYS_SESSIONKEEPER_RESCUE',true,true);
				JText::script('PLG_SYS_SESSIONKEEPER_MODALTITLE',true,true);
				$config['strings']['MODALTITLE'] = JText::_('PLG_SYS_SESSIONKEEPER_MODALTITLE');
				$config['strings']['RESCUE'] = JText::_('PLG_SYS_SESSIONKEEPER_RESCUE');
				$config['strings']['ABANDON'] = JText::_('PLG_SYS_SESSIONKEEPER_ABANDON');
				$config['strings']['EXPIREDMESSAGE'] = JText::_('PLG_SYS_SESSIONKEEPER_EXPIREDMESSAGE');
				$config['strings']['WARNINGMESSAGE'] = JText::_('PLG_SYS_SESSIONKEEPER_WARNINGMESSAGE');
				break;
		}
		$doc->addScript('https://www.promisejs.org/polyfills/promise-7.0.4'.($debug?'':'.min').'.js');
		$doc->addScript(JURI::root(true) . '/media/plg_system_sessionkeeper/sessionkeeper'.($debug?'':'.min').'.js',array('version'=>'auto'));
		$doc->addScriptOptions('plg_system_sessionkeeper_config', $config);
	}

	private function _setupWarningModal() {
		$agreebutton = '<button class="plg_system_sessionkeeper_rescue btn btn-success">' . JText::_('PLG_SYS_SESSIONKEEPER_RESCUE') . '</button>';
		$declinebutton = '<button class="btn btn-danger" data-dismiss="modal">' . JText::_('PLG_SYS_SESSIONKEEPER_ABANDON') . '</button>';
		$hms = '<span class="hms pull-left label label-important"></span>';
		$modaloptions = array(
			'title' => JText::_('PLG_SYS_SESSIONKEEPER_MODALTITLE'),
			'backdrop' => 'static',
			'keyboard' => false,
			'closeButton' => false,
			'footer' => $hms . $agreebutton . $declinebutton
		);
		$modalbody = '<p style="margin:0 20px;">' . JText::_('PLG_SYS_SESSIONKEEPER_WARNINGMESSAGE') . '</p>';
		$modalbody.= '<div class="progress progress-danger progress-striped active"><div class="bar" style="width: 100%"></div></div>';
		return JHtml::_('bootstrap.renderModal', 'sessionKeeperWarning', $modaloptions, $modalbody);
	}

	private function _setupExpiredModal() {
		$modaloptions = array(
			'title' => JText::_('PLG_SYS_SESSIONKEEPER_MODALTITLE'),
			'backdrop' => 'true',
			'keyboard' => true,
			'closeButton' => true
		);
		$modalbody = '<p>' . JText::_('PLG_SYS_SESSIONKEEPER_MODALEXPIREDTITLE') . '</p>';
		return JHtml::_('bootstrap.renderModal', 'sessionKeeperExpired', $modaloptions, $modalbody);
	}

}
