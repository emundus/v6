<?php
/**
 * @package     Joomla
 * @subpackage  eMundus
 * @link        http://www.emundus.fr
 * @copyright   Copyright (C) 2016 eMundus. All rights reserved.
 * @license     GNU/GPL
 * @author      eMundus - Benjamin Rivalland
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * Campaign Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      v6
 */
class EmundusControllerCampaign extends JControllerLegacy {
    var $_user = null;
    var $_em_user = null;
    var $_db = null;
    var $m_campaign = null;

    function __construct($config = array()){
        parent::__construct($config);

        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');

        $this->_user = JFactory::getUser();
        $this->_em_user = JFactory::getSession()->get('emundusUser');
        $this->_db = JFactory::getDBO();

        $this->m_campaign = $this->getModel('campaign');
    }
    function display($cachable = false, $urlparams = false) {
        // Set a default view if none exists
        if ( ! JFactory::getApplication()->input->get( 'view' ) ) {
            $default = 'campaign';
            JFactory::getApplication()->input->set('view', $default );
        }
        parent::display();
    }

    function clear() {
        EmundusHelperFilters::clear();
    }

    function setCampaign()
    {
        return true;
    }

    /**
     * Add campaign for Ametys sync
     *
     * @since v6
     */
    public function addcampaigns(){
        $data = array();
        $data['start_date'] = JFactory::getApplication()->input->get('start_date', null, 'POST', 'none',0);
        $data['end_date'] = JFactory::getApplication()->input->get('end_date', null, 'POST', 'none',0);
        $data['profile_id'] = JFactory::getApplication()->input->get('profile_id', null, 'POST', 'none',0);
        $data['year'] = JFactory::getApplication()->input->get('year', null, 'POST', 'none',0);
        $data['short_description'] = JFactory::getApplication()->input->get('short_description', null, 'POST', 'none',0);

        $m_programme = $this->getModel('programme');

        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $programmes = $m_programme->getProgrammes(1);

            if (count($programmes) > 0) {
                $result = $this->m_campaign->addCampaignsForProgrammes($data, $programmes);
            } else {
                $result = false;
            }

            if ($result === false) {
                $tab = array('status' => 0, 'msg' => JText::_('COM_EMUNDUS_AMETYS_ERROR_CANNOT_ADD_CAMPAIGNS'), 'data' => $result);
            } else {
                $tab = array('status' => 1, 'msg' => JText::_('COM_EMUNDUS_CAMPAIGNS_ADDED'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Gets all campaigns linked to a program code
     *
     * @since v6
     */
    public function getcampaignsbyprogram(){
        $jinput = JFactory::getApplication()->input;
        $course = $jinput->get('course');

        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"), 'campaigns' => []);
        } else {
            $campaigns = $this->m_campaign->getCampaignsByProgram($course);
            $tab = array('status' => true, 'msg' => 'CAMPAIGNS RETRIEVED', 'campaigns' => $campaigns);
        }
        echo json_encode((object) $tab);
        exit;
    }

    /**
     * Get the number of campaigns by program
     *
     * @since version 1.0
     */
    public function getcampaignsbyprogramme() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asPartnerAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $program = $jinput->get->getInt('pid');

            $campaigns = $this->m_campaign->getCampaignsByProgramme($program);

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
     * Get the campaigns's list filtered
     *
     * @since version 1.0
     */
    public function getallcampaign() {
        $tab = array('status' => false, 'msg' => JText::_("ACCESS_DENIED"));

        if (EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            $jinput = JFactory::getApplication()->input;
            $filter = $jinput->getString('filter', '');
            $sort = $jinput->getString('sort', '');
            $recherche = $jinput->getString('recherche', '');
            $lim = $jinput->getInt('lim', 25);
            $page = $jinput->getInt('page', 0);
            $program = $jinput->getString('program', 'all');
            $session = $jinput->getString('session', 'all');
            $campaigns = $this->m_campaign->getAssociatedCampaigns($filter, $sort, $recherche, $lim, $page,$program,$session);

            $eMConfig = JComponentHelper::getParams('com_emundus');
            $allow_pinned_campaign = $eMConfig->get('allow_pinned_campaign', 0);

            if (count($campaigns) > 0) {
                // this data formatted is used in onboarding lists
                foreach($campaigns['datas'] as $key => $campaign) {
                    $campaign->label = ['fr' => $campaign->label, 'en' => $campaign->label];

                    $config = JFactory::getConfig();
                    $offset = $config->get('offset');
                    $now_date_time = new DateTime('now', new DateTimeZone($offset));
                    $now = $now_date_time->format('U');
                    $start_date = strtotime($campaign->start_date);
                    $end_date = strtotime($campaign->end_date);

                    if ($now < $start_date) {
                        $campaign_time_state_label = JText::_('COM_EMUNDUS_CAMPAIGN_YET_TO_COME');
                        $campaign_time_state_class = 'label label-default em-p-5-12 em-font-weight-600';
                    } else if ($now > $end_date) {
                        $campaign_time_state_label = JText::_('COM_EMUNDUS_ONBOARD_FILTER_CLOSE');
                        $campaign_time_state_class = 'label label-black em-p-5-12 em-font-weight-600';
                    } else {
                        $campaign_time_state_label = JText::_('COM_EMUNDUS_CAMPAIGN_ONGOING');
                        $campaign_time_state_class = 'label label-default em-p-5-12 em-font-weight-600';
                    }

                    $start_date = date('d/m/Y H\hi', strtotime($campaign->start_date));
                    $end_date = date('d/m/Y H\hi', strtotime($campaign->end_date));

                    $state_values = [
                        [
                            'key' => JText::_('COM_EMUNDUS_ONBOARD_STATE'),
                            'value' => $campaign->published ? JText::_('PUBLISHED') : JText::_('COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH'),
                            'classes' => $campaign->published ? 'label label-lightgreen em-p-5-12 em-font-weight-600' : 'label label-default em-p-5-12 em-font-weight-600',
                        ],
                        [
                            'key' => JText::_('COM_EMUNDUS_ONBOARD_TIME_STATE'),
                            'value' => $campaign_time_state_label,
                            'classes' => $campaign_time_state_class
                        ]
                    ];

                    $campaign->additional_columns = [
                        [
                            'key' => JText::_('COM_EMUNDUS_ONBOARD_START_DATE'),
                            'value' => $start_date,
                            'classes' => '',
                            'display' => 'table'
                        ],
                        [
                            'key' => JText::_('COM_EMUNDUS_ONBOARD_END_DATE'),
                            'value' => $end_date,
                            'classes' => '',
                            'display' => 'table'
                        ],
                        [
                            'key' => JText::_('COM_EMUNDUS_ONBOARD_STATE'),
                            'type' => 'tags',
                            'values' => $state_values,
                            'display' => 'table'
                        ],
                        [
                            'key' => JText::_('COM_EMUNDUS_ONBOARD_NB_FILES'),
                            'value' => $campaign->nb_files,
                            'classes' => '',
                            'display' => 'table'
                        ],
                        [
                            'value' => JText::_('COM_EMUNDUS_DASHBOARD_CAMPAIGN_FROM') . ' ' . $start_date . ' ' . JText::_('COM_EMUNDUS_DASHBOARD_CAMPAIGN_TO') . ' ' . $end_date,
                            'classes' => 'em-font-size-14 em-neutral-700-color',
                            'display' => 'blocs'
                        ],
                        [
                            'type' => 'tags',
                            'key' => JText::_('COM_EMUNDUS_ONBOARD_STATE'),
                            'values' => [
                                $state_values[0],
                                $state_values[1],
                                [
                                    'key' => JText::_('COM_EMUNDUS_FILES_FILES'),
                                    'value' => $campaign->nb_files . ' ' . ( $campaign->nb_files > 1 ? JText::_('COM_EMUNDUS_FILES_FILES') : JText::_('COM_EMUNDUS_FILES_FILE')),
                                    'classes' => 'label label-default em-p-5-12 em-font-weight-600',
                                ]
                            ],
                            'classes' => 'em-mt-8 em-mb-8',
                            'display' => 'blocs'
                        ]
                    ];
                    $campaigns['datas'][$key] = $campaign;
                }

                $tab = array('status' => true, 'msg' => JText::_('CAMPAIGNS_RETRIEVED'), 'data' => $campaigns, 'allow_pinned_campaigns' => $allow_pinned_campaign);
            } else {
                $tab = array('status' => false, 'msg' => JText::_('NO_CAMPAIGNS'), 'data' => $campaigns, 'allow_pinned_campaigns' => $allow_pinned_campaign);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Delete one or multiple campaigns
     *
     * @since version 1.0
     */
    public function deletecampaign() {

        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $data = $jinput->getInt('id');

            $result = $this->m_campaign->deleteCampaign($data);

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
     * @since version 1.0
     */
    public function unpublishcampaign() {

        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getInt('id');

            $result = $this->m_campaign->unpublishCampaign($data);

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
     * @since version 1.0
     */
    public function publishcampaign() {

        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $data = $jinput->getInt('id');

            $result = $this->m_campaign->publishCampaign($data);

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
     * @since version 1.0
     */
    public function duplicatecampaign() {

        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $data = $jinput->getInt('id');

            $result = $this->m_campaign->duplicateCampaign($data);

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

    /**
     * Get teaching_unity available
     *
     * @since version 1.0
     */
    public function getyears() {

        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $years = $this->m_campaign->getYears();

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
     * @since version 1.0
     */
    public function createcampaign() {

        if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $data = $jinput->getArray();
            $data['user'] = $this->_user->id;

            $result = $this->m_campaign->createCampaign($data);

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
     * @since version 1.0
     */
    public function updatecampaign() {
        $tab = array('status' => false, 'msg' => JText::_("ACCESS_DENIED"));

        if (EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
            $jinput = JFactory::getApplication()->input;
            $data = $jinput->getRaw('body');
            $cid = $jinput->getInt('cid');

            $data['user'] = $this->_user->id;

            if (!empty($cid)) {
                $result = $this->m_campaign->updateCampaign($data, $cid);

                if ($result) {
                    $tab = array('status' => true, 'msg' => JText::_('CAMPAIGN_UPDATED'), 'data' => $result);
                } else {
                    $tab = array('status' => false, 'msg' => JText::_('ERROR_CANNOT_UPDATE_CAMPAIGN'), 'data' => $result);
                }
            } else {
                $tab = array('status' => false, 'msg' => JText::_('MISSING_PARAMETERS'));
            }
        }

        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Get a campaign by id
     *
     * @since version 1.0
     */
    public function getcampaignbyid() {

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->getInt('id');

            $campaign = $this->m_campaign->getCampaignDetailsById($id);

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
     *
     * @since version 1.0
     */
    public function getcreatedcampaign() {

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $campaign = $this->m_campaign->getCreatedCampaign();

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
     * @since version 1.0
     */
    public function updateprofile() {

        $jinput = JFactory::getApplication()->input;
        $profile = $jinput->getInt('profile');
        $campaign = $jinput->getInt('campaign');

        $result = $this->m_campaign->updateProfile($profile, $campaign);

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
     *
     * @since version 1.0
     */
    public function getcampaignstoaffect() {

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $campaigns = $this->m_campaign->getCampaignsToAffect();

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
     * @since 1.0
     */
    public function getcampaignstoaffectbyterm() {

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $term = $jinput->getString('term');

            $campaigns = $this->m_campaign->getCampaignsToAffectByTerm($term);

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
     * Add a new document to form
     *
     * @throws Exception
     * @since version 1.0
     */
    public function createdocument() {

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $document = $jinput->getString('document');
            $document = json_decode($document, true);

            $types = $jinput->getString('types');
            $types = json_decode($types, true);

            $cid = $jinput->getInt('cid');
            $pid = $jinput->getInt('pid');

            $result = $this->m_campaign->createDocument($document,$types,$cid,$pid);

            if ($result['status']) {
                $tab = array('status' => 1, 'msg' => JText::_('DOCUMENT_ADDED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_($result['msg']), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Update form document
     *
     * @throws Exception
     * @since version 1.0
     */
    public function updatedocument() {

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $document = $jinput->getString('document');
            $document = json_decode($document, true);

            $types = $jinput->getString('types');
            $types = json_decode($types, true);

            $isModeleAndUpdate=$jinput->get('isModeleAndUpdate');
            $did = $jinput->getInt('did');
            $cid = $jinput->getInt('cid');
            $pid = $jinput->getInt('pid');

            $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_UPDATE_DOCUMENT'), 'data' => '');

            if (!empty($document)) {
                $result = $this->m_campaign->updateDocument($document,$types,$did,$pid,$isModeleAndUpdate);

                $tab['data'] = $result;
                if ($result) {
                    $tab['status'] = 1;
                    $tab['msg'] = JText::_('DOCUMENT_UPDATED');
                }
            }
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function updatedocumentmandatory() {
         if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
         } else {
             $jinput = JFactory::getApplication()->input;

             $did = $jinput->getInt('did');
             $pid = $jinput->getInt('pid');
             $mandatory = $jinput->getInt('mandatory');

             if (!empty($did) && !empty($pid)) {
                 $tab['status'] = $this->m_campaign->updatedDocumentMandatory($did, $pid, $mandatory);
                 if ($tab['status']) {
                     $tab['msg'] = JText::_('DOCUMENT_UPDATED');
                 }
             }
         }

        echo json_encode((object)$tab);
        exit;
    }


    /**
     * Update translations of documents
     *
     * @throws Exception
     * @since version 1.0
     */
    public function updateDocumentFalang(){

        $jinput = JFactory::getApplication()->input;
        $text = new stdClass;
        $text->fr=$jinput->getString('text_fr');
        $text->en=$jinput->getString('text_en');
        $reference_id=$jinput->getInt('did');
        $falang = new EmundusModelFalang();
        $result = $falang->updateFalang($text,$reference_id,'emundus_setup_attachments','value');

        if ($result) {
            $tab = array('status' => 1, 'msg' => JText::_('DOCUMENT_UPDATED'), 'data' => $result);
        } else {
            $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_UPDATE_DOCUMENT'), 'data' => $result);
        }
        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Get translations of documents
     *
     * @throws Exception
     * @since version 1.0
     */
    public function getDocumentFalang()  {
        $jinput = JFactory::getApplication()->input;

        $reference_id = $jinput->getInt('docid');
        $falang = new EmundusModelFalang();

        $result = $falang->getFalang($reference_id,'emundus_setup_attachments','value');

        if ($result) {
            $tab = array('status' => 1, 'msg' => JText::_('DOCUMENT_UPDATE'), 'data' => $result);
        } else {
            $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_UPDATE_DOCUMENT'), 'data' => $result);
        }
        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Get Dropfiles documents linked to a campaign
     *
     * @throws Exception
     * @since version
     */
    public function getdocumentsdropfiles() {

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $response = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $cid = $jinput->get('cid');

            $campaign_category = $this->m_campaign->getCampaignCategory($cid);
            $datas = $this->m_campaign->getCampaignDropfilesDocuments($campaign_category);

            $response = array('status' => '1', 'msg' => 'SUCCESS', 'documents' => $datas);
        }
        echo json_encode((object)$response);
        exit;
    }

    /**
     * Delete Dropfile document
     *
     * @throws Exception
     * @since version 1.0
     */
    public function deletedocumentdropfile() {

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $did = $jinput->getInt('did');

            $result = $this->m_campaign->deleteDocumentDropfile($did);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('DOCUMENT_DELETED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_DELETE_DOCUMENT'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Edit a Dropfile document
     *
     * @throws Exception
     * @since version 1.0
     */
    public function editdocumentdropfile() {

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $did = $jinput->getInt('did');
            $name = $jinput->getString('name');

            $result = $this->m_campaign->editDocumentDropfile($did,$name);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('DOCUMENT_EDITED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_EDIT_DOCUMENT'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Update the order of Dropfiles documents
     *
     * @throws Exception
     * @since version 1.0
     */
    public function updateorderdropfiledocuments() {

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $documents = $jinput->getRaw('documents');

            $result = $this->m_campaign->updateOrderDropfileDocuments($documents);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('DOCUMENT_ORDERING'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_ORDERING_DOCUMENTS'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Get documents link to form by campaign (by the module)
     *
     * @throws Exception
     * @since version 1.0
     */
    public function getdocumentsform() {

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $response = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $pid = $jinput->get('pid');

            $datas = $this->m_campaign->getFormDocuments($pid);

            $response = array('status' => '1', 'msg' => 'SUCCESS', 'documents' => $datas);
        }
        echo json_encode((object)$response);
        exit;
    }

    /**
     * Update a document available in form view
     *
     * @throws Exception
     * @since version 1.0
     */
    public function editdocumentform() {

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $did = $jinput->getInt('did');
            $pid = $jinput->getInt('pid');
            $name = $jinput->getString('name');

            $result = $this->m_campaign->editDocumentForm($did,$name,$pid);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('DOCUMENT_EDITED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_EDIT_DOCUMENT'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    /**
     * Delete a document from form view
     *
     * @throws Exception
     * @since version 1.0
     */
    public function deletedocumentform() {

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;
            $did = $jinput->getInt('did');
            $pid = $jinput->getInt('pid');

            $result = $this->m_campaign->deleteDocumentForm($did,$pid);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('DOCUMENT_EDITED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_EDIT_DOCUMENT'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function pincampaign(){
        if (!EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_('ACCESS_DENIED'));
        } else {
            $jinput = JFactory::getApplication()->input;
            $cid = $jinput->getInt('cid');

            $result = $this->m_campaign->pinCampaign($cid);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('CAMPAIGN_PINNED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_PINNED_CAMPAIGN'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }
}
?>
