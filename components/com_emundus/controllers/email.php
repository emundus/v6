<?php
/**
 * @package    Joomla
 * @subpackage Emundus
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
*/

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');
JHTML::addIncludePath(JPATH_COMPONENT.DS.'helpers');

/**
 * eMundus Component Controller
 *
 * @package    Joomla.eMundus
 * @subpackage Components
 */
class EmundusControllerEmail extends JControllerLegacy {
	var $_em_user = null;
	var $_user = null;
	var $_db = null;
    var $m_emails = null;

	function __construct($config = array()){
        parent::__construct($config);

        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');

		$this->_em_user = JFactory::getSession()->get('emundusUser');
		$this->_user = JFactory::getUser();
		$this->_db = JFactory::getDBO();
        $this->m_emails = $this->getModel('emails');
    }

	function display($cachable = false, $urlparams = false) {
		// Set a default view if none exists
		if ( ! JFactory::getApplication()->input->get( 'view' ) ) {
			$default = 'evaluation';
			JFactory::getApplication()->input->set('view', $default );
		}

		if (EmundusHelperAccess::asEvaluatorAccessLevel($this->_em_user->id)) {
            parent::display();
        } else {
            echo JText::_('ACCESS_DENIED');
        }
    }

	function clear() {
		EmundusHelperFiles::clear();

		$itemid=JFactory::getApplication()->getMenu()->getActive()->id;
		$limitstart = JFactory::getApplication()->input->get('limitstart', null, 'POST', 'none',0);
		$filter_order = JFactory::getApplication()->input->get('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = JFactory::getApplication()->input->get('filter_order_Dir', null, 'POST', null, 0);

		$this->setRedirect('index.php?option=com_emundus&view='.JFactory::getApplication()->input->get( 'view' ).'&limitstart='.$limitstart.'&filter_order='.$filter_order.'&filter_order_Dir='.$filter_order_Dir.'&Itemid='.$itemid);
	}


	////// EMAIL ASSESSORS WITH DEFAULT MESSAGE///////////////////
	function defaultEmail($reqids = null) {
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		//@EmundusHelperEmails::sendDefaultEmail();
	}

	////// EMAIL ASSESSORS WITH CUSTOM MESSAGE///////////////////
	function customEmail() {
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		//@EmundusHelperEmails::sendCustomEmail();
	}

	////// EMAIL APPLICANT WITH CUSTOM MESSAGE///////////////////
	function applicantEmail() {
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		@EmundusHelperEmails::sendApplicantEmail();
	}

	function getTemplate(){
		require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
		@EmundusHelperEmails::getTemplate();
	}

	function sendmail_expert() {
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_em_user->id) && !EmundusHelperAccess::asAccessAction(18, 'c', $this->_user->id)) {
			echo json_encode(['status' => false, 'sent' => null, 'failed' => true, 'message' => JText::_( 'ACCESS_DENIED')]);
	        die(JText::_( 'ACCESS_DENIED'));
        }

		$jinput = JFactory::getApplication()->input;
        $fnums = $jinput->post->getString('fnums');

        $email = $this->m_emails->sendExpertMail((array) $fnums);

