<?php
/**
 * Add a user to a mailchimp mailing list
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.mailchimp
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use DrewM\MailChimp\MailChimp;

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';
require_once JPATH_ROOT . '/plugins/fabrik_form/mailchimp/vendor/autoload.php';

/**
 * Add a user to a mailchimp mailing list
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.mailchimp
 * @since       3.0
 */

class PlgFabrik_FormMailchimp extends PlgFabrik_Form
{
	protected $html = null;
	private $api = null;
	private $groups = null;
	private $mergeFields = null;
	private $interests = null;

	private function getApi()
	{
		if ($this->api === null)
		{
			$params = $this->getParams();
			$apiKey = $params->get('mailchimp_apikey');

			if ($apiKey == '')
			{
				throw new RuntimeException('Mailchimp: no api key specified');
			}

			$this->api = new MailChimp($apiKey);
		}

		return $this->api;
	}

	private function getMailchimpListId()
	{
		$params = $this->getParams();
		$listId = $params->get('mailchimp_listid');

		if ($listId == '')
		{
			throw new RuntimeException('Mailchimp: no list id specified');
		}

		return $listId;
	}

	/**
	 * Set up the html to be injected into the bottom of the form
	 *
	 * @return  void
	 */

	public function getBottomContent()
	{
		$params = $this->getParams();

		$confirmElement = $params->get('mailchimp_confirm', '');

		if ($params->get('mailchimp_userconfirm', true))
		{
			if (empty($confirmElement))
			{
				$listId = $this->getMailchimpListId();
				$api    = $this->getApi();

				//get the list of subscribers
				//$subList = $api->listMembers($listId,'subscribed', NULL, 0, 100);
				$formModel = $this->getModel();
				$emailKey  = $formModel->getElement($params->get('mailchimp_email'), true)->getFullName();
				$email     = $formModel->getElementData($emailKey);
				$hash      = $api->subscriberHash($email);
				$sub       = $api->get("lists/$listId/members/$hash");

				if ($sub !== false)
				{
					switch ($sub['status'])
					{
						case 404:
						case 'unsubscribed':
						case 'cleaned':
							$emailPresent = false;
							break;
						case 'subscribed':
						case 'pending':
						default:
							$emailPresent = true;
					}

					$checked    = $emailPresent ? ' checked="checked"' : '';
					$this->html = '
					<label class="mailchimpsignup">
						<input type="checkbox" name="fabrik_mailchimp_signup" class="fabrik_mailchimp_signup" value="1" ' . $checked . '/>' .
						FText::_($params->get('mailchimp_signuplabel')) .
						'</label>';
				}
				else
				{
					// API failed, so don't show a checkbox
					$this->html = JText::_('PLG_FORM_MAILCHIMP_API_FAIL');

					// if in debug, give some feedback
					if (FabrikHelperHTML::isDebug(true))
					{
						$this->app->enqueueMessage('Mailchimp: ' . $api->getLastError(), 'notice');
					}
				}
			}
		}
		else
		{
			$this->html = '';
		}

		$groups = $this->getGroups($params);
	}

	/**
	 * Get Mailchimp email groups
	 *
	 * @throws RuntimeException
	 *
	 * @return  array groups
	 */

	protected function getGroups()
	{
		if ($this->groups === null)
		{
			$api       = $this->getApi();
			$listId    = $this->getMailchimpListId();
			$this->groups    = array();

			$categories = $api->get("lists/$listId/interest-categories");

			if ($api->success())
			{
				foreach ($categories['categories'] as $category)
				{
					$interests = $api->get("lists/$listId/interest-categories/{$category['id']}/interests");

					if ($api->success())
					{
						$this->groups[] = array(
							'id'        => $category['id'],
							'title'     => $category['title'],
							'type'      => $category['type'],
							'interests' => $interests['interests']
						);
					}
				}
			}
		}

		return $this->groups;
	}

	private function getInterests()
	{
		if ($this->interests === null)
		{
			$groups    = $this->getGroups();
			$this->interests = array();

			foreach ($groups as $group)
			{
				foreach ($group['interests'] as $interest)
				{
					$this->interests[$interest['id']] = $interest['name'];
				}
			}
		}

		return $this->interests;
	}

	/**
	 * Get Mailchimp merge fields
	 *
	 * @throws RuntimeException
	 *
	 * @return  array groups
	 */

