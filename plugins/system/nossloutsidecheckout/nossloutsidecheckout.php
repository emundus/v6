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
class plgSystemNossloutsidecheckout extends JPlugin
{
	function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('system', 'nossloutsidecheckout');
			$this->params = new JRegistry(@$plugin->params);
		}
	}


	function onAfterRoute(){
		$app = JFactory::getApplication();

		if(version_compare(JVERSION,'4.0','>=') && $app->isClient('administrator'))
			return true;
		if(version_compare(JVERSION,'4.0','<') && $app->isAdmin())
			return true;

		if (@$_REQUEST['tmpl']=='component') return true;

		if(empty($_REQUEST['ctrl'])) $_REQUEST['ctrl'] = @$_REQUEST['view'];
		if(empty($_REQUEST['task'])) $_REQUEST['task'] = @$_REQUEST['layout'];
		if(@$_REQUEST['option']=='com_hikashop' && (@$_REQUEST['ctrl']=='checkout' || @$_REQUEST['ctrl']=='order' && @$_REQUEST['task']=='pay')){
			return true;
		}
		if(@$_REQUEST['option']=='com_ccidealplatform' && @$_REQUEST['task']=='bankform'){
			return true;
		}
		if(!empty($_POST)){
			return true;
		}

		if (!defined('DS'))
			define('DS', DIRECTORY_SEPARATOR);
		if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')) return true;

		if (hikashop_isSSL()){
			$app->setUserState('com_hikashop.ssl_redirect',0);
			$status = $this->params->get('status','');
			$status = ($status=='301'?true:false);
			$app->redirect(str_replace('https://','http://',hikashop_currentURL()));
		}

		return true;
	}
}
