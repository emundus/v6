<?php
/**
 * @version     2: emunduscollaborate
 * @package     Fabrik
 * @copyright   Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license     GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Accepter une invitation à collaborer sur un formulaire
 */

// No direct access
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;

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
class PlgFabrik_FormEmundusCollaborate extends plgFabrik_Form
{
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
	 * @return    string    element full name
	 */
	public function getFieldName($pname, $short = false)
	{
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
	public function getParam($pname, $default = '')
	{
		$params = $this->getParams();

		if ($params->get($pname) == '') {
			return $default;
		}

		return $params->get($pname);
	}

	public function onBeforeLoad()
	{
		$user = Factory::getUser();
		$formModel = $this->getModel();
		$app = Factory::getApplication();
		$key = $app->input->getString('key','');
		$redirect_url = $this->getParam('redirect_url', 'index.php?option=com_users&view=login');

		if(empty($key)) {
			$app->enqueueMessage(Text::_('PLG_FABRIK_FORM_EMUNDUSCOLLABORATE_ERROR_KEY'), 'error');
			$app->redirect('index.php');
		}

		$query = $this->_db->getQuery(true);

		$query->select('*')
			->from($this->_db->quoteName('#__emundus_files_request'))
			->where($this->_db->quoteName('keyid') . ' = ' . $this->_db->quote($key));
		$this->_db->setQuery($query);
		$collaboration = $this->_db->loadObject();

		if(empty($collaboration)) {
			$app->enqueueMessage(Text::_('PLG_FABRIK_FORM_EMUNDUSCOLLABORATE_ERROR_KEY'), 'error');
			$app->redirect('index.php');
		}

		if($collaboration->uploaded == 0) {
			$query->clear()
				->select('id')
				->from($this->_db->quoteName('#__users'))
				->where($this->_db->quoteName('email') . ' = ' . $this->_db->quote($collaboration->email));
			$this->_db->setQuery($query);
			$shared_user_id = $this->_db->loadResult();

			if(empty($shared_user_id)) {
				$formModel->data['jos_emundus_users___email'] = $collaboration->email;
			}
			else {
				$accepted = $this->acceptCollaboration($shared_user_id,$key);

				if($accepted) {
					PluginHelper::importPlugin('emundus','custom_event_handler');
					\Joomla\CMS\Factory::getApplication()->triggerEvent('onAfterAcceptCollaboration', [$shared_user_id, $key]);
					\Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onAfterAcceptCollaboration', ['user' => $shared_user_id, 'key' => $key]]);

					if($user->guest) {
						$app->enqueueMessage(Text::_('PLG_FABRIK_FORM_EMUNDUSCOLLABORATE_ACCOUNT_ALREADY_EXIST'), 'success');
						$app->redirect(Route::_($redirect_url));
					} else {
						$app->enqueueMessage(Text::_('PLG_FABRIK_FORM_EMUNDUSCOLLABORATE_SUCCESS'),'success');
						$app->redirect('index.php');
					}
				} else {
					$app->enqueueMessage(Text::_('PLG_FABRIK_FORM_EMUNDUSCOLLABORATE_ERROR_OCCURED'), 'error');
					$app->redirect('index.php');
				}
			}
		} else {
			$app->enqueueMessage(Text::_('PLG_FABRIK_FORM_EMUNDUSCOLLABORATE_ERROR_ALREADY_UPLOADED'), 'error');
			$app->redirect('index.php');
		}

		return true;
	}

	public function onAfterProcess()
	{
		$app = Factory::getApplication();

		$key = $app->input->getString('key','');
		$datas = $this->getProcessData();

		$query = $this->_db->getQuery(true);

		$query->clear()
			->update($this->_db->quoteName('#__users'))
			->set($this->_db->quoteName('name') . ' = ' . $this->_db->quote($datas['jos_emundus_users___lastname'] . ' ' . $datas['jos_emundus_users___firstname']))
			->where($this->_db->quoteName('id') . ' = ' . $this->_db->quote($datas['jos_emundus_users___user_id']));
		$this->_db->setQuery($query);
		$this->_db->execute();

		$query->clear()
			->update($this->_db->quoteName('#__emundus_users'))
			->set($this->_db->quoteName('password') . ' = ' . $this->_db->quote(''))
			->where($this->_db->quoteName('user_id') . ' = ' . $this->_db->quote($datas['jos_emundus_users___user_id']));
		$this->_db->setQuery($query);
		$this->_db->execute();

		$shared_user_id = $datas['jos_emundus_users___user_id'];

		$accepted = $this->acceptCollaboration($shared_user_id,$key);

		if($accepted) {
			PluginHelper::importPlugin('emundus','custom_event_handler');
			\Joomla\CMS\Factory::getApplication()->triggerEvent('onAfterAcceptCollaboration', [$shared_user_id, $key]);
			\Joomla\CMS\Factory::getApplication()->triggerEvent('callEventHandler', ['onAfterAcceptCollaboration', ['user' => $shared_user_id, 'key' => $key]]);

			$app->enqueueMessage(Text::_('PLG_FABRIK_FORM_EMUNDUSCOLLABORATE_SUCCESS'),'success');
			$app->login(array('username' => $datas['jos_emundus_users___email'], 'password' => $datas['jos_emundus_users___password']));
		} else {
			$app->enqueueMessage(Text::_('PLG_FABRIK_FORM_EMUNDUSCOLLABORATE_ERROR_OCCURED'), 'error');
			$app->redirect('index.php');
		}
	}

	private function acceptCollaboration($user_id, $key)
	{
		$result = false;

		$query = $this->_db->getQuery(true);

		try {
			$query->update($this->_db->quoteName('#__emundus_files_request'))
				->set($this->_db->quoteName('uploaded') . ' = 1')
				->set($this->_db->quoteName('user_id') . ' = ' . $user_id)
				->where($this->_db->quoteName('keyid') . ' = ' . $this->_db->quote($key));
			$this->_db->setQuery($query);
			$result = $this->_db->execute();
		}
		catch (Exception $e) {
			$this->setError($e->getMessage());
		}

		return $result;
	}
}
