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

use Joomla\CMS\Factory;

/**
 * Campaign Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusControllerGallery extends JControllerLegacy
{
    private $_model;
	private $_user;

	protected $app;

    public function __construct($config = array())
    {
        parent::__construct($config);

		$this->app = Factory::getApplication();
        $this->_model = $this->getModel('gallery');

		$this->_user = $this->app->getIdentity();
    }

	public function getall()
	{
		$response = array('status' => false, 'msg' => JText::_('ACCESS_DENIED'));

		if (EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$filter = $this->input->getString('filter', '');
			$sort = $this->input->getString('sort', '');
			$recherche = $this->input->getString('recherche', '');
			$lim = $this->input->getInt('lim', 25);
			$page = $this->input->getInt('page', 0);

			$galleries = $this->_model->getGalleries($filter, $sort, $recherche, $lim, $page);

			if (count($galleries) > 0) {
				// this data formatted is used in onboarding lists
				foreach($galleries['datas'] as $key => $gallery) {
					$gallery->label = ['fr' => $gallery->label, 'en' => $gallery->label];

					$start_date = date('d/m/Y H\hi', strtotime($gallery->start_date));
					$end_date = date('d/m/Y H\hi', strtotime($gallery->end_date));

					$state_values = [
						[
							'key' => JText::_('COM_EMUNDUS_ONBOARD_STATE'),
							'value' => $gallery->published ? JText::_('PUBLISHED') : JText::_('COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH'),
							'classes' => $gallery->published ? 'em-p-5-12 em-font-weight-600 em-bg-main-100 em-text-neutral-900 em-font-size-14 em-border-radius' : 'em-p-5-12 em-font-weight-600 em-bg-neutral-200 em-text-neutral-900 em-font-size-14 em-border-radius',
						]
					];

					$gallery->additional_columns = [
						[
							'key' => JText::_('COM_EMUNDUS_ONBOARD_STATE'),
							'type' => 'tags',
							'values' => $state_values,
							'display' => 'table'
						],
						[
							'type' => 'tags',
							'key' => JText::_('COM_EMUNDUS_ONBOARD_STATE'),
							'values' => [
								$state_values[0],
							],
							'classes' => 'em-mt-8 em-mb-8',
							'display' => 'blocs'
						]
					];
					$galleries['datas'][$key] = $gallery;
				}

				$response = array('status' => true, 'msg' => JText::_('CAMPAIGNS_RETRIEVED'), 'data' => $galleries);
			} else {
				$response = array('status' => false, 'msg' => JText::_('NO_CAMPAIGNS'), 'data' => $galleries);
			}
		}

		echo json_encode((object)$response);
		exit;
	}

	public function getgallery()
	{
		$response = array('status' => 1, 'msg' => '', 'data' => []);

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$response['status'] = 0;
			$response['msg'] = JText::_('ACCESS_DENIED');
		}
		else {
			$id = $this->input->getInt('id', 0);

			if(!empty($id)) {
				$response['data'] = $this->_model->getGalleryById($id);
			}
		}

		echo json_encode((object)$response);
		exit;
	}

	public function creategallery()
	{
		$response = array('status' => 1, 'msg' => JText::_('GALLERY_ADDED'), 'data' => []);

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$response['status'] = 0;
			$response['msg'] = JText::_('ACCESS_DENIED');
		}
		else {
			$data = $this->input->getArray();

			$response['data'] = $this->_model->createGallery($data, $this->_user);
		}

		echo json_encode((object)$response);
		exit;
	}

	public function getelements()
	{
		$response = array('status' => 1, 'msg' => '', 'data' => []);

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$response['status'] = 0;
			$response['msg'] = JText::_('ACCESS_DENIED');
		}
		else {
			$lid = $this->input->getInt('list_id', 0);
			$cid = $this->input->getInt('campaign_id', 0);

			if(!empty($cid)) {
				$response['data']['elements'] = $this->_model->getElements($cid,$lid);

				if(!empty($response['data'])) {
					$keys_to_remove = [];

					$response['data']['simple_fields'] = [];
					$response['data']['choices_fields'] = [];
					$response['data']['description_fields'] = [];

					foreach ($response['data']['elements'] as $key => $element) {

						$elts_to_remove = [];
						foreach ($element['elements'] as $index => $elt) {
							if(empty($elt->label)) {
								$elts_to_remove[] = $index;
								continue;
							}

							if (!in_array($elt->plugin, ['checkbox'])) {

								if(!isset($response['data']['simple_fields'][$key]['label'])) {
									$response['data']['simple_fields'][$key]['label'] = $element['label'];
								}
								$response['data']['simple_fields'][$key]['elements'][] = $elt;
							}

							if (in_array($elt->plugin, ['checkbox', 'dropdown', 'radiobutton', 'databasejoin'])) {
								if(!isset($response['data']['choices_fields'][$key]['label'])) {
									$response['data']['choices_fields'][$key]['label'] = $element['label'];
								}
								$response['data']['choices_fields'][$key]['elements'][] = $elt;
							}

							if (in_array($elt->plugin, ['textarea', 'field'])) {
								if(!isset($response['data']['description_fields'][$key]['label'])) {
									$response['data']['description_fields'][$key]['label'] = $element['label'];
								}
								$response['data']['description_fields'][$key]['elements'][] = $elt;
							}
						}

						foreach ($elts_to_remove as $elt) {
							unset($response['data']['elements'][$key]['elements'][$elt]);
						}

						if(empty($response['data']['elements'][$key]['elements'])) {
							$keys_to_remove[] = $key;
						}


						$response['data']['elements'][$key]['elements'] = array_values($response['data']['elements'][$key]['elements']);
					}

					foreach ($keys_to_remove as $key) {
						unset($response['data']['elements'][$key]);
					}

					$response['data']['elements'] = array_values($response['data']['elements']);
					$response['data']['description_fields'] = array_values($response['data']['description_fields']);
					$response['data']['choices_fields'] = array_values($response['data']['choices_fields']);
					$response['data']['simple_fields'] = array_values($response['data']['simple_fields']);
				}
			}
		}

		echo json_encode((object)$response);
		exit;
	}

	public function updateattribute()
	{
		$response = array('status' => 1, 'msg' => '', 'data' => []);

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$response['status'] = 0;
			$response['msg'] = JText::_('ACCESS_DENIED');
		}
		else {
			$gid = $this->input->getInt('gallery_id', 0);
			$attribute = $this->input->getString('attribute', '');
			$value = $this->input->getString('value', '');

			$response['data'] = $this->_model->updateAttribute($gid,$attribute,$value);
		}

		echo json_encode((object)$response);
		exit;
	}

	public function updategallerylist()
	{
		$response = array('status' => 1, 'msg' => '', 'data' => []);

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$response['status'] = 0;
			$response['msg'] = JText::_('ACCESS_DENIED');
		}
		else {
			$lid = $this->input->getInt('list_id', 0);
			$attribute = $this->input->getString('attribute', '');
			$value = $this->input->getString('value', '');

			$response['data'] = $this->_model->updateList($lid,$attribute,$value);
		}

		echo json_encode((object)$response);
		exit;
	}

	public function getattachments()
	{
		$response = array('status' => 1, 'msg' => '', 'data' => []);

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$response['status'] = 0;
			$response['msg'] = JText::_('ACCESS_DENIED');
		}
		else {
			$cid = $this->input->getInt('campaign_id', 0);

			if(!empty($cid)) {
				$response['data'] = $this->_model->getAttachments($cid);
			}
		}

		echo json_encode((object)$response);
		exit;
	}

	public function editprefilter()
	{
		$response = array('status' => 1, 'msg' => '', 'data' => []);

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$response['status'] = 0;
			$response['msg'] = JText::_('ACCESS_DENIED');
		}
		else {
			$lid = $this->input->getInt('list_id', 0);
			$value = $this->input->getString('value', '');

			$response['data'] = $this->_model->editPrefilter($lid,$value);
		}

		echo json_encode((object)$response);
		exit;
	}

	public function addtab()
	{
		$response = array('status' => 1, 'msg' => '', 'data' => 0);

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$response['status'] = 0;
			$response['msg'] = JText::_('ACCESS_DENIED');
		}
		else {
			$gallery_id = $this->input->getInt('gallery_id', 0);
			$title = $this->input->getString('title', '');

			$response['data'] = $this->_model->addTab($gallery_id,$title);
		}

		echo json_encode((object)$response);
		exit;
	}

	public function updatetabtitle()
	{
		$response = array('status' => 1, 'msg' => '');

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$response['status'] = 0;
			$response['msg'] = JText::_('ACCESS_DENIED');
		}
		else {
			$tab_id = $this->input->getInt('tab_id', 0);
			$title = $this->input->getString('title', '');

			$response['status'] = $this->_model->updateTabTitle($tab_id,$title);
		}

		echo json_encode((object)$response);
		exit;
	}

	public function addfield()
	{
		$response = array('status' => 1, 'msg' => '');

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$response['status'] = 0;
			$response['msg'] = JText::_('ACCESS_DENIED');
		}
		else {
			$tab_id = $this->input->getInt('tab_id', 0);
			$field = $this->input->getString('field', '');

			$response['status'] = $this->_model->addField($tab_id,$field);
		}

		echo json_encode((object)$response);
		exit;
	}

	public function removefield()
	{
		$response = array('status' => 1, 'msg' => '');

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$response['status'] = 0;
			$response['msg'] = JText::_('ACCESS_DENIED');
		}
		else {
			$tab_id = $this->input->getInt('tab_id', 0);
			$field = $this->input->getString('field', '');

			$response['status'] = $this->_model->removeField($tab_id,$field);
		}

		echo json_encode((object)$response);
		exit;
	}

	public function updatefieldsorder()
	{
		$response = array('status' => 1, 'msg' => '');

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$response['status'] = 0;
			$response['msg'] = JText::_('ACCESS_DENIED');
		}
		else {
			$tab_id = $this->input->getInt('tab_id', 0);
			$fields = $this->input->getString('fields', '');

			$response['status'] = $this->_model->updateFieldsOrder($tab_id,$fields);
		}

		echo json_encode((object)$response);
		exit;
	}
}