        echo json_encode(['status' => true, 'sent' => $email['sent'], 'failed' => $email['failed'], 'message' => $email['message']]);
        exit;
    }

    /**
     * Get emails filtered
     */
    public function getallemail() {
	    $tab = array('status' => false, 'msg' => JText::_("ACCESS_DENIED"));

        if (EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            $jinput = JFactory::getApplication()->input;
            $filter = $jinput->getString('filter') ? $jinput->getString('filter') : 'Publish';
            $sort = $jinput->getString('sort', '');
            $recherche = $jinput->getString('recherche', '');
            $lim = $jinput->getInt('lim', 25);
            $page = $jinput->getInt('page', 0);

            $emails = $this->m_emails->getAllEmails($lim, $page, $filter, $sort, $recherche);

            if (count($emails) > 0) {
                $tab = array('status' => true, 'msg' => JText::_('EMAIL_RETRIEVED'), 'data' => $emails);
            } else {
                $tab = array('status' => false, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_EMAIL'), 'data' => $emails);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function deleteemail() {
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $data = $jinput->getInt('id');

            $emails = $this->m_emails->deleteEmail($data);

            if ($emails) {
                $tab = array('status' => 1, 'msg' => JText::_('EMAIL_DELETED'), 'data' => $emails);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_DELETE_EMAIL'), 'data' => $emails);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function unpublishemail() {
        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $data = $jinput->getInt('id');

            $emails = $this->m_emails->unpublishEmail($data);

            if ($emails) {
                $tab = array('status' => 1, 'msg' => JText::_('EMAIL_UNPUBLISHED'), 'data' => $emails);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_UNPUBLISH_EMAIL'), 'data' => $emails);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function publishemail() {
        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $data = $jinput->getInt('id');

            $emails = $this->m_emails->publishEmail($data);

            if ($emails) {
                $tab = array('status' => 1, 'msg' => JText::_('EMAIL_PUBLISHED'), 'data' => $emails);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_PUBLISH_EMAIL'), 'data' => $emails);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function duplicateemail() {
        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $data = $jinput->getInt('id');

            $email = $this->m_emails->duplicateEmail($data);

            if ($email) {
                $this->getallemail();
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_DUPLICATE_EMAIL'), 'data' => $email);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function createemail() {
        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $data = $jinput->getRaw('body');
            $receivers_cc = $jinput->getRaw('selectedReceiversCC');
            $receivers_bcc = $jinput->getRaw('selectedReceiversBCC');
            $letter_attachments = $jinput->getRaw('selectedLetterAttachments');
            $candidate_attachments = $jinput->getRaw('selectedCandidateAttachments');
            $tags = $jinput->getRaw('selectedTags');

            $cc_list = [];
            $bcc_list = [];
            $letter_list = [];
            $document_list = [];
            $tag_list = [];

            // get receiver cc from cc list
            if(!empty($receivers_cc)) {
                foreach ($receivers_cc as $value) {
                    if(!empty($value['email']) or !is_null($value['email'])) { $cc_list[] = $value['email']; }
                }
            }

            // get receiver bcc from cc list
            if(!empty($receivers_bcc)) {
                foreach ($receivers_bcc as $value) {
                    if(!empty($value['email']) or !is_null($value['email'])) { $bcc_list[] = $value['email']; }
                }
            }

            // get letters from $letter_attachments
            if(!empty($letter_attachments)) {
                foreach ($letter_attachments as $value) { if(!empty($value['id']) or !is_null($value['id'])) { $letter_list[] = $value['id']; } }
            }

            // get candidate attachments from $candidate_attachments
            if(!empty($candidate_attachments)) {
                foreach ($candidate_attachments as $value) {
                    if(!empty($value['id']) or !is_null($value['id'])) { $document_list[] = $value['id']; }
                }
            }

            // get tags from $tags
            if(!empty($tags)) {
                foreach ($tags as $value) {
                    if(!empty($value['id']) or !is_null($value['id'])) { $tag_list[] = $value['id']; }
                }
            }

            // call to createEmail model
            $result = $this->m_emails->createEmail($data, $cc_list, $bcc_list, $letter_list, $document_list, $tag_list);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('EMAIL_ADDED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_ADD_EMAIL'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }


    public function updateemail() {
        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $data = $jinput->getRaw('body');
            $code = $jinput->getString('code');
            $receivers_cc = $jinput->getRaw('selectedReceiversCC');
            $receivers_bcc = $jinput->getRaw('selectedReceiversBCC');
            $letter_attachments = $jinput->getRaw('selectedLetterAttachments');
            $candidate_attachments = $jinput->getRaw('selectedCandidateAttachments');
            $tags = $jinput->getRaw('selectedTags');

            $cc_list = [];
            $bcc_list = [];
            $letter_list = [];

            $document_list = [];
            $tag_list = [];

            // get receiver cc from cc list
            if(!empty($receivers_cc)) {
                foreach ($receivers_cc as $value) {
                    if(!empty($value['email']) or !is_null($value['email'])) { $cc_list[] = $value['email']; }
                }
            }

            // get receiver bcc from cc list
            if(!empty($receivers_bcc)) {
                foreach ($receivers_bcc as $value) {
                    if(!empty($value['email']) or !is_null($value['email'])) { $bcc_list[] = $value['email']; }
                }
            }

            // get attachments from $letters
            if(!empty($letter_attachments)) {
                foreach ($letter_attachments as $value) {
                    if(!empty($value['id']) or !is_null($value['id'])) { $letter_list[] = $value['id']; }
                }
            }

            // get candidate attachments from $candidate_attachments
            if(!empty($candidate_attachments)) {
                foreach ($candidate_attachments as $value) {
                    if(!empty($value['id']) or !is_null($value['id'])) { $document_list[] = $value['id']; }
                }
            }

            // get tags from $tags
            if(!empty($tags)) {
                foreach ($tags as $value) {
                    if(!empty($value['id']) or !is_null($value['id'])) { $tag_list[] = $value['id']; }
                }
            }

            $result = $this->m_emails->updateEmail($code, $data, $cc_list, $bcc_list, $letter_list, $document_list, $tag_list);        // updateEmail (models)

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('EMAIL_ADDED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('EMAIL'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getemailbyid() {
        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $id = $jinput->getInt('id');

            $email = $this->m_emails->getAdvancedEmailById($id);

            if (!empty($email)) {
                $tab = array('status' => 1, 'msg' => JText::_('EMAIL_RETRIEVED'), 'data' => $email);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_EMAIL'), 'data' => $email);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getemailcategories() {
	    $response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));

        if (EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            $categories = $this->m_emails->getEmailCategories();

            if (!empty($categories)) {
	            $response = array('status' => true, 'msg' => JText::_('EMAIL_CATEGORIES_RETRIEVED'), 'data' => $categories);
            } else {
	            $response['msg'] = JText::_('ERROR_CANNOT_RETRIEVE_EMAIL_CATEGORIES');
            }
        }

        echo json_encode((object)$response);
        exit;
    }

    public function getemailtypes() {
        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $email = $this->m_emails->getEmailTypes();

            if (!empty($email)) {
                $tab = array('status' => 1, 'msg' => JText::_('EMAIL_RETRIEVED'), 'data' => $email);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_EMAIL'), 'data' => $email);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getstatus() {
        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $status = $this->m_emails->getStatus();

            if (!empty($status)) {
                $tab = array('status' => 1, 'msg' => JText::_('STATUS_RETRIEVED'), 'data' => $status);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_STATUS'), 'data' => $status);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function gettriggersbyprogram() {
        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $pid = $jinput->getInt('pid');

            $triggers = $this->m_emails->getTriggersByProgramId($pid);

            if (!empty($triggers)) {
                $tab = array('status' => 1, 'msg' => JText::_('TRIGGERS_RETRIEVED'), 'data' => $triggers);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_TRIGGERS'), 'data' => $triggers);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function gettriggerbyid() {
        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $tid = $jinput->getInt('tid');

            $trigger = $this->m_emails->getTriggerById($tid);

            if (!empty($trigger)) {
                $tab = array('status' => 1, 'msg' => JText::_('TRIGGER_RETRIEVED'), 'data' => $trigger);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_TRIGGER'), 'data' => $trigger);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function createtrigger() {
        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $trigger = $jinput->getRaw('trigger');
            $this->_users = $jinput->getRaw('users');

            $status = $this->m_emails->createTrigger($trigger, $this->_users, $this->_user);

            if ($status) {
                $tab = array('status' => 1, 'msg' => JText::_('TRIGGER_CREATED'), 'data' => $status);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_CREATE_TRIGGER'), 'data' => $status);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function updatetrigger() {
        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $tid = $jinput->getInt('tid');
            $trigger = $jinput->getRaw('trigger');
            $this->_users = $jinput->getRaw('users');

            $status = $this->m_emails->updateTrigger($tid, $trigger, $this->_users);

            if (!empty($status)) {
                $tab = array('status' => 1, 'msg' => JText::_('TRIGGER_CREATED'), 'data' => $status);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_CREATE_TRIGGER'), 'data' => $status);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function removetrigger() {
        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $tid = $jinput->getInt('tid');

            $status = $this->m_emails->removeTrigger($tid);

            if (!empty($status)) {
                $tab = array('status' => 1, 'msg' => JText::_('TRIGGER_CREATED'), 'data' => $status);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_CREATE_TRIGGER'), 'data' => $status);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

}
