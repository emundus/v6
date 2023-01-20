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
		$refresh = JFactory::getApplication()->input->getString('refresh',false);


		$files_session = unserialize(JFactory::getSession()->get('files'));
		if($files_session instanceof Files){
			$this->files = $files_session;
		}

		if(empty($this->files)) {
			if ($type == 'evaluation') {
				$this->files = new Evaluations();
			}
			else {
				$this->files = new Files();
			}
		}

		if($refresh == true) {
			$this->files->setFiles();
		}

	    JFactory::getSession()->set('files',serialize($this->files));

        parent::__construct($config);
    }

    public function getfiles(){
        $results = ['status' => 1, 'msg' => '', 'data' => [], 'total' => 0];

        if(EmundusHelperAccess::asAccessAction(1,'r',JFactory::getUser()->id)){
            $results['data'] = $this->files->getFiles();
            $results['total'] = $this->files->getTotal();
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

	public function getlimit(){
		$results = ['status' => 1, 'msg' => '', 'data' => []];

		if(EmundusHelperAccess::asAccessAction(1,'r',JFactory::getUser()->id)){
			$results['data'] = $this->files->getLimit();
		} else {
			$results['status'] = 0;
			$results['msg'] = JText::_('ACCESS_DENIED');
		}

		echo json_encode((object)$results);
		exit;
	}

	public function getpage(){
		$results = ['status' => 1, 'msg' => '', 'data' => []];

		if(EmundusHelperAccess::asAccessAction(1,'r',JFactory::getUser()->id)){
			$results['data'] = $this->files->getPage();
		} else {
			$results['status'] = 0;
			$results['msg'] = JText::_('ACCESS_DENIED');
		}

		echo json_encode((object)$results);
		exit;
	}

    public function getevaluationformbyfnum(){
        $results = ['status' => 1, 'msg' => '', 'data' => []];

        if(EmundusHelperAccess::asAccessAction(5,'r',JFactory::getUser()->id)){
            $fnum = JFactory::getApplication()->input->getString('fnum',null);

            $results['data'] = $this->files->getEvaluationFormByFnum($fnum);
        } else {
            $results['status'] = 0;
            $results['msg'] = JText::_('ACCESS_DENIED');
        }

        echo json_encode((object)$results);
        exit;
    }

	public function checkaccess(){
		$results = ['status' => 1, 'msg' => '', 'data' => []];

		if(EmundusHelperAccess::asAccessAction(5,'r',JFactory::getUser()->id)){
			$fnum = JFactory::getApplication()->input->getString('fnum',null);

			$results['data'] = $this->files->checkAccess($fnum);
		} else {
			$results['status'] = 0;
			$results['msg'] = JText::_('ACCESS_DENIED');
		}

		echo json_encode((object)$results);
		exit;
	}

	public function updatelimit(){
		$results = ['status' => 1, 'msg' => ''];

		if(EmundusHelperAccess::asAccessAction(5,'r',JFactory::getUser()->id)){
			$limit = JFactory::getApplication()->input->getInt('limit',5);

			$this->files->setLimit($limit);

			JFactory::getSession()->set('files',serialize($this->files));
		} else {
			$results['status'] = 0;
			$results['msg'] = JText::_('ACCESS_DENIED');
		}

		echo json_encode((object)$results);
		exit;
	}

	public function updatepage(){
		$results = ['status' => 1, 'msg' => ''];

		if(EmundusHelperAccess::asAccessAction(5,'r',JFactory::getUser()->id)){
			$page = JFactory::getApplication()->input->getInt('page',0);

			$this->files->setPage($page);

			JFactory::getSession()->set('files',serialize($this->files));
		} else {
			$results['status'] = 0;
			$results['msg'] = JText::_('ACCESS_DENIED');
		}

		echo json_encode((object)$results);
		exit;
	}
}