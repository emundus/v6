<?php

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');
jimport('joomla.user.helper');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

require_once JPATH_ROOT . '/components/com_emundus/helpers/files.php';

class EmundusControllerComments extends JControllerLegacy
{
    private $user;
    private $app;

    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->app = Factory::getApplication();
        $this->user = Factory::getUser();
    }

    private function sendJsonResponse($response)
    {
        if ($response['code'] === 403) {
            header('HTTP/1.1 403 Forbidden');
            echo $response['message'];
            exit;
        } else if ($response['code'] === 500) {
            header('HTTP/1.1 500 Internal Server Error');
            echo $response['message'];
            exit;
        }

        echo json_encode($response);
        exit;
    }

    public function getcomments()
    {
        $response = ['status' => false, 'code' => 403, 'message' => Text::_('ACCESS_DENIED')];
        $ccid = $this->app->input->getInt('ccid', 0);

        if (!empty($ccid)) {
            $response['code'] = 500;
            $fnum = EmundusHelperFiles::getFnumFromId($ccid);

            if (EmundusHelperAccess::asAccessAction(10, 'r', $this->user->id, $fnum)) {
                $response['code'] = 200;
                $model = $this->getModel('comments');
                $response['data'] = $model->getComments($ccid, $this->user->id);
                $response['status'] = true;
            }
        }

        $this->sendJsonResponse($response);
    }
}