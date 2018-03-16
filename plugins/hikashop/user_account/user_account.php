<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2018 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class plgHikashopUser_account extends JPlugin {

	function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
		if(isset($this->params))
			return;

		$plugin = JPluginHelper::getPlugin('hikashop', 'user_account');
		if(version_compare(JVERSION,'2.5','<')){
			jimport('joomla.html.parameter');
			$this->params = new JParameter($plugin->params);
		} else {
			$this->params = new JRegistry($plugin->params);
		}
	}

	function onUserAccountDisplay(&$buttons){
		$force_itemid = $this->params->get('itemid');

		$url_itemid = '';
		if(!empty($force_itemid)) {
			$url_itemid = '&Itemid='.$force_itemid;
		} else {
			global $Itemid;
			if(!empty($Itemid)) {
				$url_itemid = '&Itemid='.$Itemid;
			}
		}
		if(version_compare(JVERSION, '1.6', '<')) {
			$url = JRoute::_('index.php?option=com_user&view=user&task=edit'.$url_itemid);
		} else {
			$url = JRoute::_('index.php?option=com_users&view=profile&layout=edit'.$url_itemid);
		}

		$my = array(
			'joomla_user' => array(
				'link' => $url,
				'level' => 0,
				'image' => 'user2',
				'text' => JText::_('CUSTOMER_ACCOUNT'),
				'description' => JText::_('EDIT_INFOS')
			)
		);
		$buttons = array_merge($my, $buttons);

		$redirect = $this->params->get('redirect_back_on_profile_save');
		if($redirect){
			$app = JFactory::getApplication();
			$app->setUserState('com_users.edit.profile.redirect',hikashop_currentURL());
		}
		return true;
	}

}
