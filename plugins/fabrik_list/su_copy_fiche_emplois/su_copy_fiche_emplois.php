<?php
/**
 * List Copy Row plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.list.copy
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-list.php';

/**
 * Add an action button to the list to copy rows
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.list.copy
 * @since       3.0
 */
class PlgFabrik_ListSu_copy_fiche_emplois extends PlgFabrik_List
{

	/**
	 * Button prefix
	 *
	 * @var string
	 */ 
	protected $buttonPrefix = 'copy';

	/**
	 * Prep the button if needed
	 *
	 * @param   object  $params  plugin params
	 * @param   object  &$model  list model
	 * @param   array   &$args   arguements
	 *
	 * @return  bool;
	 */

	public function button(&$args)
	{
		parent::button($args);
		return true;
	}

	/**
	 * Get the button label
	 *
	 * @return  string
	 */
	protected function buttonLabel()
	{
		return $this->getParams()->get('copytable_button_label', parent::buttonLabel());
	}

	/**
	 * Get button image
	 *
	 * @since   3.1b
	 *
	 * @return   string  image
	 */

	protected function getImageName()
	{
		$img = parent::getImageName();

		if (FabrikWorker::j3() && $img === 'copy.png')
		{
			$img = 'copy';
		}

		return $img;
	}

	/**
	 * Get the parameter name that defines the plugins acl access
	 *
	 * @return  string
	 */

	protected function getAclParam()
	{
		return 'copytable_access';
	}

	/**
	 * Can the plug-in select list rows
	 *
	 * @return  bool
	 */

	public function canSelectRows()
	{
		return true;
	}

	/**
	 * Do the plug-in action
	 *
	 * @param   object  $params  plugin parameters
	 * @param   object  &$model  list model
	 * @param   array   $opts    custom options
	 *
	 * @return  bool
	 */

	public function process($opts = array())
	{
		$model = $this->getModel();
		$ids = JRequest::getVar('ids', array(), 'method', 'array');
		$formModel = $model->getFormModel();
		$user=JFactory::getSession()->get('emundusUser');
		$db = FabrikWorker::getDbo();
		$config = JFactory::getConfig();
        
        $jdate = JFactory::getDate();
        $timezone = new DateTimeZone( $config->get('offset') );
    	$jdate->setTimezone($timezone);
        $now = $jdate->toSql();

		$query = 'SELECT id 
					FROM #__emundus_setup_campaigns
					WHERE published=1 
					AND training like "utc-dfp-dri" 
					AND start_date<="'.$now.'" 
					AND end_date>"'.$now.'" 
					LIMIT 0,1';
		$db->setQuery($query);
		$campaign_id = $db->loadResult();

		if ($campaign_id>0) {
			if($model->copyRows($ids)){
				$query = 'select eee.id from #__emundus_emploi_etudiant eee where user='.$user->id.' order by id desc LIMIT 0,1';
				$db->setQuery($query);
				$id = $db->loadResult();

				$query = 'UPDATE #__emundus_emploi_etudiant e 
					SET e.date_time=NOW(), e.published=1, e.valide_comite='.$db->Quote('-1').', e.valide='.$db->Quote('-1').',
					    e.campaign_id='.$campaign_id.', e.date_debut=NULL, e.date_fin=NULL,
					    e.date_limite=NULL, e.user_modify=NULL, e.date_modify=NULL, e.etablissement='.$user->university_id.'
					WHERE e.id='.$id;

				$db->setQuery($query);
				$res = $db->execute();

				if(count($ids) == 1) {
					$url = JUri::base().'fiches-emplois/mes-fiches/form/124/'.$id;
					JFactory::getApplication()->redirect($url, JText::sprintf('PLG_LIST_ROW_COPIED', count($ids)), 'INFO');
				}
			}
		} else {
			$res = false;
		}
		return $res;
	}

	/**
	 * Get the message generated in process()
	 *
	 * @param   int  $c  plugin render order
	 *
	 * @return  string
	 */

	public function process_result($c)
	{
		$ids = JRequest::getVar('ids', array(), 'method', 'array');
		$link = '';
		//if(count($ids) == 1) {
		//	$link = ' <a class="btn fabrik_view fabrik__rowlink btn-default" href="fiches-emplois/mes-fiches/form/124/'.array_key_first($ids).'" target="_blank">modifier</a>';
		//}
		return JText::sprintf('PLG_LIST_ROWS_COPIED', count($ids)).$link;
	}


	/**
	 * Return the javascript to create an instance of the class defined in formJavascriptClass
	 *
	 * @param   array  $args  Array [0] => string table's form id to contain plugin
	 *
	 * @return bool
	 */
	public function onLoadJavascriptInstance($args)
	{
		parent::onLoadJavascriptInstance($args);
		$opts = $this->getElementJSOptions();
		$opts = json_encode($opts);
		$this->jsInstance = "new FbListSu_copy_fiche_emplois($opts)";

		return true;
	}

	/**
	 * Load the AMD module class name
	 *
	 * @return string
	 */
	public function loadJavascriptClassName_result()
	{
		return 'FbListSu_copy_fiche_emplois';
	}

}
