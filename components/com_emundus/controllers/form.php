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

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

/**
 * Form Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusControllerForm extends JControllerLegacy {

    var $m_form = null;
    public function __construct($config = array()) {
        parent::__construct($config);

        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        require_once (JPATH_COMPONENT.DS.'models'.DS.'form.php');
        $this->m_form = new EmundusModelForm;
    }

    public function getallform() {
        $user = JFactory::getUser();
	    $tab = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));

        if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
	        $jinput = JFactory::getApplication()->input;
	        $page = $jinput->getInt('page', 0);
	        $lim = $jinput->getInt('lim', 0);
	        $filter = $jinput->getString('filter', '');
	        $sort = $jinput->getString('sort', '');
	        $recherche = $jinput->getString('recherche', '');

	        $data = $this->m_form->getAllForms($filter, $sort, $recherche, $lim, $page);

            foreach ($data['datas'] as $key => $form) {
                // find campaigns associated with form
                $campaigns = $this->m_form->getAssociatedCampaign($form->id);

                if (!empty($campaigns)) {
                    if (count($campaigns) < 2) {
                        $short_tags = '<a href="/index.php?option=com_emundus&view=campaigns&layout=addnextcampaign&cid=' . $campaigns[0]->id . '" class="mr-2 mb-2 h-max px-3 py-1 font-semibold em-bg-main-100 em-text-neutral-900 text-sm em-border-radius"> ' . $campaigns[0]->label . '</a>';
                    } else {
                        $tags = '<div>';
                        $short_tags = $tags;
						$tags .= '<h2 class="mb-2">'.Text::_('COM_EMUNDUS_ONBOARD_CAMPAIGNS_ASSOCIATED_TITLE').'</h2>';
						$tags .= '<div class="flex flex-wrap">';
                        foreach ($campaigns as $campaign) {
                            $tags .= '<a href="/index.php?option=com_emundus&view=campaigns&layout=addnextcampaign&cid=' . $campaign->id . '" class="mr-2 mb-2 h-max px-3 py-1 font-semibold em-bg-main-100 em-text-neutral-900 text-sm em-border-radius"> ' . $campaign->label . '</a>';
                        }
						$tags .= '</div>';

                        $short_tags .= '<span class="cursor-pointer font-semibold em-profile-color flex items-center underline">' . count($campaigns) . JText::_('COM_EMUNDUS_ONBOARD_CAMPAIGNS_ASSOCIATED') . '</span>';
                        $short_tags .= '</div>';
                        $tags .= '</div>';
                    }
                } else {
                    $short_tags = JText::_('COM_EMUNDUS_ONBOARD_CAMPAIGNS_ASSOCIATED_NOT');
                }

                $new_column = [
                    'key' => JText::_('COM_EMUNDUS_ONBOARD_CAMPAIGNS_ASSOCIATED_TITLE'),
                    'value' => $short_tags,
                    'classes' => '',
                    'display' => 'all'
                ];

                if (isset($tags)) {
                    $new_column['long_value'] = $tags;
                }

                $form->additional_columns = [
                    $new_column
                ];
            }

            if (!empty($data)) {
                $tab = array('status' => true, 'msg' => JText::_('FORM_RETRIEVED'), 'data' => $data);
            } else {
                $tab['msg'] = JText::_('ERROR_CANNOT_RETRIEVE_FORM');
            }
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function getallgrilleEval() {
        $user = JFactory::getUser();
	    $tab = array('status' => false, 'msg' => JText::_("ACCESS_DENIED"));

        if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $jinput = JFactory::getApplication()->input;
	        $page = $jinput->getInt('page', 0);
	        $lim = $jinput->getInt('lim', 0);
	        $filter = $jinput->getString('filter', '');
	        $sort = $jinput->getString('sort', '');
	        $recherche = $jinput->getString('recherche', '');

            $forms = $this->m_form->getAllGrilleEval($filter, $sort, $recherche, $lim, $page);

            if (count($forms) > 0) {
                $tab = array('status' => true, 'msg' => JText::_('FORM_RETRIEVED'), 'data' => $forms);
            } else {
                $tab['msg'] = JText::_('ERROR_CANNOT_RETRIEVE_FORM');
            }
        }
        echo json_encode((object)$tab);
        exit;
    }


    public function getallformpublished() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $forms = $this->m_form->getAllFormsPublished();

            if (count($forms) > 0) {
                $tab = array('status' => 1, 'msg' => JText::_('FORM_RETRIEVED'), 'data' => $forms);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_FORM'), 'data' => $forms);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }


    public function deleteform() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $jinput = JFactory::getApplication()->input;

	        $data = $jinput->getInt('id');

            $forms = $this->m_form->deleteForm($data);

            if ($forms) {
                $tab = array('status' => 1, 'msg' => JText::_('FORM_DELETED'), 'data' => $forms);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_DELETE_FORM'), 'data' => $forms);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }


    public function unpublishform() {
	    $response = array('status' => 0, 'msg' => JText::_('ACCESS_DENIED'));
	    $user = JFactory::getUser();

        if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
	        $jinput = JFactory::getApplication()->input;
	        $id = $jinput->getInt('id', 0);

            $result = $this->m_form->unpublishForm([$id]);

            if ($result['status']) {
	            $response = array('status' => 1, 'msg' => JText::_('FORM_UNPUBLISHED'));
            } else {
	            $response = array('status' => 0, 'msg' => !empty($result['msg']) ? JText::_($result['msg']) : JText::_('ERROR_CANNOT_UNPUBLISH_FORM'));
            }
        }

        echo json_encode((object)$response);
        exit;
    }


    public function publishform() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $jinput = JFactory::getApplication()->input;
	        $id = $jinput->getInt('id');

            $forms = $this->m_form->publishForm([$id]);

            if ($forms) {
                $tab = array('status' => 1, 'msg' => JText::_('FORM_PUBLISHED'), 'data' => $forms);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_PUBLISH_FORM'), 'data' => $forms);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }


    public function duplicateform() {
        $user = JFactory::getUser();
	    $tab = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));

        if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
	        $jinput = JFactory::getApplication()->input;
	        $data = $jinput->getInt('id', 0);

			if (!empty($data)) {
				$form = $this->m_form->duplicateForm($data);
				if ($form) {
					$tab = array('status' => true, 'msg' => JText::_('FORM_DUPLICATED'), 'data' => $form);
				} else {
					$tab['msg'] = JText::_('ERROR_CANNOT_DUPLICATE_FORM');
				}
			} else {
				$tab['msg'] = JText::_('MISSING_PARAMS');
			}
        }

        echo json_encode((object)$tab);
        exit;
    }


    public function createform() {
	    $tab = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));
	    $user = JFactory::getUser();

        if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = $this->m_form->createApplicantProfile();

            if ($result) {
                $tab = array('status' => true, 'msg' => JText::_('FORM_ADDED'), 'data' => $result, 'redirect' => 'index.php?option=com_emundus&view=form&layout=formbuilder&prid='.$result);
            } else {
                $tab['msg'] = JText::_('ERROR_CANNOT_ADD_FORM');
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

	public function createformeval() {
		$tab = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));
		$user = JFactory::getUser();

		if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$form_id = $this->m_form->createFormEval();

			if ($form_id) {
				$tab = array('status' => true, 'msg' => JText::_('FORM_ADDED'), 'data' => $form_id, 'redirect' => 'index.php?option=com_emundus&view=form&layout=formbuilder&prid='. $form_id . '&mode=eval');
			} else {
				$tab['msg'] = JText::_('ERROR_CANNOT_ADD_FORM');
			}
		}

		echo json_encode((object)$tab);
		exit;
	}


    public function updateform() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $jinput = JFactory::getApplication()->input;

	        $data = $jinput->getRaw('body');
	        $pid = $jinput->getInt('pid');

            $result = $this->m_form->updateForm($pid, $data);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('FORM_ADDED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('FORM'), 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function updateformlabel() {
        $user = JFactory::getUser();
	    $tab = array('status' => 0, 'msg' => JText::_('ACCESS_DENIED'));

	    if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $jinput = JFactory::getApplication()->input;
            $prid = $jinput->getInt('prid', 0);
            $label = $jinput->getString('label');

            $result = $this->m_form->updateFormLabel($prid, $label);

            if ($result) {
                $tab = array('status' => 1, 'msg' => JText::_('FORM_UPDATED'), 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('FORM_NOT_UPDATED'), 'data' => $result);
            }
        }

        echo json_encode((object)$tab);
        exit;
    }


    public function getformbyid() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $jinput = JFactory::getApplication()->input;

	        $id = $jinput->getInt('id');

            $form = $this->m_form->getFormById($id);
            if (!empty($form)) {
                $tab = array('status' => 1, 'msg' => JText::_('FORM_RETRIEVED'), 'data' => $form);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_FORM'), 'data' => $form);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

	public function getFormByFabrikId() {
		$user = JFactory::getUser();
		$response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));

		if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$jinput = JFactory::getApplication()->input;
			$id = $jinput->getInt('form_id');

			if (!empty($id)) {
				$form = $this->m_form->getFormByFabrikId($id);
				if (!empty($form)) {
					$response = array('status' => true, 'msg' => JText::_('FORM_RETRIEVED'), 'data' => $form);
				} else {
					$response['msg'] = JText::_('ERROR_CANNOT_RETRIEVE_FORM');
				}
			} else {
				$response['msg'] = JText::_('MISSING_PARAMS');
			}
		}

		echo json_encode((object)$response);
		exit;
	}

    public function getalldocuments() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $jinput = JFactory::getApplication()->input;

	        $prid = $jinput->getInt('prid');
	        $cid = $jinput->getInt('cid');

            $form = $this->m_form->getAllDocuments($prid, $cid);

            if (!empty($form)) {
                $tab = array('status' => 1, 'msg' => JText::_('DOCUMENTS_RETRIEVED'), 'data' => $form);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_DOCUMENTS'), 'data' => $form);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }


    public function getundocuments() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $form = $this->m_form->getUnDocuments();

            if (!empty($form)) {
                $tab = array('status' => 1, 'msg' => JText::_('DOCUMENTS_RETRIEVED'), 'data' => $form);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_RETRIEVE_DOCUMENTS'), 'data' => $form);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

	public function getAttachments() {
		$response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));
		$user = JFactory::getUser();

		if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
			$attachments = $this->m_form->getAttachments();
			if (!empty($attachments)) {
				$response['status'] = true;
				$response['msg'] =  JText::_('DOCUMENTS_RETRIEVED');
				$response['data'] = $attachments;
			} else {
				$response['msg'] =  JText::_('ERROR_CANNOT_RETRIEVE_DOCUMENTS');
			}
		}

		echo json_encode((object)$response);
		exit;
	}

    public function getdocumentsusage() {
        $user = JFactory::getUser();
        $tab = array('status' => 0, 'msg' => JText::_("ACCESS_DENIED"));

        if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $jinput = JFactory::getApplication()->input;
            $document_ids = $jinput->getString('documentIds');
            $document_ids = explode(',', $document_ids);

            if (!empty($document_ids)) {
                $forms = $this->m_form->getDocumentsUsage($document_ids);

                if (!empty($forms)) {
                    $tab['status'] = 1;
                    $tab['msg'] = 'SUCCESS';
                    $tab['data'] = $forms;
                } else {
                    $tab['msg'] = JText::_("ERROR_GETTING_DOCUMENT_USAGE");
                }
            } else {
                $tab['msg'] = JText::_('MISSING_PARAMS');
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function updatemandatory() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $did = $jinput->getInt('did');
            $prid = $jinput->getInt('prid');
            $cid = $jinput->getInt('cid');

            $documents = $this->m_form->updateMandatory($did,$prid,$cid);

            if ($documents) {
                $tab = array('status' => 1, 'msg' => JText::_('DOCUMENTS_UPDATED'), 'data' => $documents);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_UPDATE_DOCUMENTS'), 'data' => $documents);
            }
        }

        echo json_encode((object)$tab);
        exit;
    }

    public function adddocument() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $did = $jinput->getInt('did');
            $prid = $jinput->getInt('prid');
            $cid = $jinput->getInt('cid');

            $documents = $this->m_form->addDocument($did, $prid, $cid);

            if ($documents) {
                $tab = array('status' => 1, 'msg' => JText::_('DOCUMENTS_UPDATED'), 'data' => $documents);
            } else {
                $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_UPDATE_DOCUMENTS'), 'data' => $documents);
            }
        }

        echo json_encode((object)$tab);
        exit;
    }


    public function removedocument() {
	    $user = JFactory::getUser();

	    if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
		    $result = 0;
		    $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
	    } else {
	        $jinput = JFactory::getApplication()->input;

	        $did = $jinput->getInt('did');
	        $prid = $jinput->getInt('prid');
	        $cid = $jinput->getInt('cid');

	        $documents = $this->m_form->removeDocument($did, $prid, $cid);

	        if ($documents) {
	            $tab = array('status' => 1, 'msg' => JText::_('DOCUMENTS_UPDATED'), 'data' => $documents);
	        } else {
	            $tab = array('status' => 0, 'msg' => JText::_('ERROR_CANNOT_UPDATE_DOCUMENTS'), 'data' => $documents);
	        }
        }

        echo json_encode((object)$tab);
        exit;
    }


    public function deletedocument() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $did = $jinput->getInt('did');

            $state = $this->m_form->deleteDocument($did);

            $tab = array('status' => $state, 'msg' => JText::_('DOCUMENT_DELETED'));

        }

        echo json_encode((object)$tab);
        exit;
    }


     public function getFormsByProfileId() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $jinput = JFactory::getApplication()->input;

	        $profile_id = $jinput->getInt('profile_id');

            $form = $this->m_form->getFormsByProfileId($profile_id);

            if (!empty($form)) {
                $tab = array('status' => 1, 'msg' => 'worked', 'data' => $form);
            } else {
                $tab = array('status' => 0, 'msg' => 'Doesn t worked', 'data' => $form);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getDocuments() {
	    $response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));
	    $user = JFactory::getUser();

        if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $jinput = JFactory::getApplication()->input;
            $profile_id = $jinput->getInt('pid');

			if (!empty($profile_id)) {
				$documents = $this->m_form->getDocumentsByProfile($profile_id);

				if (!empty($documents)) {
					$response = array('status' => true, 'msg' => 'worked', 'data' => $documents);
				} else {
					$response = array('status' => true, 'msg' => 'No documents attached to profile found', 'data' => $documents);
				}
			} else {
				$response = array('status' => false, 'msg' => 'Missing parameters');
			}
        }

        echo json_encode((object)$response);
        exit;
    }

    public function reorderDocuments() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $documents = $jinput->getString('documents');
            $documents = json_decode($documents, true);
            $documents = $this->m_form->reorderDocuments($documents);

            if (!empty($documents)) {
                $tab = array('status' => 1, 'msg' => 'worked', 'data' => $documents);
            } else {
                $tab = array('status' => 0, 'msg' => 'Doesn t worked', 'data' => $documents);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function removeDocumentFromProfile() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $did = $jinput->getInt('did');

            $result = $this->m_form->removeDocumentFromProfile($did);

            if (!empty($result)) {
                $tab = array('status' => 1, 'msg' => 'worked', 'data' => $result);
            } else {
                $tab = array('status' => 0, 'msg' => 'Doesn t worked', 'data' => $result);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }

    public function getgroupsbyform() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $form_id = $jinput->getInt('form_id');

            $form = $this->m_form->getGroupsByForm($form_id);

            if (!empty($form)) {
                $tab = array('status' => 1, 'msg' => 'worked', 'data' => $form);
            } else {
                $tab = array('status' => 0, 'msg' => 'Doesn t worked', 'data' => $form);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }


    public function getProfileLabelByProfileId() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $jinput = JFactory::getApplication()->input;

	        $profile_id = $jinput->getInt('profile_id');

            $form = $this->m_form->getProfileLabelByProfileId($profile_id);

            if (!empty($form)) {
                $tab = array('status' => 1, 'msg' => 'worked', 'data' => $form);
            } else {
                $tab = array('status' => 0, 'msg' => 'Doesn t worked', 'data' => $form);
            }
        }
        echo json_encode((object)$tab);
        exit;
    }


    public function getfilesbyform() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $jinput = JFactory::getApplication()->input;

	        $profile_id = $jinput->getInt('pid');

            $files = $this->m_form->getFilesByProfileId($profile_id);

            $tab = array('status' => 1, 'msg' => 'worked', 'data' => $files);
        }
        echo json_encode((object)$tab);
        exit;
    }


    public function getassociatedcampaign() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $jinput = JFactory::getApplication()->input;

	        $profile_id = $jinput->getInt('pid');

            $campaigns = $this->m_form->getAssociatedCampaign($profile_id);

            $tab = array('status' => 1, 'msg' => 'worked', 'data' => $campaigns);
        }
        echo json_encode((object)$tab);
        exit;
    }
    public function getassociatedprogram() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $tab = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $form_id = $jinput->getInt('fid');

            $campaigns = $this->m_form->getAssociatedProgram($form_id);

            $tab = array('status' => 1, 'msg' => 'worked', 'data' => $campaigns);
        }
        echo json_encode((object)$tab);
        exit;
    }


    public function affectcampaignstoform() {
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
	        $jinput = JFactory::getApplication()->input;

	        $prid = $jinput->getInt('prid');
	        $campaigns = $jinput->getRaw('campaigns');

            $changeresponse = $this->m_form->affectCampaignsToForm($prid, $campaigns);
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function getsubmittionpage(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $prid = $jinput->getInt('prid');

            $submittionpage = $this->m_form->getSubmittionPage($prid);
        }
        echo json_encode((object)$submittionpage);
        exit;
    }

    public function getAccess(){
        $user = JFactory::getUser();

        if (EmundusHelperAccess::asAdministratorAccessLevel($user->id)) {
            $response = array('status' => 1, 'msg' => JText::_("ACCESS_SYSADMIN"), 'access' => true);
        } else {
            $response = array('status' => 0, 'msg' => JText::_("ACCESS_REFUSED"), 'access' => false);
        }
        echo json_encode((object)$response);
        exit;
    }

    public function getActualLanguage(){
        $lang = JFactory::getLanguage();

        if ($lang) {
            $response = array('status' => 1, 'msg' => substr($lang->getTag(), 0, 2));
        } else {
            $response = array('status' => 0, 'msg' =>  JText::_("ACCESS_REFUSED"));
        }

        echo json_encode((object)$response);
        exit;
    }

    public function deletemodeldocument(){
        $user = JFactory::getUser();

        if (!EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $result = 0;
            $changeresponse = array('status' => $result, 'msg' => JText::_("ACCESS_DENIED"));
        } else {
            $jinput = JFactory::getApplication()->input;

            $did = $jinput->getInt('did');

            $result = $this->m_form->deleteModelDocument($did);

            $changeresponse = array('allowed' => $result, 'msg' => 'worked');
        }
        echo json_encode((object)$changeresponse);
        exit;
    }

    public function getdatabasejoinoptions(){
        $user = JFactory::getUser();

        $jinput = JFactory::getApplication()->input;

        $table_name = $jinput->getString('table_name');
        $column_name = $jinput->getString('column_name');
        $value = $jinput->getString('value');
        $concat_value = $jinput->getString('concat_value');
        $where_clause = $jinput->getString('where_clause');

        $options = $this->m_form->getDatabaseJoinOptions($table_name, $column_name, $value, $concat_value, $where_clause);

        echo json_encode((object)array('status' => 1, 'msg' => 'worked', 'options' => $options));
        exit;
    }

    public function checkcandocbedeleted() {
        $user = JFactory::getUser();
        $response = array('status' => 0, 'msg' => JText::_("ACCESS_DENIED"));

        if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $jinput = JFactory::getApplication()->input;
            $docid = $jinput->getInt('docid');
            $prid = $jinput->getInt('prid');

            if (!empty($prid) && !empty($docid)) {
                $canBeDeleted = $this->m_form->checkIfDocCanBeRemovedFromCampaign($docid, $prid);

                $response['status'] = 1;
                $response['msg'] = JText::_("SUCCESS");
                $response['data'] = $canBeDeleted;
            } else {
                $response['msg'] = JText::_("MISSING_PARAMS");
            }
        }

        echo json_encode((object)$response);
        exit;
    }

    public function getpagegroups()
    {
        $user = JFactory::getUser();
        $response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));

        if (EmundusHelperAccess::asCoordinatorAccessLevel($user->id)) {
            $jinput = JFactory::getApplication()->input;
            $formId = $jinput->getInt('form_id');

            if (!empty($formId)) {
                $groups = $this->m_form->getGroupsByForm($formId);

                if ($groups !== false) {
                    $response['msg'] = JText::_('SUCCESS');
                    $response['status'] = true;
                    $response['data'] = ['groups' => $groups];
                } else {
                    $response['msg'] = JText::_('FAILED');
                }
            } else {
                $response['msg'] = JText::_('MISSING_PARAMS');
            }
        }

        echo json_encode((object)$response);
        exit;
    }
}

