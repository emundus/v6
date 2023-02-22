<?php
/**
 * @package	eMundus
 * @version	6.6.5
 * @author	eMundus.fr
 * @copyright (C) 2018 eMundus SOFTWARE. All rights reserved.
 * @license	GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );


class plgSystemEmundus_conditional_redirect extends JPlugin {

	function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
		$this->loadLanguage();

        JLog::addLogger(['text_file' => 'plugins.emundus_conditional_redirect.php'], JLog::ALL, 'plugins.emundus_conditional_redirect');

        if (!isset($this->params)) {
			$plugin = JPluginHelper::getPlugin('system', 'emundus_conditional_redirect');
			$this->params = new JRegistry(@$plugin->params);
		}
	}


    function onAfterRender() {
		$app = JFactory::getApplication();

		if ($app->isAdmin() || JFactory::getUser()->guest) {
			return true;
		}

        $code_php = $this->params->get('condition');
        $menu_item_id = $this->params->get('redirection_url');
        $redirection_url = JRoute::_('index.php?Itemid='.$menu_item_id);

        if (!empty($code_php) && !empty($redirection_url)) {
            $unimpacted_urls = $this->params->get('list_unimpacted_urls');
            $unimpacted_urls = json_decode($unimpacted_urls, true);
            $menu = $app->getMenu();
            $current_menu = $menu->getActive();

            if ($current_menu->id == $menu_item_id || in_array($current_menu->id, $unimpacted_urls['unimpacted_url'])) {
                // User on selected redirection url, no need to run code
            } else {
                $code_response = true;
                try {
                    $code_response = eval($code_php);
                } catch (Exception $e) {
                    JLog::add('Failed to evaluate condition for redirection ' . $e->getMessage(), JLog::ERROR, 'plugins.emundus_conditional_redirect');
                    $code_response = true;
                }

                if ($code_response === false) {
                    $redirection_message = JText::_($this->params->get('redirection_message'));
                    if (!empty($redirection_message)) {
                        $app->enqueueMessage($redirection_message, 'info');
                    }

                    $app->redirect($redirection_url);
                }
            }
        }

		return true;
	}
}
