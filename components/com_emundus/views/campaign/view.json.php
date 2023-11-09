<?php


/**
 * @package      Joomla
 * @subpackage   eMundus
 * @link         http://www.emundus.fr
 * @copyright    Copyright (C) 2016 eMundus SAS. All rights reserved.
 * @license      GNU/GPL
 * @author       eMundus SAS - Benjamin Rivalland
 */

// no direct access

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

use Joomla\CMS\Factory;

/**
 * campaign View
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusViewCampaign extends JViewLegacy
{
	private $app;

	function __construct($config = array())
	{
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'models' . DS . 'campaign.php');

		$this->app = Factory::getApplication();

		parent::__construct($config);
	}

	function display($tpl = null)
	{

		$jinput  = $this->app->input;
		$request = $jinput->get->get('request');

		$m_campaign = new EmundusModelCampaign();

		switch ($request) {
			case 'years':
				$data = $m_campaign->getTeachingUnity();
				break;

			case 'campaigns':
				$data = $m_campaign->getAllCampaigns();
				break;

			default:
				// For retro-compatibility reasons, the default case is the CCI export.
				$data = $m_campaign->getCCITU();

				foreach ($data as $key => $row) {

					// Process city name
					$town        = preg_replace('/[0-9]+/', '', str_replace(" cedex", "", ucfirst(strtolower($row->location_city))));
					$town        = ucwords(strtolower($town), '\',. ');
					$beforeComma = strpos($town, "D'");
					if (!empty($beforeComma)) {
						$replace            = strpbrk($town, "D'");
						$row->location_city = substr_replace($town, lcfirst($replace), $beforeComma);
					}

					// Proccess address
					$row->location_address = ucfirst(strtolower($row->location_address));

					// Proccess URL
					$row->url = 'https://www.competencesetformation.fr/formation?rowid=' . $row->row_id;

					// Process tax.
					$row->prix_ttc = !empty($row->tax_rate);

					$data[$key] = $row;
				}
				break;
		}

		echo json_encode($data);
	}
}
