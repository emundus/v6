<?php
// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

class TranslatorYandex extends TranslatorDefault {
	
	function __construct()
	{
		$params = JComponentHelper::getParams('com_falang');
		$token = $params->get('translator_yandexkey');
		if (strlen($token) < 20){
			JFactory::getApplication()->enqueueMessage(JText::_('COM_FALANG_INVALID_YANDEX_KEY'), 'error');
			return;
		}
		
		$script = "var YandexKey = '".$token."';\n";

		$document = JFactory::getDocument();
		$document->addScriptDeclaration($script,'text/javascript');
		
		$this->script = 'translatorYandex.js';
	}
}