	protected function getMergeFields()
	{
		if ($this->mergeFields === null)
		{
			$api       = $this->getApi();
			$listId    = $this->getMailchimpListId();
			$this->mergeFields    = array();

			$mergeFields = $api->get("lists/$listId/merge-fields");

			if ($api->success())
			{
				foreach ($mergeFields['merge_fields'] as $mergeField)
				{
					$this->mergeFields[$mergeField['tag']] = $mergeField;
				}
			}
		}

		return $this->mergeFields;
	}

	/**
	 * Inject custom html into the bottom of the form
	 *
	 * @param   int  $c  Plugin counter
	 *
	 * @return  string  html
	 */

	public function getBottomContent_result($c)
	{
		return $this->html;
	}

	private function getSubscribe()
	{
		$params = $this->getParams();
		$confirmElement = $params->get('mailchimp_confirm', '');
		$subscribe = false;

		if (!empty($confirmElement))
		{
			$formModel = $this->getModel();
			$confirmKey = $formModel->getElement($confirmElement, true)->getFullName();
			$subscribe    = $formModel->getElementData($confirmKey, true);
			$subscribe    = is_array($subscribe) ? $subscribe[0] : $subscribe;
			$subscribe    = !empty($subscribe);
		}
		else
		{
			$filter = JFilterInput::getInstance();
			$post = $filter->clean($_POST, 'array');
			$subscribe = array_key_exists('fabrik_mailchimp_signup', $post);
		}

		return $subscribe;
	}

	/**
	 * Run right at the end of the form processing
	 * form needs to be set to record in database for this to hook to be called
	 *
	 * @return	bool
	 */

