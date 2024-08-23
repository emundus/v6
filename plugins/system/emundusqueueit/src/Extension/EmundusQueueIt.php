<?php

/**
 * @package     Joomla.Plugins
 * @subpackage  System.actionlogs
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Emundus\Plugin\System\EmundusQueueIt\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use QueueIT\KnownUserV3\SDK\KnownUser;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Users Actions Logging Plugin.
 *
 * @since  3.9.0
 */
final class EmundusQueueIt extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @since  3.9.0
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @since  3.9.0
	 */
	protected $db;

	/**
	 * Load plugin language file automatically so that it can be used inside component
	 *
	 * @var    boolean
	 * @since  3.9.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array    $config   An optional associative array of configuration settings.
	 *
	 * @since   3.9.0
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		if(!empty($this->params->get('json_config', ''))) {
			$configText = $this->params->get('json_config', '');
		}

		$customerID = $this->params->get('customer_id', ''); //Your Queue-it customer ID
		$secretKey = $this->params->get('secret_key', ''); //Your 72 char secret key as specified in Go Queue-it self-service platform

		$queueittoken = isset( $_GET["queueittoken"] )? $_GET["queueittoken"] :'';

		if(!empty($customerID) && !empty($secretKey) && !empty($configText))
		{
			try
			{
				$fullUrl                       = self::getFullRequestUri();
				$currentUrlWithoutQueueitToken = preg_replace("/([\\?&])(" . "queueittoken" . "=[^&]*)/i", "", $fullUrl);

				//Verify if the user has been through the queue
				$result = KnownUser::validateRequestByIntegrationConfig(
					$currentUrlWithoutQueueitToken, $queueittoken, $configText, $customerID, $secretKey);

				if ($result->doRedirect())
				{
					//Adding no cache headers to prevent browsers to cache requests
					header("Expires:Fri, 01 Jan 1990 00:00:00 GMT");
					header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
					header("Pragma: no-cache");
					//end

					if (!$result->isAjaxResult)
					{
						//Send the user to the queue - either because hash was missing or because is was invalid
						header('Location: ' . $result->redirectUrl);
					}
					else
					{
						header('HTTP/1.0: 200');
						header($result->getAjaxQueueRedirectHeaderKey() . ': ' . $result->getAjaxRedirectUrl());
						header("Access-Control-Expose-Headers" . ': ' . $result->getAjaxQueueRedirectHeaderKey());
					}

					die();
				}
				if (!empty($queueittoken) && $result->actionType == "Queue")
				{
					//Request can continue - we remove queueittoken form querystring parameter to avoid sharing of user specific token
					header('Location: ' . $currentUrlWithoutQueueitToken);
					die();
				}
			}
			catch (\Exception $e)
			{
				// There was an error validating the request
				// Use your own logging framework to log the error
				// This was a configuration error, so we let the user continue
				Log::add('Queue-it', $e->getMessage(), Log::ERROR, 'com.emundusqueueit');
			}
		}
	}

	private static function getFullRequestUri()
	{
		// Get HTTP/HTTPS (the possible values for this vary from server to server)
		$myUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && !in_array(strtolower($_SERVER['HTTPS']),array('off','no'))) ? 'https' : 'http';
		// Get domain portion
		$myUrl .= '://'.$_SERVER['HTTP_HOST'];
		// Get path to script
		$myUrl .= $_SERVER['REQUEST_URI'];
		// Add path info, if any
		if (!empty($_SERVER['PATH_INFO'])) $myUrl .= $_SERVER['PATH_INFO'];

		return $myUrl;
	}
}
