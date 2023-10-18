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
class plgHikashopUser_account extends JPlugin {

	function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
		if(isset($this->params))
			return;

		$plugin = JPluginHelper::getPlugin('hikashop', 'user_account');
		$this->params = new JRegistry($plugin->params);
	}

	function onUserAccountDisplay(&$buttons){
		$force_itemid = $this->params->get('itemid');

		global $Itemid;
		$url_itemid = '';
		if(!empty($force_itemid)) {
			$url_itemid = '&Itemid='.$force_itemid;
		} else {
			if(!empty($Itemid)) {
				$url_itemid = '&Itemid='.$Itemid;
			}
		}
		$url = JRoute::_('index.php?option=com_users&view=profile&layout=edit'.$url_itemid);

		$my = array(
			'joomla_user' => array(
				'link' => $url,
				'level' => 0,
				'image' => 'user2',
				'text' => JText::_('CUSTOMER_ACCOUNT'),
				'description' => JText::_('EDIT_INFOS'),
				'fontawesome' => ''.
					'<i class="far fa-file-alt fa-stack-2x"></i>'.
					'<i class="fas fa-circle fa-stack-1x fa-inverse" style="left:30%;top:30%"></i>'.
					'<i class="fas fa-user-circle fa-stack-1x" style="left:30%;top:30%"></i>'
			)
		);
		$buttons = array_merge($my, $buttons);

		$redirect = $this->params->get('redirect_back_on_profile_save');
		if($redirect){
			$app = JFactory::getApplication();
			$app->setUserState('com_users.edit.profile.redirect','index.php?option=com_hikashop&ctrl=user&task=cpanel&Itemid='.$Itemid);
		}
		return true;
	}

}
