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

require_once JPATH_COMPONENT.'/controller.php';
require_once(JPATH_COMPONENT.DS.'models'.DS.'contact.php');
require_once(JPATH_COMPONENT.DS.'helpers'.DS.'jcrm.php');

/**
 * Contacts list controller class.
 */
class JcrmControllerContacts extends JcrmController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param string $name
	 * @param string $prefix
	 * @param array  $config
	 *
	 * @return bool|JModelLegacy
	 * @since    1.6
	 */
	public function &getModel($name = 'Contacts', $prefix = 'JcrmModel', $config = array()) {
		$m_contacts = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $m_contacts;
	}

	/**
	 *
	 */
	public function getcontacts() {
		$jinput = JFactory::getApplication()->input;
		$m_contacts = $this->getModel();
		$id = $jinput->getInt('group_id', null);
		$index = $jinput->getInt('index', 0);
		$q = $jinput->getString('q', "");
		$type = $jinput->getInt('type', 1);

		if ($index < 0) {
			$index = 0;
		}
		if ($id == 0)  {
			$id = null;
		}

		$res = new stdClass();
		$res->contacts = $m_contacts->getAllContacts($id, $index, $q, $type);
		$res->nbContacts = $m_contacts->getNbContacts($id, $type);
		echo  json_encode($res);
		exit();
	}

	/**
	 *
	 */
	public function getorganisations() {
		$jinput = JFactory::getApplication()->input;
		$org = $jinput->getString('org', "");
		$m_contacts = $this->getModel();
		$orgs = $m_contacts->getOrgas($org);
		if (!is_string($orgs)) {
			echo json_encode($orgs);
		} else {
			echo json_encode(array('error' => JText::_('ERROR'), 'msg' => $orgs));
		}
		exit();
	}


	/**
	 *
	 */
	public function getgroups()
	{
		$m_contacts = $this->getModel();
		$orgs = $m_contacts->getGroups();
		if(!is_string($orgs))
		{
			if(is_null($orgs))
			{
				$orgs = array();
			}
			echo json_encode($orgs);
		}
		else
		{
			echo json_encode(array('error' => JText::_('ERROR'), 'msg' => $orgs));
		}
		exit();
	}

	/**
	 *
	 */
	public function export()
	{
		$request_body 	= (object) json_decode(file_get_contents('php://input'));
		$groups 		= $request_body->contacts->groups;
		$contacts 		= $request_body->contacts->contacts;
		$orgExport 		= $request_body->orgexport;

		$m_contact = new JcrmModelContact();

		if ($orgExport != 'direct')
			$orgList = $m_contact->getContactIdByOrg($contacts);
		else
			$orgList = array();

		$contacts = array_unique(array_merge($contacts, $orgList));

		if (!empty($groups))
			$groupList = $m_contact->getContactIdByGroup($groups);
		else
			$groupList = array();

		$contactIds = array_unique(array_merge($contacts, $groupList));

		$contacts = $m_contact->getContacts($contactIds);

		//type == 0 => csv
		if ($request_body->export == 0)
			$path = JcrmFrontendHelper::buildCSV($contacts);
		else
			$path = JcrmFrontendHelper::buildVcard($contacts);

		$url = JURI::base();
		$url .= 'index.php?option=com_jcrm&task=contacts.download&file='.$path;

		$res = array("status" => true, 'msg' => JText::_('CONTACT_EXPORT_SUCCESS_CLICK_LINK_BELOW'), 'link' => $url, 'linkMsg' => JText::_('CONTACT_CLICK_TO_DOWNLOAD'));
		echo json_encode((object)$res);
		exit;
	}


	public function download()
	{
		$jinput = JFactory::getApplication()->input;
		$file = $jinput->getString('file', null);
		$name = explode('-', basename($file));
		$name = $name[1];
		if(!is_null($file))
		{
				header('HTTP/1.1 200 OK');
				header('Cache-Control: no-cache, must-revalidate');
				header("Pragma: no-cache");
				header("Expires: 0");
				header("Content-type: text/csv");
				header("Content-Disposition: attachment; filename=$name");
				readfile(JPATH_BASE.DS.'tmp'. DS . $file);
				exit;

		}
		else
			exit;
	}
}