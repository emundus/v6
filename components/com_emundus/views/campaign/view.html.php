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

/**
 * campaign View
 *
 * @package    Joomla
 * @subpackage eMundus
 * @since      5.0.0
 */
class EmundusViewCampaign extends JViewLegacy
{
	protected $active_campaigns;
	protected $my_campaigns;
	protected $pagination;
	protected $lists;

	function __construct($config = array())
	{
		require_once(JPATH_BASE . DS . 'components' . DS . 'com_emundus' . DS . 'helpers' . DS . 'access.php');

		parent::__construct($config);
	}

	function display($tpl = null)
	{
		$this->active_campaigns = $this->get('ActiveCampaign');

		$this->my_campaigns = $this->get('MyCampaign');

		$this->pagination = $this->get('Pagination');

		$state                           = $this->get('state');
		$this->lists['filter_order_Dir'] = $state->get('filter_order_Dir');
		$this->lists['filter_order']     = $state->get('filter_order');

		parent::display($tpl);
	}
}

?>