<?php
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');
jimport( 'joomla.user.helper' );

use \classes\files\files;

class EmundusControllerFile extends JControllerLegacy
{
	private $files;

    public function __construct($config = array())
    {
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'classes'.DS.'files'.DS.'Files.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'classes'.DS.'files'.DS.'Evaluations.php');
		
		$type = JFactory::getApplication()->input->getString('type','default');


		$files_session = unserialize(JFactory::getSession()->get('files'));
		if($files_session instanceof Files){
			$this->files = $files_session;
		}

		if(empty($this->files)){
			if($type == 'evaluation') {
				$this->files = new Evaluations();
			} else {
				$this->files = new Files();
			}

			$this->files->setFiles();

			JFactory::getSession()->set('files',serialize($this->files));
		}

        parent::__construct($config);
    }

    public function getfiles(){
        $results = ['status' => 1, 'msg' => '', 'data' => []];

        if(EmundusHelperAccess::asAccessAction(1,'r',JFactory::getUser()->id)){
            $results['data'] = $this->files->getFiles();
        } else {
            $results['status'] = 0;
            $results['msg'] = JText::_('ACCESS_DENIED');
        }

        echo json_encode((object)$results);
        exit;
    }

	public function getcolumns(){
		$results = ['status' => 1, 'msg' => '', 'data' => []];

		if(EmundusHelperAccess::asAccessAction(1,'r',JFactory::getUser()->id)){
			$results['data'] = $this->files->getColumns();
		} else {
			$results['status'] = 0;
			$results['msg'] = JText::_('ACCESS_DENIED');
		}

		echo json_encode((object)$results);
		exit;
	}
}