<?php
/**
 * @version 2: emundusSendEmail 2020-03-18 Hugo Moracchini
 * @package Fabrik
 * @copyright Copyright (C) 2020 emundus.fr. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Redirection et chainage des formulaires suivant le profile de l'utilisateur
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
 * Create a Joomla user from the forms data
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.juseremundus
 * @since       3.0
 */

class PlgFabrik_FormEmundusSendemail extends plgFabrik_Form {
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

		if ($params->get($pname) == '') {
			return '';
		}

		$elementModel = FabrikWorker::getPluginManager()->getElementPlugin($params->get($pname));
		return $short ? $elementModel->getElement()->name : $elementModel->getFullName();
	}

	/**
	 * Get the fields value regardless of whether its in joined data or no
	 *
	 * @param   string  $pname    Params property name to get the value for
	 * @param   mixed   $default  Default value
	 *
	 * @return  mixed  value
	 */
	public function getParam($pname, $default = '') {
		$params = $this->getParams();

		if ($params->get($pname) == '') {
			return $default;
		}

		return $params->get($pname);
	}

	/**
	 * Main script.
	 *
	 * @return  bool
	 * @throws Exception
	 */
	public function onAfterProcess() {

		jimport('joomla.utilities.utility');
		jimport('joomla.log.log');
		JLog::addLogger(['text_file' => 'com_emundus.sendemail.php'], JLog::ALL, ['com_emundus']);

		include_once (JPATH_BASE.'/components/com_emundus/models/files.php');
		include_once (JPATH_BASE.'/components/com_emundus/controllers/messages.php');

		$app = JFactory::getApplication();
		$jinput = $app->input;
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

		$email_tmpl = $this->getParam('tmpl');
		$to = $this->getParam('to');
		$user_id = null;
        $fnum = $jinput->get->get('rowid');

		if (empty($email_tmpl)) {
			JLog::add('No email template defined.', JLog::ERROR, 'com_emundus');
			return false;
		}

		if (!empty($to)) {
			if ($to !== 'fnum') {
                $request = explode('___', $to);
			    $query->select($request[1])
                    ->from($request[0])
                    ->where($db->quoteName('fnum') . ' = ' . $db->quote($fnum));
			    $db->setQuery($query);
				$to = $db->loadResult();

				if($to == '') {
                    JLog::add('No email address.', JLog::ERROR, 'com_emundus');
				    return false;
                }

                $query->clear()
                    ->select('user')
                    ->from($request[0])
                    ->where($db->quoteName('fnum') . ' = ' . $db->quote($fnum));
                $db->setQuery($query);
                $user_id = $db->loadResult();
			}
		} else {
            JLog::add('No email address.', JLog::ERROR, 'com_emundus');
            return false;
		}


		// Génération de l'id du prochain fichier qui devra être ajouté par le referent
		$c_messages = new EmundusControllerMessages();

		if ($to === 'fnum') {
			if (empty($fnum)) {
				JLog::add('No fnum in form', JLog::ERROR, 'com_emundus');
				return false;
			} else {
				return $c_messages->sendEmail($fnum, $email_tmpl);
			}
		} else {
			return $c_messages->sendEmailNoFnum($to, $email_tmpl, null, $user_id, array(), $fnum);
		}
	}


	/**
	 * Raise an error - depends on whether you are in admin or not as to what to do
	 *
	 * @param   array   &$err    Form models error array
	 * @param   string   $field  Name
	 * @param   string   $msg    Message
	 *
	 * @return  void
	 * @throws Exception
	 */
	protected function raiseError(&$err, $field, $msg) {
		$app = JFactory::getApplication();

		if ($app->isClient('administrator')) {
			$app->enqueueMessage($msg, 'notice');
		} else {
			$err[$field][0][] = $msg;
		}
	}
}
