<?php
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

class TranslatorBing extends TranslatorDefault {
	
	function __construct()
	{
		$params = JComponentHelper::getParams('com_falang');
		if (strlen($params->get('translator_bingkey')) < 20){
			JFactory::getApplication()->enqueueMessage(JText::_('COM_FALANG_INVALID_BING_KEY'), 'error');
			return;
		}

		if(!function_exists('curl_init')) {
			JFactory::getApplication()->enqueueMessage(JText::_('COM_FALANG_CURL_BING_MESSAGE'), 'error');
			return;
		}

		//region non necessary for global endpoint
		$script = "var azureKey = '".$params->get('translator_bingkey')."';\n";

		$document = JFactory::getDocument();
		$document->addScriptDeclaration($script,'text/javascript');
		
		$this->script = 'translatorBing.js';
	}

}