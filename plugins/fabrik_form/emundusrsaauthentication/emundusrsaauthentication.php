<?php
/**
 * @version     2: emunduscampaign 2019-04-11 Hugo Moracchini
 * @package     Fabrik
 * @copyright   Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license     GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 * @description Création de dossier de candidature automatique.
 */

// No direct access
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;

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
class PlgFabrik_FormEmundusRsaauthentication extends plgFabrik_Form
{

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

		if ($params->get($pname) == '')
		{
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

		if ($params->get($pname) == '')
		{
			return $default;
		}

		return $params->get($pname);
	}

	public function onBeforeLoad()
	{
		if($this->getParam('emundusrsaauhtentication_event_to_run', 'onBeforeLoad') == 'onBeforeLoad')
		{
			$this->manageRSAAuthentication();
		}
	}

	public function onAfterProcess()
	{
		if($this->getParam('emundusrsaauhtentication_event_to_run', 'onBeforeLoad') == 'onAfterProcess')
		{
			$data = $this->getProcessData();
			$table_name = $this->getModel()->getTableName();
			$new_email = $data[$table_name . '___email'];
			$this->manageRSAAuthentication($new_email);
		}
	}

	private function manageRSAAuthentication($new_email = null)
	{
		$app         = Factory::getApplication();
		$current_url = Uri::getInstance()->toString();

		$datas_key = $this->getParam('datas_key', 'data');

		if (strpos($current_url, $datas_key) !== false)
		{
			$rsa_public_key = $this->getParam('rsa_public_key');
			if (!empty($rsa_public_key) && file_exists(JPATH_SITE . '/plugins/fabrik_form/emundusrsaauthentication/' . $rsa_public_key))
			{
				$rsa_public_key = file_get_contents(JPATH_SITE . '/plugins/fabrik_form/emundusrsaauthentication/' . $rsa_public_key);
			}
			else
			{
				$rsa_public_key = $this->getParam('emundusrsaauhtentication_public_key_content');
			}

			$datas = $app->input->getString($datas_key, '');
			$datas = str_replace(' ', '+', $datas);

			if (!empty($datas) && !empty($rsa_public_key))
			{
				// Decrypt $datas with rsa public key via openssl
				try
				{
					openssl_public_decrypt(base64_decode($datas), $decrypted, $rsa_public_key);

					if (!empty($decrypted))
					{
						$decrypted = json_decode($decrypted, true);
					}
					else
					{
						$decrypted = array();
						throw new Exception('PLG_FABRIK_FORM_EMUNDUSRSAAUTHENTICATION_DECRYPT_DATAS_ERROR', 500);
					}

					$username         = (string) $decrypted[$this->getParam('emundusrsaauhtentication_attributes_id', 'id')];
					$firstname        = $decrypted[$this->getParam('emundusrsaauhtentication_attributes_firstname', 'firstname')];
					$lastname         = $decrypted[$this->getParam('emundusrsaauhtentication_attributes_lastname', 'lastname')];
					$email            = $decrypted[$this->getParam('emundusrsaauhtentication_attributes_email', 'email')];
					$other_attributes = $this->getParam('emundusrsaauhtentication_attributes_other', []);
					if (!empty($other_attributes))
					{
						$other_attributes = json_decode($other_attributes, true);
					}
					else
					{
						$other_attributes = [];
					}
					$attributes = [
						'id'        => $username,
						'firstname' => $firstname,
						'lastname'  => $lastname,
						'email'     => $email,
						'other'     => $other_attributes
					];

					if (empty($username) || empty($email))
					{
						throw new Exception('PLG_FABRIK_FORM_EMUNDUSRSAAUTHENTICATION_GET_DATAS_ERROR', 500);
					}

					$db    = Factory::getDbo();
					$query = $db->getQuery(true);

					require_once JPATH_ROOT . '/components/com_emundus/models/users.php';
					$m_users = new EmundusModelUsers();

					// First we check if username corresponding to existing id else we run an event for custom clients
					if (empty(UserHelper::getUserId($username)))
					{
						PluginHelper::importPlugin('emundus');
						$dispatcher = JEventDispatcher::getInstance();
						$results    = $dispatcher->trigger('callEventHandler', ['onGetUsername', ['datas' => $decrypted, 'attributes' => $attributes]]);

						if (is_array($results) && !empty($results[0]['onGetUsername']))
						{
							$username = $results[0]['onGetUsername'];
						}
					}

					// Then we check if username corresponding to existing email if we have no username
					if (empty(UserHelper::getUserId($username)) && empty($new_email))
					{
						$query->select('username')
							->from('#__users')
							->where('email = ' . $db->quote($email));
						$db->setQuery($query);
						$existing_username = $db->loadResult();

						if($existing_username != $email && $existing_username != $username)
						{
							$app->enqueueMessage(Text::_('PLG_FABRIK_FORM_EMUNDUSRSAAUTHENTICATION_EMAIL_ALREADY_USED'), 'error');
							$app->redirect('modifier-mon-adresse-email?data=' . $app->input->getString($datas_key, ''));
						}

						if (!empty($existing_username))
						{
							$username = $existing_username;
						}
					}

					if (empty(UserHelper::getUserId($username)))
					{
						$user                 = new User();
						$user->name           = $lastname . ' ' . $firstname;
						$user->username       = (string) $username;
						$user->email          = !empty($new_email) ? $new_email : $email;
						$user->password_clear = '';
						$user->password       = '';
						$user->block          = 0;
						$user->sendEmail      = 0;
						$user->registerDate   = date('Y-m-d H:i:s');
						$user->activation     = 1;
						$user->params         = '';
						$user->guest          = 0;
						$user->setParam('skip_activation', true);

						$user->groups = [$this->getParam('emundusrsaauhtentication_jgroup', 2)];
						$e_profiles   = [$this->getParam('emundusrsaauhtentication_eprofile', 1000)];
						$e_groups     = [];

						if ($user->save())
						{
							$other_param['firstname']    = $firstname;
							$other_param['lastname']     = $lastname;
							$other_param['profile']      = !empty($e_profiles) ? $e_profiles[0] : 1000;
							$other_param['em_oprofiles'] = implode(',', $e_profiles);
							$other_param['univ_id']      = 0;
							$other_param['em_groups']    = implode(',', $e_groups);
							$other_param['em_campaigns'] = [];
							$other_param['news']         = '';
							$m_users->addEmundusUser($user->id, $other_param);
						}
						else
						{
							throw new Exception('PLG_FABRIK_FORM_EMUNDUSRSAAUTHENTICATION_CREATE_USER_ERROR', 500);
						}
					}
					else
					{
						$uid  = UserHelper::getUserId($username);
						$user = User::getInstance($uid);

						$user->username      = (string) $decrypted[$this->getParam('emundusrsaauhtentication_attributes_id', 'id')];
						$user->lastvisitDate = date('Y-m-d H:i:s');

						if (!$user->save())
						{
							throw new Exception('PLG_FABRIK_FORM_EMUNDUSRSAAUTHENTICATION_UPDATE_USER_ERROR', 500);
						}
					}

					$query->clear()
						->select('id')
						->from($db->quoteName('#__emundus_users'))
						->where($db->quoteName('user_id') . ' = ' . $db->quote($user->id));
					$db->setQuery($query);
					$emundus_user_id = $db->loadResult();

					if (!empty($other_attributes) && !empty($other_attributes['emundusrsaauhtentication_attributes_other_table']))
					{
						$delete_at_login = $this->getParam('emundusrsaauhtentication_attributes_delete_at_login', 0);

						foreach ($other_attributes['emundusrsaauhtentication_attributes_other_table'] as $key => $table)
						{
							$column        = $other_attributes['emundusrsaauhtentication_attributes_other_column'][$key];
							$attribute     = $other_attributes['emundusrsaauhtentication_attributes_other_attribute'][$key];
							$user_key      = $other_attributes['emundusrsaauhtentication_attributes_other_user_key'][$key];
							$user_key_type = $other_attributes['emundusrsaauhtentication_attributes_other_user_key_type'][$key];

							$values = $decrypted[$attribute];

							$values = is_array($values) ? $values : [$values];

							$uid_attribute = $user_key_type == '1' ? $emundus_user_id : $user->id;

							if($delete_at_login == 1)
							{
								$query->clear()
									->select($column)
									->from($db->quoteName($table))
									->where($db->quoteName($user_key) . ' = ' . $db->quote($uid_attribute));
								$db->setQuery($query);
								$existing_values = $db->loadColumn();

								foreach ($existing_values as $value)
								{
									if (!in_array($value, $values))
									{
										$query->clear()
											->delete($db->quoteName($table))
											->where($db->quoteName($column) . ' = ' . $db->quote($value))
											->where($db->quoteName($user_key) . ' = ' . $db->quote($uid_attribute));
										$db->setQuery($query);
										$db->execute();
									}
								}
							}

							foreach ($values as $value)
							{
								$query->clear()
									->select('id')
									->from($db->quoteName($table))
									->where($db->quoteName($column) . ' = ' . $db->quote($value));
								$db->setQuery($query);
								$existing = $db->loadResult();

								if (empty($existing))
								{
									$insert = [
										$user_key => $uid_attribute,
										$column   => $value
									];
									$insert = (object) $insert;
									$db->insertObject($table, $insert);
								}
							}
						}
					}

					$update = [
						'email' => $email,
						'id' => $emundus_user_id
					];
					$update = (object) $update;
					$db->updateObject('#__emundus_users', $update, 'id');

					$m_users->login($user->id);

					$this->app->redirect('index.php');
				}
				catch (Exception $e)
				{
					$app->enqueueMessage(Text::_($e->getMessage()), 'error');
					$app->redirect('index.php');
				}
			}
		}
	}
}