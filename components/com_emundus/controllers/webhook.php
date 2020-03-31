<?php
/**
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @copyright  eMundus
 * @author     Benjamin Rivalland
 * @since      3.9.16
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * eMundus Component Controller
 *
 * @package    Joomla
 * @subpackage Components
 */

class EmundusControllerWebhook extends JControllerLegacy {

	public function __construct(array $config = array()) {

		JLog::addLogger(
		    array(
		         // Sets file name
		         'text_file' => 'com_emundus.webhook.php'
		    ),
		    // Sets messages of all log levels to be sent to the file.
		    JLog::ALL,
		    // The log category/categories which should be recorded in this file.
		    // In this case, it's just the one category from our extension.
		    // We still need to put it inside an array.
		    array('com_emundus')
		);

		parent::__construct($config);
	}

	/**
	 * Gets video info from addpipe webhook
	 *
	 * @return bool|string
	 */
	public function addpipe() {

		try {
			$payload = $_POST["payload"];
			JLog::add('Webhook START: '.$payload, JLog::WARNING, 'com_emundus');
	
	        //the data is JSON encoded, so we must decode it in an associative array
	        $webhookData = json_decode($payload, true);

	        //you can get the webhook type by accessing the event element in the array
	        $type = $webhookData["event"];

	        //if you wish to get the name of the video you simply access it like this
	        $vidName = $webhookData["data"]["videoName"];

        //JLog::add($webhookData, JLog::ERROR, 'com_emundus.upload_video');
		} catch (Exception $e) {
			JLog::add('Unable to handle addpipe webhook : '.$payload, JLog::ERROR, 'com_emundus');
			exit;
		}

	}
	
}