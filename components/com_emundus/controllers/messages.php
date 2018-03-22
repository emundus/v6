<?php
/**
 * Messages controller used for the creation and emission of messages from the platform.
 *
 * @package    Joomla
 * @subpackage Emundus
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Hugo Moracchini
*/

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * eMundus Component Controller
 *
 * @package    Joomla.eMundus
 * @subpackage Components
 */
class EmundusControllerMessages extends JControllerLegacy {

    /**
     * Constructor
     *
     * @since 3.8.6
     */
    function __construct($config = array()) {
        parent::__construct($config);
    }

    /**
     * Upload a file from computer to be attached to the emails sent.
     */
    public function uploadfiletosend() {

        $jinput = JFactory::getApplication()->input;

        // Get the file sent via AJAX POST
        $file = $jinput->files->get('file');

        // Check if the message attachements directory exists.
        if (!is_dir('tmp'.DS.'messageattachements')) {
            mkdir('tmp'.DS.'messageattachements', 0777, true);
        }

        // Move the uploaded file to the server directory.
        $target = 'tmp'.DS.'messageattachements'.DS.$file['name'];

        if (file_exists($target))
            unlink($target);

        move_uploaded_file($file['tmp_name'], $target);

        // Send back the info to the frontend.
        echo json_encode(['status' => true, 'file_name' => $file['name'], 'file_path' => $target]);
        exit;

    }

}