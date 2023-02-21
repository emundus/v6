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

		if (!isset($this->params)) {
			$plugin = JPluginHelper::getPlugin('system', 'emundus_conditional_redirect');
			$this->params = new JRegistry(@$plugin->params);
		}
	}


    function onAfterInitialise() {
		$app = JFactory::getApplication();

		if ($app->isAdmin() || JFactory::getUser()->guest) {
			return true;
		}

        $code_php = $this->params->get('condition');
        $redirection_url = $this->params->get('redirection_url');
        if (!empty($code_php) && !empty($redirection_url)) {
            $unimpacted_urls = $this->params->get('list_unimpacted_urls');
            $unimpacted_urls = json_decode($unimpacted_urls, true);

            $current_uri = JUri::getInstance();
            $path = $current_uri->getPath();
            $absoulte_url = $current_uri->render();
            if ($path == $redirection_url || $absoulte_url == $redirection_url || !empty(array_intersect([$path, $absoulte_url], $unimpacted_urls['unimpacted_url']))) {
                // User on selected redirection url, no need to run code
            } else {
                $code_response = eval($code_php);

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
