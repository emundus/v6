<?php
/**
 * Created by PhpStorm.
 * User: James Dean
 * Date: 2018-12-27
 * Time: 12:02
 */


// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

use Joomla\CMS\Factory;

/**
 * eMundus Component Controller
 *
 * @package    Joomla
 * @subpackage Components
 */

class EmundusControllerFormations extends JControllerLegacy {

    protected $app;
    private $user;


    public function __construct(array $config = array()) {

        require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'logs.php');
        require_once(JPATH_SITE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'formations.php');

        // Load class variables
	    $this->app = Factory::getApplication();
        $this->user = $this->app->getSession()->get('emundusUser');

        parent::__construct($config);
    }


    public function deletecompany() {


        $id = $this->input->post->get('id', null);

        $m_formations = $this->getModel('Formations');

        $isHR = $m_formations->checkHR($id, $this->user->id);
        if (!empty($isHR)) {
            $m_formations->deleteCompany($id);
            
            echo json_encode((object)[
                'status' => true
            ]);
        }

        exit;
    }


    public function deleteassociate() {

        $id = $this->input->post->get('id', null);
        $cid = $this->input->post->get('cid', null);

        $m_formations = $this->getModel('Formations');

        echo json_encode((object)[
            'status' => $m_formations->deleteAssociate($id, $cid, $this->user->id)
        ]);
        exit;

    }


}

