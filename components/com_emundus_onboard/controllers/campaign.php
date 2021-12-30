<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2016 eMundus. All rights reserved.
 * @license     GNU/GPL
 * @author      James Dean
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Campaign Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusonboardControllercampaign extends JControllerLegacy {

    var $model = null;

    public function __construct($config = array()) {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        parent::__construct($config);
        $this->model = $this->getModel('campaign');
    }

    /**
     * Get the number of campaigns with some filters
     */
    public function getcampaigncount() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $m_camp = $this->model;

	        $jinput = JFactory::getApplication()->input;

	        $filterCount = $jinput->getString('filterCount');
	        $rechercheCount = $jinput->getString('rechercheCount');

            $campaigns = $m_camp->getCampaignCount($filterCount, $rechercheCount);

            if ($campaigns > 0) {
                $tab = array('status' => 1, 'msg' => JText::_('CAMPAIGNS_RETRIEVED'), 'data' => $campaigns);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('NO_CAMPAIGNS'), 'data' => $campaigns);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Get the campaigns's list filtered
     */
    public function getallcampaign() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $m_camp = $this->model;

	        $jinput = JFactory::getApplication()->input;

	        $filter = $jinput->getString('filter');
	        $sort = $jinput->getString('sort');
	        $recherche = $jinput->getString('recherche');
	        $lim = $jinput->getInt('lim');
	        $page = $jinput->getInt('page');
            $program=$jinput->getString('program');

            $campaigns = $m_camp->getAssociatedCampaigns($filter, $sort, $recherche, $lim, $page,$program);

            if (count($campaigns) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_('CAMPAIGNS_RETRIEVED'), 'data' => $campaigns);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('NO_CAMPAIGNS'), 'data' => $campaigns);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Get the number of campaigns by program
     */
    public function getcampaignsbyprogram() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_camp = $this->model;

	        $jinput = JFactory::getApplication()->input;
	        $program = $jinput->get->getInt('pid');

            $campaigns = $m_camp->getCampaignsByProgram($program);

            if (count($campaigns) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_('CAMPAIGNS_RETRIEVED'), 'data' => $campaigns);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('NO_CAMPAIGNS'), 'data' => $campaigns);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Delete one or multiple campaigns
     *
     * @throws Exception
     */
    public function deletecampaign() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $jinput = JFactory::getApplication()->input;
	        $data = $jinput->getInt('id');

	        $m_camp = $this->model;

            $result = $m_camp->deleteCampaign($data);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('CAMPAIGN_DELETED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_DELETE_CAMPAIGN'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Unpublish one or multiple campaigns
     *
     * @throws Exception
     */
    public function unpublishcampaign() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $jinput = JFactory::getApplication()->input;
	        $data = $jinput->getInt('id');

	        $m_camp = $this->model;

            $result = $m_camp->unpublishCampaign($data);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('CAMPAIGN_UNPUBLISHED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_UNPUBLISH_CAMPAIGN'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Publish one or multiple campaigns
     *
     * @throws Exception
     */
    public function publishcampaign() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $data = $jinput->getInt('id');
	        $m_camp = $this->model;

            $result = $m_camp->publishCampaign($data);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('CAMPAIGN_PUBLISHED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_PUBLISH_CAMPAIGN'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Duplicate one or multiple campaigns
     *
     * @throws Exception
     */
    public function duplicatecampaign() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $data = $jinput->getInt('id');
	        $m_camp = $this->model;

            $result = $m_camp->duplicateCampaign($data);

            if ($result) {
                $this->getallcampaign();
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_DUPLICAT_CAMPAIGN'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    //TODO Throw in the years controller
    public function getyears() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $m_camp = $this->model;
            $years = $m_camp->getYears();

            if ($years > 0) {
                $tab = array('status' => 1, 'msg' => JText::_('CAMPAIGNS_RETRIEVED'), 'data' => $years);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_CAMPAIGNS'), 'data' => $years);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Create a campaign
     *
     * @throws Exception
     */
    public function createcampaign() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $data = $jinput->getRaw('body');
	        $m_camp = $this->model;

            $data['user'] = $user->id;

            $result = $m_camp->createCampaign($data);

            if ($result) {
            	$tab = array('status' => 1, 'msg' => JText::_('CAMPAIGN_ADDED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_ADD_CAMPAIGN'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Update a campaign
     *
     * @throws Exception
     */
    public function updatecampaign() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $data = $jinput->getRaw('body');
	        $cid = $jinput->getInt('cid');
	        $m_camp = $this->model;

            $data['user'] = $user->id;

            $result = $m_camp->updateCampaign($data, $cid);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('CAMPAIGN_UPDATED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_UPDATE_CAMPAIGN'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    //TODO Throw in the years controller
    //TODO Not getting the program category in data... Gonna need to get it
    public function createyear() {

        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $data = $jinput->getRaw('body');
	        $m_camp = $this->model;

            $result = $m_camp->createYear($data);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('YEAR_ADDED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_ADD_YEAR'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Get a campaign by id
     *
     * @throws Exception
     */
    public function getcampaignbyid() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $id = $jinput->getInt('id');
	        $m_camp = $this->model;

            $campaign = $m_camp->getCampaignById($id);
            if (!empty($campaign)) {
                $tab = array('status' => 1, 'msg' => JText::_('CAMPAIGN_RETRIEVED'), 'data' => $campaign);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_CAMPAIGN'), 'data' => $campaign);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Return the created campaign
     */
    public function getcreatedcampaign() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_camp = $this->model;
            $campaign = $m_camp->getCreatedCampaign();

            if (!empty($campaign)) {
                $tab = array('status' => 1, 'msg' => JText::_('CREATED_CAMPAIGN_RETRIEVED'), 'data' => $campaign);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_CREATED_CAMPAIGN'), 'data' => $campaign);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Affect a profile(form) to a campaign
     *
     * @throws Exception
     */
    public function updateprofile() {

    	$jinput = JFactory::getApplication()->input;
        $profile = $jinput->getInt('profile');
        $campaign = $jinput->getInt('campaign');
        $m_camp = $this->model;

        $result = $m_camp->updateProfile($profile, $campaign);

        if ($result) {
            $tab = array('status' => 1, 'msg' => JText::_('CAMPAIGN_UPDATED'), 'data' => $result);
        } else {
            $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_UPDATE_CAMPAIGN'), 'data' => $result);
        }

        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Get campaigns without profile affected and not finished
     */
    public function getcampaignstoaffect() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $m_camp = $this->model;
            $campaigns = $m_camp->getCampaignsToAffect();

            if (!empty($campaigns)) {
                $tab = array('status' => 1, 'msg' => JText::_('USERS_RETRIEVED'), 'data' => $campaigns);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_USERS'), 'data' => $campaigns);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Get campaigns with term filter in name and description
     *
     * @throws Exception
     */
    public function getcampaignstoaffectbyterm() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {

	        $jinput = JFactory::getApplication()->input;
	        $term = $jinput->getString('term');
	        $m_camp = $this->model;

            $campaigns = $m_camp->getCampaignsToAffectByTerm($term);
            if (!empty($campaigns)) {
                $tab = array('status' => 1, 'msg' => JText::_('USERS_RETRIEVED'), 'data' => $campaigns);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_USERS'), 'data' => $campaigns);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function createdocument() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $document = $jinput->getRaw('document');
            $types = $jinput->getRaw('types');
            $cid = $jinput->getInt('cid');
            $pid = $jinput->getInt('pid');

            $m_camp = $this->model;

            $result = $m_camp->createDocument($document,$types,$cid,$pid);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('DOCUMENT_ADDED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_ADD_DOCUMENT'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function updatedocument() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $document = $jinput->getRaw('document');
            $types = $jinput->getRaw('types');
            $isModeleAndUpdate=$jinput->get('isModeleAndUpdate');
            $did = $jinput->getInt('did');
            $cid = $jinput->getInt('cid');
            $pid = $jinput->getInt('pid');


            $m_camp = $this->model;

            $result = $m_camp->updateDocument($document,$types,$did,$pid,$isModeleAndUpdate);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('DOCUMENT_UPDATED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_UPDATE_DOCUMENT'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }


    public function updateDocumentFalang(){

        $jinput = JFactory::getApplication()->input;
        $text = new stdClass;
        $text->fr=$jinput->getString('text_fr');
        $text->en=$jinput->getString('text_en');
        $reference_id=$jinput->getInt('did');
        $falang=$this->getModel('falang');
        $result=$falang->updateFalang($text,$reference_id,'emundus_setup_attachments','value');

        if ($result) {
            $tab = array('status' => 1, 'msg' => JText::_('DOCUMENT_UPDATED'), 'data' => $result);
        } else {
            $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_UPDATE_DOCUMENT'), 'data' => $result);
        }
        echo json_encode((object)$tab);
        exit;
    }
    public function getDocumentFalang()  {
        $jinput = JFactory::getApplication()->input;

        $reference_id=$jinput->getInt('docid');
        //echo "hello";
        $falang=$this->getModel('falang');
        $result=$falang->getFalang($reference_id,'emundus_setup_attachments','value');

        if ($result) {
            $tab = array('status' => 1, 'msg' => JText::_('DOCUMENT_UPDATE'), 'data' => $result);
        } else {
            $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_UPDATE_DOCUMENT'), 'data' => $result);
        }
        echo json_encode((object)$tab);
        exit;
        }

    public function getdocumentsdropfiles() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $response = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $m_camp = $this->model;

            $jinput = JFactory::getApplication()->input;
            $cid = $jinput->get('cid');
            $campaign_category = $m_camp->getCampaignCategory($cid);
            $datas = $m_camp->getCampaignDropfilesDocuments($campaign_category);
            $response = array('status' => '1', 'msg' => 'SUCCESS', 'documents' => $datas);
        }
        echo json_encode((object)$response);
        exit;
    }

    public function deletedocumentdropfile() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $did = $jinput->getInt('did');
            $m_camp = $this->model;

            $result = $m_camp->deleteDocumentDropfile($did);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('DOCUMENT_DELETED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_DELETE_DOCUMENT'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function editdocumentdropfile() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $did = $jinput->getInt('did');
            $name = $jinput->getString('name');
            $m_camp = $this->model;

            $result = $m_camp->editDocumentDropfile($did,$name);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('DOCUMENT_EDITED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_EDIT_DOCUMENT'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function updateorderdropfiledocuments() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $documents = $jinput->getRaw('documents');
            $m_camp = $this->model;

            $result = $m_camp->updateOrderDropfileDocuments($documents);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('DOCUMENT_ORDERING'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_ORDERING_DOCUMENTS'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getdocumentsform() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $response = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $m_camp = $this->model;

            $jinput = JFactory::getApplication()->input;
            $pid = $jinput->get('pid');
            $datas = $m_camp->getFormDocuments($pid);
            $response = array('status' => '1', 'msg' => 'SUCCESS', 'documents' => $datas);
        }
        echo json_encode((object)$response);
        exit;
    }

    public function editdocumentform() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $did = $jinput->getInt('did');
            $pid = $jinput->getInt('pid');
            $name = $jinput->getString('name');
            $m_camp = $this->model;

            $result = $m_camp->editDocumentForm($did,$name,$pid);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('DOCUMENT_EDITED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_EDIT_DOCUMENT'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function deletedocumentform() {
        $user = JFactory::getUser();

        if (!EmundusonboardHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $did = $jinput->getInt('did');
            $pid = $jinput->getInt('pid');
            $m_camp = $this->model;

            $result = $m_camp->deleteDocumentForm($did,$pid);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('DOCUMENT_EDITED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_EDIT_DOCUMENT'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

}

