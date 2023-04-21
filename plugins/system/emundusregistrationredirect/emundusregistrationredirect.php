<?php
/**
 * @package	eMundus
 * @version	6.6.5
 * @author	eMundus.fr
 * @copyright (C) 2018 eMundus SOFTWARE. All rights reserved.
 * @license	GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

class plgSystemEmundusregistrationredirect extends JPlugin {

	function __construct(&$subject, $config) {

		parent::__construct($subject, $config);
		$this->loadLanguage();

		if (!isset($this->params)) {
			$plugin = JPluginHelper::getPlugin('system', 'emundusregistrationredirect');
			$this->params = new JRegistry(@$plugin->params);
		}
	}


	function onAfterRoute() {

		$app = JFactory::getApplication();
		$jinput = $app->input;

		if ($app->isAdmin()) {
			return true;
		}

		if (($jinput->get('option', '') == 'com_user' && $jinput->get('view', '') == 'register') || ($jinput->get('option', '') == 'com_users' && $jinput->get('view', '') == 'registration')) {

			if (!defined('DS')) {
				define('DS', DIRECTORY_SEPARATOR);
			}

			// Load params
			$Itemid = $this->params->get('item_id');
			$url = $this->params->get('url_to_registration');

			// If the itemID is not found in params, look for it elsewhere.
			if (empty($Itemid)) {
				global $Itemid;
			}

			// If the URL to registration is not found, don't redirect.
			if (empty($url)) {
				return false;
			}

			// By using a translation tag we can get a separate SEF URL for english or french.
			$url = JText::_($url);

			// Add the itemID we want to the URL.
			$url_itemid = '';
			if (!empty($Itemid)) {
				$url_itemid .= '&Itemid='.$Itemid;
			}

			// In case the URL set in the params already contains an item id, we need to remove it so we can append our own.
			$parsed_url = parse_url($url);
			parse_str($parsed_url['query'], $params);
			if (!empty($params['Itemid'])) {
				unset($params['Itemid']);
				$url = http_build_query($params);
			}

			// Redirect! :)
			$app->redirect($url.$url_itemid, false);
		}

        if(!JFactory::getUser()->guest) {
            $e_session = JFactory::getSession()->get('emundusUser');
            if(empty($e_session)){
                include_once(JPATH_SITE.'/components/com_emundus/models/profile.php');
                $m_profile = new EmundusModelProfile();
                $m_profile->initEmundusSession();
            }
        }

		return true;
	}

}
