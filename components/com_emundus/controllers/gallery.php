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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Campaign Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusControllerGallery extends BaseController
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

	/**
	 * Get all galleries
	 *
	 * @since version 1.40.0
	 */
	public function getall()
	{
		$response = array('status' => false, 'msg' => Text::_('ACCESS_DENIED'));

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
							'key' => Text::_('COM_EMUNDUS_ONBOARD_STATE'),
							'value' => $gallery->published ? Text::_('PUBLISHED') : Text::_('COM_EMUNDUS_ONBOARD_FILTER_UNPUBLISH'),
							'classes' => $gallery->published ? 'em-p-5-12 em-font-weight-600 em-bg-main-100 em-text-neutral-900 em-font-size-14 em-border-radius' : 'em-p-5-12 em-font-weight-600 em-bg-neutral-200 em-text-neutral-900 em-font-size-14 em-border-radius',
						]
					];

					$gallery->additional_columns = [
						[
							'key' => Text::_('COM_EMUNDUS_ONBOARD_STATE'),
							'type' => 'tags',
							'values' => $state_values,
							'display' => 'table'
						],
						[
							'type' => 'tags',
							'key' => Text::_('COM_EMUNDUS_ONBOARD_STATE'),
							'values' => [
								$state_values[0],
							],
							'classes' => 'em-mt-8 em-mb-8',
							'display' => 'blocs'
						]
					];
					$galleries['datas'][$key] = $gallery;
				}

				$response = array('status' => true, 'msg' => Text::_('CAMPAIGNS_RETRIEVED'), 'data' => $galleries);
			} else {
				$response = array('status' => false, 'msg' => Text::_('NO_CAMPAIGNS'), 'data' => $galleries);
			}
		}

		echo json_encode((object)$response);
		exit;
	}

	/**
	 * Get gallery by id
	 *
	 * @since version 1.40.0
	 */
	public function getgallery()
	{
		$response = array('status' => 0, 'msg' => Text::_('ACCESS_DENIED'), 'data' => []);

		if (EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$id = $this->input->getInt('id', 0);

			if(!empty($id)) {
				$gallery = $this->_model->getGalleryById($id);

				if (!empty($gallery->id)) {
					$response['data'] = $gallery;
					$response['msg'] = '';
					$response['status'] = 1;
				} else {
					$response['msg'] = Text::_('GALLERY_NOT_FOUND');
				}
			} else {
				$response['msg'] = Text::_('MISSING_PARAMS');
			}
		}

		echo json_encode((object)$response);
		exit;
	}

	/**
	 * Create gallery
	 *
	 * @since version 1.40.0
	 */
	public function creategallery()
	{
		$response = array('status' => 1, 'msg' => Text::_('GALLERY_ADDED'), 'data' => []);

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$response['status'] = 0;
			$response['msg'] = Text::_('ACCESS_DENIED');
		}
		else {
			$data = $this->input->getArray();

			$response['data'] = $this->_model->createGallery($data, $this->_user);
		}

		echo json_encode((object)$response);
		exit;
	}

	/**
	 * Get Fabrik elements by campaign id and Fabrik list id to setup gallery
	 *
	 * @since version 1.40.0
	 */
	public function getelements()
	{
		$response = array('status' => 1, 'msg' => '', 'data' => []);

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$response['status'] = 0;
			$response['msg'] = Text::_('ACCESS_DENIED');
		}
		else {
			$lid = $this->input->getInt('list_id', 0);
			$cid = $this->input->getInt('campaign_id', 0);

			if(!empty($cid)) {
				$elements = $this->_model->getElements($cid,$lid);

				if (!empty($elements)) {
					$keys_to_remove = [];
					$response['data']['elements'] = $elements;
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

	/**
	 * Update an attribute of gallery model
	 *
	 * @since version 1.40.0
	 */
	public function updateattribute()
	{
		$response = array('status' => 1, 'msg' => '', 'data' => []);

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$response['status'] = 0;
			$response['msg'] = Text::_('ACCESS_DENIED');
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

	/**
	 * Update an attribute of gallery Fabrik list
	 *
	 * @since version 1.40.0
	 */
	public function updategallerylist()
	{
		$response = array('status' => 1, 'msg' => '', 'data' => []);

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$response['status'] = 0;
			$response['msg'] = Text::_('ACCESS_DENIED');
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

	/**
	 * Get attachments available for logo and banner of gallery
	 *
	 * @since version 1.40.0
	 */
	public function getattachments()
	{
		$response = array('status' => 1, 'msg' => '', 'data' => []);

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$response['status'] = 0;
			$response['msg'] = Text::_('ACCESS_DENIED');
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

	/**
	 * Manage prefilter of Fabrik list
	 *
	 * @since version 1.40.0
	 */
	public function editprefilter()
	{
		$response = array('status' => 1, 'msg' => '', 'data' => []);

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$response['status'] = 0;
			$response['msg'] = Text::_('ACCESS_DENIED');
		}
		else {
			$lid = $this->input->getInt('list_id', 0);
			$value = $this->input->getString('value', '');

			$response['data'] = $this->_model->editPrefilter($lid,$value);
		}

		echo json_encode((object)$response);
		exit;
	}

	/**
	 * Add a tab to gallery details view
	 *
	 * @since version 1.40.0
	 */
	public function addtab()
	{
		$response = array('status' => 1, 'msg' => '', 'data' => 0);

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$response['status'] = 0;
			$response['msg'] = Text::_('ACCESS_DENIED');
		}
		else {
			$gallery_id = $this->input->getInt('gallery_id', 0);
			$title = $this->input->getString('title', '');

			$response['data'] = $this->_model->addTab($gallery_id,$title);
		}

		echo json_encode((object)$response);
		exit;
	}

	/**
	 * Delete a tab from gallery details view
	 *
	 * @since version 1.40.0
	 */
	public function deletetab()
	{
		$response = array('status' => false, 'msg' => '');

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$response['msg'] = Text::_('ACCESS_DENIED');
		}
		else {
			$tab_id = $this->input->getInt('tab_id', 0);

			$response['status'] = $this->_model->deleteTab($tab_id);
		}

		echo json_encode((object)$response);
		exit;
	}

	/**
	 * Update tab title in gallery details view
	 *
	 * @since version 1.40.0
	 */
	public function updatetabtitle()
	{
		$response = array('status' => 1, 'msg' => '');

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$response['status'] = 0;
			$response['msg'] = Text::_('ACCESS_DENIED');
		}
		else {
			$tab_id = $this->input->getInt('tab_id', 0);
			$title = $this->input->getString('title', '');

			$response['status'] = $this->_model->updateTabTitle($tab_id,$title);
		}

		echo json_encode((object)$response);
		exit;
	}

	/**
	 * Add field to a tab
	 *
	 * @since version 1.40.0
	 */
	public function addfield()
	{
		$response = array('status' => 1, 'msg' => '');

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$response['status'] = 0;
			$response['msg'] = Text::_('ACCESS_DENIED');
		}
		else {
			$tab_id = $this->input->getInt('tab_id', 0);
			$field = $this->input->getString('field', '');

			$response['status'] = $this->_model->addField($tab_id,$field);
		}

		echo json_encode((object)$response);
		exit;
	}

	/**
	 * Remove field from a tab
	 *
	 * @since version 1.40.0
	 */
	public function removefield()
	{
		$response = array('status' => 1, 'msg' => '');

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$response['status'] = 0;
			$response['msg'] = Text::_('ACCESS_DENIED');
		}
		else {
			$tab_id = $this->input->getInt('tab_id', 0);
			$field = $this->input->getString('field', '');

			$response['status'] = $this->_model->removeField($tab_id,$field);
		}

		echo json_encode((object)$response);
		exit;
	}

	/**
	 * Update fields order in a tab
	 *
	 * @since version 1.40.0
	 */
	public function updatefieldsorder()
	{
		$response = array('status' => 1, 'msg' => '');

		if (!EmundusHelperAccess::asPartnerAccessLevel($this->_user->id)) {
			$response['status'] = 0;
			$response['msg'] = Text::_('ACCESS_DENIED');
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
