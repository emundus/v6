<?php
/**
 * Plugin element to render fields
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.field
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use GuzzleHttp\Client as GuzzleClient;

jimport('joomla.application.component.model');

/**
 * Plugin element to render fields
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.field
 * @since       3.0
 */
class PlgFabrik_ElementInsee extends PlgFabrik_Element
{

	/**
	 * Shows the data formatted for the list view
	 *
	 * @param   string    $data      Elements data
	 * @param   stdClass  &$thisRow  All the data in the lists current row
	 * @param   array     $opts      Rendering options
	 *
	 * @return  string	formatted value
	 */
	public function renderListData($data, stdClass &$thisRow, $opts = array())
	{
		return parent::renderListData($data, $thisRow, $opts);
	}

	public function preRenderElement($data, $repeatCounter = 0)
	{
		$groupModel = $this->getGroupModel();

		if (!$this->canView() && !$this->canUse())
		{
			return '';
		}
		// Used for working out if the element should behave as if it was in a new form (joined grouped) even when editing a record
		$this->inRepeatGroup        = $groupModel->canRepeat();
		$this->_inJoin              = $groupModel->isJoin();
		$opts                       = array('runplugins' => 1);
		$formatedInputValueBack     = $this->getValue($data, $repeatCounter, $opts);

		if ($this->isEditable())
		{
			return $this->render($data, $repeatCounter);
		}
		else
		{
			$htmlId = $this->getHTMLId($repeatCounter);
			return '<div class="fabrikElementReadOnly" id="' . $htmlId . '">' . $formatedInputValueBack. '</div>';
		}
	}

	/**
	 * Draws the html form element
	 *
	 * @param   array  $data           To pre-populate element with
	 * @param   int    $repeatCounter  Repeat group counter
	 *
	 * @return  string	elements html
	 */
	public function render($data, $repeatCounter = 0)
	{
		$bits = $this->inputProperties($repeatCounter);
		$bits['value'] = $this->getValue($data, $repeatCounter);
		$bits['mustValidate'] = $this->validator->hasValidations(); // is the element mandatory ?

		$layout = $this->getLayout('form');
		$layoutData = new stdClass;
		$layoutData->attributes = $bits;

		return $layout->render($layoutData);
	}

	/**
	 * Determines the value for the element in the form view
	 *
	 * @param   array  $data           Form data
	 * @param   int    $repeatCounter  When repeating joined groups we need to know what part of the array to access
	 * @param   array  $opts           Options, 'raw' = 1/0 use raw value
	 *
	 * @return  string	value
	 */
	public function getValue($data, $repeatCounter = 0, $opts = array())
	{
		$value = parent::getValue($data, $repeatCounter, $opts);

		if (is_array($value))
		{
			return array_pop($value);
		}

		return $value;
	}

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 *
	 * @param   int  $repeatCounter  Repeat group counter
	 *
	 * @return  array
	 */
	public function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);

		$params = $this->getParams();
		$config = JComponentHelper::getParams('com_emundus');

		$opts->bearerToken = $this->getBearerToken();
		$opts->baseUrl = trim($config->get('insee_api_base_url', $params->get('insee_api_base_url', 'https://api.insee.fr')));
		$opts->mapping = $params->get('form_mapping', array());
		$opts->propertyToCheck = $params->get('insee_property_to_check', 'siret');

		return array('FbInsee', $id, $opts);
	}

	/**
	 * Manipulates posted form data for insertion into database
	 *
	 * @param   mixed  $val   This elements posted form data
	 * @param   array  $data  Posted form data
	 *
	 * @return  mixed
	 */
	public function storeDatabaseFormat($val, $data)
	{
		return $val;
	}

	public function getBearerToken()
	{
		$response = ['status' => 200, 'message' => '', 'data' => JFactory::getSession()->get('insee_bearer_token','')];
		
		if(empty($response['data']))
		{
			$params = $this->getParams();
			$config = JComponentHelper::getParams('com_emundus');

			$baseUrl = trim($config->get('insee_api_base_url', $params->get('insee_api_base_url', 'https://api.insee.fr')));
			$consumerKey = trim($config->get('insee_api_consumer_key', $params->get('insee_api_consumer_key', '')));
			$consumerSecret = trim($config->get('insee_api_consumer_secret', $params->get('insee_api_consumer_secret', '')));

			if(!empty($baseUrl) && !empty($consumerKey) && !empty($consumerSecret))
			{
				$client  = new GuzzleClient([
					'base_uri' => $baseUrl,
					'verify'   => false
				]);
				$headers = [
					'Authorization' => 'Basic ' . base64_encode($consumerKey . ':' . $consumerSecret),
					'Content-Type'  => 'application/x-www-form-urlencoded',
				];
				$options = [
					'form_params' => [
						'grant_type' => 'client_credentials'
					]
				];

				try
				{
					$request = $client->post('/token', ['headers' => $headers, 'form_params' => $options['form_params']]);

					$response['status'] = $request->getStatusCode();
					if($response['status'] == 200)
					{
						$response['data'] = json_decode($request->getBody());
						$response['data'] = $response['data']->token_type . ' ' . $response['data']->access_token;
						
						JFactory::getSession()->set('insee_bearer_token', $response['data']);
					}
				}
				catch (\Exception $e)
				{
					JLog::add('[POST] ' . $e->getMessage(), JLog::ERROR, 'com_emundus.api');
					$response['status']  = $e->getCode();
					$response['message'] = $e->getMessage();
				}
			} else {
				$response['status']  = 500;
				$response['message'] = 'Missing configuration';
			}
		}

		return $response;
	}
}
