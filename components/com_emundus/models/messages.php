<?php
/**
 * Messages model used for the new message dialog.
 *
 * @package    Joomla
 * @subpackage eMundus
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class EmundusModelMessages extends JModelList {

    /**
     * Constructor
     *
     * @since 3.8.6
     */
    public function __construct() {

    }

    function getAllMessages($type = 2) {

		$db = JFactory::getDbo();

        try {

            $query = 'SELECT * FROM #__emundus_setup_emails WHERE type IN ('.$db->Quote($type).') AND published=1';
            $db->setQuery($query);
            return $db->loadObjectList();

        } catch (Exception $e) {
            JLog::add('Error getting emails in model/messages at query : '.$query, JLog::ERROR, 'com_emundus');
            return false;
        }

	}

	function getAllCategories($type = 2) {

        $db = JFactory::getDbo();

        try {

            $query = 'SELECT DISTINCT(category) FROM #__emundus_setup_emails WHERE type IN ('.$db->Quote($type).') AND published=1';
            $db->setQuery($query);
            return $db->loadColumn();

        } catch (Exception $e) {
            JLog::add('Error getting email categories in model/messages at query : '.$query, JLog::ERROR, 'com_emundus');
            return false;
        }

	}

	function getTemplate() {

		$db = JFactory::getDBO();
		$select = JRequest::getVar('select', null, 'POST', 'none', 0);
		$query = 'SELECT * FROM #__emundus_setup_emails WHERE id='.$select;
		$db->setQuery($query);
		$email = $db->loadObject();
		echo json_encode((object)(array('status' => true, 'tmpl' => $email)));
		die();

	}


}

?>