	public function onAfterProcess()
	{
		$params = $this->getParams();
		$formModel = $this->getModel();
		$emailData = $this->getProcessData();
		$subscribe = $this->getSubscribe();
		$confirm = $params->get('mailchimp_userconfirm', '0') === '1';

		if ($formModel->isNewRecord() && $confirm && !$subscribe)
		{
			return;
		}

		$listId = $params->get('mailchimp_listid');
		$apiKey = $params->get('mailchimp_apikey');

		if ($apiKey == '')
		{
			throw new RuntimeException('Mailchimp: no api key specified');
		}

		if ($listId == '')
		{
			throw new RuntimeException('Mailchimp: no list id specified');
		}

		$emailKey = $formModel->getElement($params->get('mailchimp_email'), true)->getFullName();
		$email    = $formModel->formDataWithTableName[$emailKey];
		$api = new MailChimp($params->get('mailchimp_apikey'));
		$hash = $api->subscriberHash($email);

		if (!$formModel->isNewRecord() && $confirm && !$subscribe)
		{
			$method = $params->get('mailchimp_unsub_method', 'unsub');

			switch ($method)
			{
				case 'delete':
					$result = $api->delete("lists/$listId/members/$hash");
					break;
				case 'unsubscribed':
				case 'cleaned':
					$result = $api->patch(
				"lists/$listId/members/$hash",
						array(
							'status' => $method
						)
					);
					break;
			}
		}
		else
		{
			$sub = $api->get("lists/$listId/members/$hash");

			if ($sub !== false)
			{
				switch ($sub['status'])
				{
					case 404:
						$emailPresent = false;
						break;
					case 'subscribed':
					case 'pending':
					case 'unsubscribed':
					case 'cleaned':
					default:
						$emailPresent = true;
				}

				$opts          = array();
				$firstNameKey  = $formModel->getElement($params->get('mailchimp_firstname'), true)->getFullName();
				$fname         = $formModel->formDataWithTableName[$firstNameKey];
				$opts['FNAME'] = $fname;
				$opts['NAME']  = $fname;

				if ($params->get('mailchimp_lastname', '') !== '')
				{
					$lastNameKey   = $formModel->getElement($params->get('mailchimp_lastname'), true)->getFullName();
					$lname         = $formModel->formDataWithTableName[$lastNameKey];
					$opts['LNAME'] = $lname;
					$opts['NAME']  .= ' ' . $lname;
				}

				$ignoreTags = array(
					'LNAME',
					'FNAME',
					'NAME'
				);

				$mergeFields = json_decode($params->get('mailchimp_mergefields', "[]"));
				$allMergeFields = $this->getMergeFields();

				$w = new FabrikWorker();

				if (!empty($mergeFields))
				{
					foreach ($mergeFields as $tagName => $elementName)
					{
						$opts[$tagName] = $w->parseMessageForPlaceHolder($elementName, $formModel->formData);
					}

					foreach ($mergeFields as $mergeTag => $value)
					{
						if (!array_key_exists($mergeTag, $allMergeFields))
						{
							if (FabrikHelperHTML::isDebug(true))
							{
								$this->app->enqueueMessage('Mailchimp: no such merge tag: ' . $mergeTag, 'notice');
							}

							unset($opts[$mergeTag]);
						}
					}

					foreach ($allMergeFields as $mergeTag => $mergeField)
					{
						if (!array_key_exists($mergeTag, $mergeFields) && !in_array($mergeTag, $ignoreTags))
						{
							$opts[$mergeTag] = $mergeField['default_value'];
						}
					}
				}

				$groupOpts = json_decode($params->get('mailchimp_groupopts', "[]"));
				$interests = array();
				$w         = new FabrikWorker;

				if (!empty($groupOpts))
				{
					foreach ($groupOpts as $interestId => $elementName)
					{
						$value = false;
						list($elementName, $elementValue) = $this->getNameValue($elementName);

						if (array_key_exists($elementName, $formModel->formDataWithTableName))
						{
							$values = (array) $formModel->formDataWithTableName[$elementName];

							foreach ($values as $v)
							{
								if ($v === $elementValue)
								{
									$value = true;
									break;
								}
							}
						}
						else
						{
							$value = $w->parseMessageForPlaceHolder($elementName, $formModel->formData);
						}

						$interests[$interestId] = !empty($value);
					}

					$allInterests = $this->getInterests();

					foreach ($interests as $interestId => $value)
					{
						if (!array_key_exists($interestId, $allInterests))
						{
							if (FabrikHelperHTML::isDebug(true))
							{
								$this->app->enqueueMessage('Mailchimp: no such interest ID: ' . $interestId, 'notice');
							}

							unset($interests[$interestId]);
						}
					}

					foreach ($allInterests as $interestId => $name)
					{
						if (!array_key_exists($interestId, $interests))
						{
							$interests[$interestId] = false;
						}
					}
				}

						// By default this sends a confirmation email - you will not see new members until the link contained in it is clicked!
				$emailType      = $params->get('mailchimp_email_type', 'html');
				$doubleOptin    = (bool) $params->get('mailchimp_double_optin', true);
				$updateExisting = (bool) $params->get('mailchimp_update_existing', true);

				if ($emailPresent)
				{
					if ($updateExisting)
					{
						$payload = 	array (
							'status' => 'subscribed',
							'merge_fields' => $opts,
							'email_type' => $emailType
						);

						if (count($interests) > 0)
						{
							$payload['interests'] = $interests;
						}

						$result = $api->patch(
							"lists/$listId/members/$hash",
							$payload
						);
					}
				}
				else
				{
					$status = $doubleOptin ? 'pending' : 'subscribed';

					$payload = 	array (
						'status' => $status,
						'email_address' => $email,
						'merge_fields' => $opts,
						'email_type' => $emailType
					);

					if (count($interests) > 0)
					{
						$payload['interests'] = $interests;
					}

					$result = $api->post(
						"lists/$listId/members",
						$payload
					);
				}
			}
		}

		if (!$api->success())
		{
			// if in debug, give some feedback
			if (FabrikHelperHTML::isDebug(true))
			{
				$this->app->enqueueMessage('Mailchimp: ' . $api->getLastError(), 'notice');
			}
			else
			{
				$this->app->enqueueMessage(FText::_('PLG_FORM_MAILCHIMP_API_FAIL'));
			}

			if ((bool) $params->get('mailchimp_fail_on_error', true) === true)
			{
				$formModel->errors['mailchimp_error'] = true;

				return false;
			}
			else
			{
				return true;
			}
		}
		else
		{
			if ($params->get('mailchimp_success', '0') === '1')
			{
				$this->app->enqueueMessage(FText::_($params->get('mailchimp_success_msg', 'PLG_FORM_MAILCHIMP_API_SUCCESS')));
			}

			return true;
		}
	}

	private function getNameValue($elementName)
	{
		$name = $elementName;
		$value = '';
		$matches = array();

		if (preg_match('/\{(.*)\}/', $elementName, $matches))
		{
			if (strstr($matches[1], '|'))
			{
				list($name,$value) = explode('|', $matches[1]);
			}
			else
			{
				$name = $matches[1];
			}
		}

		return array($name, $value);
	}
}
