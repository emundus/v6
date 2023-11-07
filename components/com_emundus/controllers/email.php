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

use Joomla\CMS\Factory;

/**
 * eMundus Component Controller
 *
 * @package    Joomla.eMundus
 * @subpackage Components
 */
class EmundusControllerEmail extends JControllerLegacy
{
	protected $app;
	
	private $_em_user;
	private $_user;
	private $m_emails;

	function __construct($config = array()){
        parent::__construct($config);

		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'filters.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'export.php');

		$this->app = Factory::getApplication();
		$this->_em_user = $this->app->getSession()->get('emundusUser');
		$this->_user    = $this->app->getIdentity();
        $this->m_emails = $this->getModel('emails');
    }

	function display($cachable = false, $urlparams = false) {
		// Set a default view if none exists
		if (!$this->input->get('view')) {
			$default = 'evaluation';
			$this->input->set('view', $default);
		}

		if (EmundusHelperAccess::asEvaluatorAccessLevel($this->_em_user->id)) {
            parent::display();
        } else {
            echo JText::_('ACCESS_DENIED');
        }
    }

	function clear() {
		EmundusHelperFiles::clear();

		$itemid           = $this->app->getMenu()->getActive()->id;
		$limitstart       = $this->input->get('limitstart', null, 'POST');
		$filter_order     = $this->input->get('filter_order', null, 'POST', null, 0);
		$filter_order_Dir = $this->input->get('filter_order_Dir', null, 'POST', null, 0);

		$this->setRedirect('index.php?option=com_emundus&view=' . $this->input->get('view') . '&limitstart=' . $limitstart . '&filter_order=' . $filter_order . '&filter_order_Dir=' . $filter_order_Dir . '&Itemid=' . $itemid);
	}

	function applicantEmail()
	{
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'emails.php');
		EmundusHelperEmails::sendApplicantEmail();
	}

	function getTemplate()
	{
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'emails.php');
		EmundusHelperEmails::getTemplate();
	}

	function sendmail_expert() {
		$response = ['status' => false, 'sent' => null, 'failed' => true, 'message' => JText::_( 'ACCESS_DENIED')];

		if (EmundusHelperAccess::asCoordinatorAccessLevel($this->_em_user->id) || EmundusHelperAccess::asAccessAction(18, 'c', $this->_user->id)) {

			$fnums = $this->input->post->getString('fnums');

			if (!empty($fnums)) {
				$email = $this->m_emails->sendExpertMail((array) $fnums);
				$response = ['status' => true, 'sent' => $email['sent'], 'failed' => $email['failed'], 'message' => $email['message']];
			} else {
				$response = ['status' => false, 'sent' => null, 'failed' => true, 'message' => JText::_( 'MISSING_PARAMS')];
			}
		}

        echo json_encode($response);
        exit;
    }

    /**
     * Get emails filtered
     */
    public function getallemail() {
	    $tab = array('status' => false, 'msg' => JText::_("ACCESS_DENIED"));

        if (EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {

			$filter    = $this->input->getString('filter') ? $this->input->getString('filter') : 'Publish';
			$sort      = $this->input->getString('sort', '');
			$recherche = $this->input->getString('recherche', '');
			$lim       = $this->input->getInt('lim', 25);
			$page      = $this->input->getInt('page', 0);
			$category  = $this->input->getString('category', '');

            $emails = $this->m_emails->getAllEmails($lim, $page, $filter, $sort, $recherche, $category);

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
		}
		else {


			$data = $this->input->getInt('id');

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
		}
		else {


			$data = $this->input->getInt('id');

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
		}
		else {


			$data = $this->input->getInt('id');

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
		}
		else {


			$data = $this->input->getInt('id');

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
		}
		else {


			$data                  = $this->input->getRaw('body');
			$receivers_cc          = $this->input->getRaw('selectedReceiversCC');
			$receivers_bcc         = $this->input->getRaw('selectedReceiversBCC');
			$letter_attachments    = $this->input->getRaw('selectedLetterAttachments');
			$candidate_attachments = $this->input->getRaw('selectedCandidateAttachments');
			$tags                  = $this->input->getRaw('selectedTags');

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
			$data                  = $this->input->getRaw('body','{}');
			$data = json_decode($data, true);
			$code                  = $this->input->getString('code');
			$receivers_cc          = $this->input->getRaw('selectedReceiversCC');
			$receivers_bcc         = $this->input->getRaw('selectedReceiversBCC');
			$letter_attachments    = $this->input->getRaw('selectedLetterAttachments');
			$candidate_attachments = $this->input->getRaw('selectedCandidateAttachments');
			$tags                  = $this->input->getRaw('selectedTags');

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
		}
		else {


			$id = $this->input->getInt('id');

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
		}
		else {


			$pid = $this->input->getInt('pid');

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
		}
		else {


			$tid = $this->input->getInt('tid');

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
		}
		else {


			$trigger      = $this->input->getRaw('trigger');
			$users = $this->input->getRaw('users');

            $status = $this->m_emails->createTrigger($trigger, $users, $this->_user);

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
		}
		else {


			$tid          = $this->input->getInt('tid');
			$trigger      = $this->input->getRaw('trigger');
			$users = $this->input->getRaw('users');

            $status = $this->m_emails->updateTrigger($tid, $trigger, $users);

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
		}
		else {


			$tid = $this->input->getInt('tid');

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
