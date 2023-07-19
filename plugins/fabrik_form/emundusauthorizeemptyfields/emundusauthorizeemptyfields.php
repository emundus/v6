<?php
/**
 * @version 2: emundusauthorizeemptyfields 2022-04-04 Clément Bernard
 * @package Fabrik
 * @copyright Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Locks access to a file if the file is not of a certain status.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';
// include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');


/**
 * Authorizes specifics profile ids to save empty fields
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.emundusauthorizeemptyfields
 * @since       3.0
 */

class PlgFabrik_FormEmundusauthorizeemptyfields extends plgFabrik_Form {
    /**
     * Status field
     *
     * @var  string
     */
    protected $URLfield = '';

	/**
	 * Get an element name
	 *
	 * @param   string  $pname  Params property name to look up
	 * @param   bool    $short  Short (true) or full (false) element name, default false/full
	 *
	 * @return	string	element full name
	 */
	public function getFieldName($pname, $short = false) {
		$params = $this->getParams();

		if ($params->get($pname) == '')
			return '';

		$elementModel = FabrikWorker::getPluginManager()->getElementPlugin($params->get($pname));

		return $short ? $elementModel->getElement()->name : $elementModel->getFullName();
	}

	/**
	 * Get the fields value regardless of whether its in joined data or no
	 *
	 * @param   string  $pname    Params property name to get the value for
	 * @param   array   $data     Posted form data
	 * @param   mixed   $default  Default value
	 *
	 * @return  mixed  value
	 */
	public function getParam($pname, $default = '') {
		$params = $this->getParams();

		if ($params->get($pname) == '')
			return $default;

		return $params->get($pname);
	}

	/**
	 * Main script.
	 *
	 * @return  bool
	 */
	public function onError() {
		/* Autorise certains utilisateurs à enregistrer des champs vides */

		$is_authorized = false;
		$user = JFactory::getSession()->get('emundusUser');
		$user_id = $user->id;
		$user_profiles = $user->emProfiles;
		$target_profiles = preg_replace( '/[^0-9,]/', '', $this->getParam('profiles', ''));
		$target_profiles = explode(',', $target_profiles);


		$profiles = [];
		foreach($user_profiles as $profile) {
			if(in_array($profile->id, $target_profiles)) {
				$is_authorized = true;
			}
		}

		if($is_authorized === true) {
			$realErrors = false;
			$formModel = $this->getModel(); 
			$input = $this->app->input;
			$errors = $formModel->errors;
			foreach($errors as $key => $fields) {

			/* On vérifie si les champs relevés en erreur sont vides */
			/* If simple group */
			if(count($fields) == 1) {
			  if(count($fields[0])) {
			    if(empty($inputs->data[$key])) {
			      // echo $key . ' has error and empty<br>';
			      $formModel->errors[$key][0] = [];
			    } else {
			      $realErrors = true;
			    }
			  }
			}
			/* If repeat group */
			else {
			  foreach($fields as  $index => $field) {
			    if(count($field)) {
			      if(empty($inputs->data[$key][$index])) {
			        // echo $key . ' nb ' . $index . ' (repeat group) has error and empty<br>';
			        $formModel->errors[$key][$index] = [];
			      }
			      else {
			        $realErrors = true;
			      }
			    }
			  }
			}
			}

			/* S'il n'y a pas d'erreur ou que les seules erreurs rapportées sont sur des champs vides, on autorise l'enregistrement */
			if(!$realErrors) {
				$formModel->process();
			  	header('Location: '.$_SERVER['REQUEST_URI']);
			}
		}
	}
}
