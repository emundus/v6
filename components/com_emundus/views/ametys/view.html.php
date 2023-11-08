<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
//error_reporting(E_ALL);
jimport('joomla.application.component.view');

/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */
class EmundusViewAmetys extends JViewLegacy
{
	protected $itemId;
	protected $task;
	protected $token;

	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	public function display($tpl = null)
	{
		// translation to load in javacript file ; /media/com_emundus/em_files.js
		// put it in com_emundus/emundus.php

		$current_user = JFactory::getUser();
		if (!EmundusHelperAccess::asCoordinatorAccessLevel($current_user->id))
			die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));

		$app                           = JFactory::getApplication();
		$params                        = JComponentHelper::getParams('com_emundus');
		$ametys_sync_default_eval      = $params->get('ametys_sync_default_eval', null);
		$ametys_sync_default_decision  = $params->get('ametys_sync_default_decision', null);
		$ametys_sync_default_synthesis = $params->get('ametys_sync_default_synthesis', null);
		$this->assignRef('ametys_sync_default_eval', $ametys_sync_default_eval);
		$this->assignRef('ametys_sync_default_decision', $ametys_sync_default_decision);
		$this->assignRef('ametys_sync_default_synthesis', $ametys_sync_default_synthesis);

		$document = JFactory::getDocument();
		//$document->addScript("media/com_emundus/lib/jquery-1.10.2.min.js" );
		//$document->addScript("media/com_emundus/lib/bootstrap-336/js/bootstrap.min.js" );
		$document->addScript("media/com_emundus/lib/Semantic-UI-CSS-master/components/daterangepicker/moment.min.js");
		$document->addScript("media/com_emundus/lib/Semantic-UI-CSS-master/components/daterangepicker/daterangepicker.min.js");
		//$document->addStyleSheet("media/com_emundus/lib/bootstrap-336/css/bootstrap.min.css" );
		$document->addStyleSheet("media/com_emundus/lib/Semantic-UI-CSS-master/components/daterangepicker/daterangepicker.min.css");

		// overide css
		$menu         = @JFactory::getApplication()->getMenu();
		$current_menu = $menu->getActive();
		$menu_params  = $menu->getParams($current_menu->id);

		$page_heading  = $menu_params->get('page_heading', '');
		$pageclass_sfx = $menu_params->get('pageclass_sfx', '');
		if (!empty($page_heading)) {
			$document->addStyleSheet("media/com_emundus/lib/Semantic-UI-CSS-master/components/site." . $page_heading . ".css");
		}

		$this->itemId = $current_menu->id;
		$this->task   = $app->input->getInt('task', null);
		$this->token  = $app->input->getInt('token', null);

		parent::display($tpl);
	}

}

?>

