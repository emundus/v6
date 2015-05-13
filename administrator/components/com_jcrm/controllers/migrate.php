<?php
/**
 * @version     1.0.0
 * @package     com_jcrm
 * @copyright   Copyright (C) 2014. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      Décision Publique <dev@emundus.fr> - http://www.emundus.fr
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'jcrm.php');

/**
 * Emails list controller class.
 */
class JcrmControllerMigrate extends JControllerAdmin
{
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'migrate', $prefix = 'JcrmModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}


	public function migrate()
	{
		$model = $this->getModel();
		$tables = $model->canMigrate();
		if(empty($tables))
		{
			$model->emptyContacts();
			$oldOrgs = $model->getOldOrganisation();
			$newOrgs = array();
			foreach($oldOrgs as $oldOrg)
			{
				$newOrg = JcrmHelper::buildContactFromOrg($oldOrg);
				$newOrgs[] = $newOrg;
			}
			$model->addOrgs($newOrgs);
			$nbOldOrgs = count($oldOrgs);
			echo "<pre>";
				var_dump($nbOldOrgs);
			echo "</pre>";
			$nbNewOrg = $model->getNbOrgs();
			echo "<pre>";
				var_dump($nbNewOrg);
			echo "</pre>";
			if($nbOldOrgs != $nbNewOrg)
			{
				die("Difference du nombre dimport");
			}
			$oldContacts = $model->getOldContacts();
			$newContacts = array();
			foreach($oldContacts as $old)
			{
				$newContact = JcrmHelper::buildContactFromContactBk($old);
				$newContacts[] = $newContact;
			}

			$model->addContacts($newContacts);
			$nbOldCont = count($oldContacts);
			echo "<pre>";
			var_dump($nbOldCont);
			echo "</pre>";
			$nbNewCont = $model->getNbCont();
			echo "<pre>";
			var_dump($nbNewCont);
			echo "</pre>";
			$orgs = $model->getOrgs();
			foreach($orgs as $org)
			{
				$orgContacts = $model->getContactByOrgName($org->organisation);
				if(!empty($orgContacts))
				{
					$model->addContactToOrg($org->id, $orgContacts);
				}
			}
			$model->renameTable();
		}
		else
		{
			echo "<pre>";
				echo"<h2>Error missing database table</h2>";
			    var_dump($tables);
			echo "</pre>";
		}
	}


    
	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$input = JFactory::getApplication()->input;
		$pks = $input->post->get('cid', array(), 'array');
		$order = $input->post->get('order', array(), 'array');

		// Sanitize the input
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		JFactory::getApplication()->close();
	}
    
    
    
}