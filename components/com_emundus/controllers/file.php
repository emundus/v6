<?php
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');
jimport( 'joomla.user.helper' );

class EmundusControllerFile extends JControllerLegacy
{
    public function __construct($config = array())
    {
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'classes'.DS.'files'.DS.'Evaluations.php');

        parent::__construct($config);
    }

    public function getfilestoevaluate(){
        $results = ['status' => 1, 'msg' => '', 'data' => []];

        if(EmundusHelperAccess::asAccessAction(1,'r',JFactory::getUser()->id)){
            $evaluations = new Evaluations();
            $evaluations->setFiles();

            $results['data'] = $evaluations->getFiles();
        } else {
            $results['status'] = 0;
            $results['msg'] = 'ACCESS_DENIED';
        }

        echo json_encode((object)$results);
        exit;
    }
}