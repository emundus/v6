<?php
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');
jimport( 'joomla.user.helper' );

use \classes\files\files;
use Joomla\CMS\Factory;

class EmundusControllerFile extends JControllerLegacy
{
	protected $app;

	private $_user;
	private $type;
	private $files;

    public function __construct($config = array())
    {
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'classes'.DS.'files'.DS.'Files.php');
        require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'classes'.DS.'files'.DS.'Evaluations.php');

		$this->app = Factory::getApplication();
		$this->_user = $this->app->getIdentity();
		$this->type = $this->input->getString('type','default');
		$refresh = $this->input->getString('refresh',false);


		$files_session = unserialize($this->app->getSession()->get('files'));
		if($files_session instanceof Files){
			$this->files = $files_session;
		}

		if(empty($this->files)) {
			if ($this->type == 'evaluation') {
				$this->files = new Evaluations();
			}
			else {
				$this->files = new Files();
			}
		}

		if(empty($this->files->getTotal()) || $refresh == true) {
			try {
				$this->files->setFiles();
			} catch (Exception $e) {
				if ($e->getMessage() === 'COM_EMUNDUS_ERROR_NO_EVALUATION_GROUP') {
					echo json_encode(['status' => false, 'msg' => JText::_($e->getMessage())]);
					exit;
				}
			}
		}

	    $this->app->getSession()->set('files', serialize($this->files));

        parent::__construct($config);
    }

    public function getfiles(){
        $results = ['status' => false, 'msg' => JText::_('ACCESS_DENIED')];

        if (EmundusHelperAccess::asAccessAction(1,'r', JFactory::getUser()->id)){
            $results['data'] = $this->files->getFiles();
            $results['total'] = $this->files->getTotal();
	        $results['status'] = true;
	        $results['msg'] = '';

	        if ($this->type == 'evaluation'){
				$results['all'] = $this->files->getAll();
				$results['to_evaluate'] = $this->files->getToEvaluate();
				$results['evaluated'] = $this->files->getEvaluated();
	        }
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

        if(EmundusHelperAccess::asAccessAction(5,'r',JFactory::getUser()->id) || EmundusHelperAccess::asAccessAction(5,'c', JFactory::getUser()->id)){
            $fnum = $this->input->getString('fnum',null);

            $results['data'] = $this->files->getEvaluationFormByFnum($fnum);
        } else {
            $results['status'] = 0;
            $results['msg'] = JText::_('ACCESS_DENIED');
        }

        echo json_encode((object)$results);
        exit;
    }

	public function getmyevaluation(){
		$results = ['status' => 1, 'msg' => '', 'data' => []];

		if(EmundusHelperAccess::asAccessAction(5,'r',JFactory::getUser()->id) || EmundusHelperAccess::asAccessAction(5,'c', JFactory::getUser()->id)){
			$fnum = $this->input->getString('fnum',null);

			$results['data'] = $this->files->getMyEvaluation($fnum);
		} else {
			$results['status'] = 0;
			$results['msg'] = JText::_('ACCESS_DENIED');
		}

		echo json_encode((object)$results);
		exit;
	}

	public function checkaccess(){
		$results = ['status' => 0, 'msg' => '', 'data' => []];

		if(EmundusHelperAccess::asAccessAction(5,'r',JFactory::getUser()->id) || EmundusHelperAccess::asAccessAction(5,'c', JFactory::getUser()->id)){
			$fnum = $this->input->getString('fnum',null);

			$results['status'] = $this->files->checkAccess($fnum);
			$results['data'] = $this->files->getAccess($fnum);
		} else {
			$results['msg'] = JText::_('ACCESS_DENIED');
		}

		echo json_encode((object)$results);
		exit;
	}

	public function getfile(){
		$results = ['status' => 1, 'msg' => '', 'data' => [],'rights' => []];

		if(EmundusHelperAccess::asAccessAction(5,'r',JFactory::getUser()->id) || EmundusHelperAccess::asAccessAction(5,'c', JFactory::getUser()->id)){
			$fnum = $this->input->getString('fnum',null);

			$access = $this->files->checkAccess($fnum);
			if($access){
				$results['data']  = $this->files->getFile($fnum);
				$results['rights']  = $this->files->getAccess($fnum);
			} else {
				$results['status'] = 0;
			}
		} else {
			$results['status'] = 0;
			$results['msg'] = JText::_('ACCESS_DENIED');
		}

		echo json_encode((object)$results);
		exit;
	}

	public function updatelimit(){
		$results = ['status' => 1, 'msg' => ''];

		if(EmundusHelperAccess::asAccessAction(5,'r',JFactory::getUser()->id) || EmundusHelperAccess::asAccessAction(5,'c', JFactory::getUser()->id)){
			$limit = $this->input->getInt('limit',5);

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

		if(EmundusHelperAccess::asAccessAction(5,'r',JFactory::getUser()->id) || EmundusHelperAccess::asAccessAction(5,'c', JFactory::getUser()->id)){
			$page = $this->input->getInt('page',0);

			$this->files->setPage($page);

			JFactory::getSession()->set('files',serialize($this->files));
		} else {
			$results['status'] = 0;
			$results['msg'] = JText::_('ACCESS_DENIED');
		}

		echo json_encode((object)$results);
		exit;
	}

	public function getselectedtab(){
		$results = ['status' => 1, 'msg' => '', 'data' => ''];

		if(EmundusHelperAccess::asAccessAction(5,'r',JFactory::getUser()->id) || EmundusHelperAccess::asAccessAction(5,'c', JFactory::getUser()->id)){
			$results['data'] = $this->files->getSelectedTab();
		} else {
			$results['status'] = 0;
			$results['msg'] = JText::_('ACCESS_DENIED');
		}

		echo json_encode((object)$results);
		exit;
	}

	public function setselectedtab(){
		$results = ['status' => 1, 'msg' => ''];

		if(EmundusHelperAccess::asAccessAction(5,'r',JFactory::getUser()->id) || EmundusHelperAccess::asAccessAction(5,'c', JFactory::getUser()->id)){
			$tab = $this->input->getString('tab','');

			$this->files->setSelectedTab($tab);

			JFactory::getSession()->set('files',serialize($this->files));
		} else {
			$results['status'] = 0;
			$results['msg'] = JText::_('ACCESS_DENIED');
		}

		echo json_encode((object)$results);
		exit;
	}

	public function getcomments(){
		$results = ['status' => 1, 'msg' => '', 'data' => []];
		$fnum = $this->input->getString('fnum','');

		if(!empty($fnum) && (EmundusHelperAccess::asAccessAction(10,'r',JFactory::getUser()->id,$fnum) || EmundusHelperAccess::asAccessAction(10,'c',JFactory::getUser()->id,$fnum))){
			$results['data'] = $this->files->getComments($fnum);
		} else {
			$results['status'] = 0;
			$results['msg'] = JText::_('ACCESS_DENIED');
		}

		echo json_encode((object)$results);
		exit;
	}

	public function savecomment(){
		$results = ['status' => 0, 'msg' => JText::_('ACCESS_DENIED'), 'data' => []];

		$fnum = $this->input->getString('fnum', '');

		if (!empty($fnum) && EmundusHelperAccess::asAccessAction(10,'c',JFactory::getUser()->id,$fnum)){
			$reason = $this->input->getString('reason','');
			$comment_body = $this->input->getString('comment_body','');

			$comment = $this->files->saveComment($fnum,$reason,$comment_body);

			if (!empty($comment->id)) {
				$results['status'] = 1;
				$results['msg'] = '';
				$results['data'] = $comment;
			} else {
				$results['msg'] = JText::_('COM_EMUNDUS_FILES_CANNOT_GET_COMMENTS');
				$results['status'] = 0;
			}
		}

		echo json_encode((object)$results);
		exit;
	}

	public function deletecomment(){
		$results = ['status' => 0, 'msg' => JText::_('ACCESS_DENIED')];

		$cid = $this->input->getString('cid','');

		if(!empty($cid) && EmundusHelperAccess::asAccessAction(10,'c',JFactory::getUser()->id)) {
			$results['status'] = $this->files->deleteComment($cid);
		}

		echo json_encode((object)$results);
		exit;
	}

    public function getfilters() {
        $response = ['status' => 1, 'msg' => ''];

        if (EmundusHelperAccess::asAccessAction(5,'r', JFactory::getUser()->id) || EmundusHelperAccess::asAccessAction(5,'c', JFactory::getUser()->id)) {
            $filters = $this->files->getFilters();
            $filters['default_filters'] = array_values($filters['default_filters']);
            $response['data'] = $filters;
        } else {
            $response['status'] = 0;
            $response['msg'] = JText::_('ACCESS_DENIED');
        }

        echo json_encode((object)$response);
        exit;
    }

    public function applyfilters()
    {
        $response = ['status' => 1, 'msg' => ''];

        if (EmundusHelperAccess::asAccessAction(5,'r', JFactory::getUser()->id) || EmundusHelperAccess::asAccessAction(5,'c', JFactory::getUser()->id)) {

            $filters = $this->input->getString('filters');
            $filters = json_decode($filters, true);
            $this->files->applyFilters($filters);
            JFactory::getSession()->set('files', serialize($this->files));
        } else {
            $response['status'] = 0;
            $response['msg'] = JText::_('ACCESS_DENIED');
        }

        echo json_encode((object)$response);
        exit;
    }
}