<?php
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class TranslatorGoogle extends TranslatorDefault {

    function __construct()
    {
        $params = JComponentHelper::getParams('com_falang');
        if (strlen($params->get('translator_googlekey')) < 20){
            Factory::getApplication()->enqueueMessage(Text::_('COM_FALANG_INVALID_GOOGLE_KEY'), 'error');
            return;
        }

        if(!function_exists('curl_init')) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_FALANG_CURL_GOOGLE_MESSAGE'), 'error');
            return;
        }

        //region non necessary for global endpoint
        $script = "var googleKey = '".$params->get('translator_googlekey')."';\n";

        $document = Factory::getDocument();
        $document->addScriptDeclaration($script,'text/javascript');

        $this->script = 'translatorGoogle.js';
    }

}