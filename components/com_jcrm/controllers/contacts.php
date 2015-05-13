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
	 * @since	1.6
	 */
	public function &getModel($name = 'Contacts', $prefix = 'JcrmModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	/**
	 *
	 */
	public function getcontacts()
	{
		$jinput = JFactory::getApplication()->input;
		$model = $this->getModel();
		$id = $jinput->getInt('group_id', null);
		$index = $jinput->getInt('index', 0);
		$q = $jinput->getString('q', "");
		$type = $jinput->getInt('type', 1);

		if($index < 0)
		{
			$index = 0;
		}
		if($id == 0)
		{
			$id = null;
		}

		$res = new stdClass();
		$res->contacts = $model->getAllContacts($id, $index, $q, $type);
		$res->nbContacts = $model->getNbContacts($id, $type);
		echo  json_encode($res);
		exit();
	}

	/**
	 *
	 */
	public function getorganisations()
	{
		$jinput = JFactory::getApplication()->input;
		$org = $jinput->getString('org', "");
		$model = $this->getModel();
		$orgs = $model->getOrgas($org);
		if(!is_string($orgs))
		{
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
	public function getgroups()
	{
		$model = $this->getModel();
		$orgs = $model->getGroups();
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
		$request_body = (object) json_decode(file_get_contents('php://input'));
		//list == 0 id represent just one user, list == 1 id represent the whole list or a group

		$groups = $request_body->contacts->groups;
		$contacts = $request_body->contacts->contacts;
		$model = new JcrmModelContact();
		if(!empty($groups))
			$groupList = $model->getContactIdByGroup($groups);
		else
			$groupList = array();
		$contactIds = array_unique(array_merge($contacts, $groupList));
		$contacts = $model->getContacts($contactIds);
		//type == 0 => csv
		if($request_body->export == 0)
		{
			$path = JcrmFrontendHelper::buildCSV($contacts);
		}
		else
		{
			$path = JcrmFrontendHelper::buildVcard($contacts);
		}
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