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
		$token = $this->getToken($params->get('translator_bingkey'));
		
		$script = "var AzureToken = '".$token."';\n";

		$document = JFactory::getDocument();
		$document->addScriptDeclaration($script,'text/javascript');
		
		$this->script = 'translatorBing.js';
	}
	
	/*
	 * Get the access token.
	 *
	 * @param string $azure_key    Subscription key for Text Translation API.
	 *
	 * @return string.
	 * 
	 * See https://github.com/MicrosoftTranslator/HTTP-Code-Samples
	 */
	private function getToken($azure_key)
	{
		$url = 'https://api.cognitive.microsoft.com/sts/v1.0/issueToken';
		$ch = curl_init();
		$data_string = json_encode('{body}');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($data_string),
				'Ocp-Apim-Subscription-Key: ' . $azure_key
		)
				);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$strResponse = curl_exec($ch);
		curl_close($ch);
		return $strResponse;
	}}