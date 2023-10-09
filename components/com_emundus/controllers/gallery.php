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
					$gallery->label = ['fr' => $gallery->title, 'en' => $gallery->title];

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
}
