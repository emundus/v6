<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Controller;

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\AdminTools\Admin\Model\TempSuperUsers as TempSuperUsersModel;
use DateInterval;
use Exception;
use FOF40\Container\Container;
use FOF40\Controller\DataController;
use FOF40\Controller\Mixin\PredefinedTaskList;
use FOF40\Date\Date;
use Joomla\CMS\Language\Text;
use RuntimeException;

class TempSuperUsers extends DataController
{
	use PredefinedTaskList;

	/**
	 * Form fields to save in the session
	 *
	 * @var   array
	 * @since 5.3.0
	 */
	protected $formFieldsToSave = [
		'expiration',
		'username',
		'password',
		'password2',
		'email',
		'name',
		'groups',
	];

	/**
	 * The ID of a just added record
	 *
	 * @var   int
	 * @since 5.3.0
	 */
	protected $newUserId = 0;

	public function __construct(Container $container, array $config = [])
	{
		parent::__construct($container, $config);

		$this->setPredefinedTaskList([
			'browse', 'add', 'edit', 'save', 'apply', 'remove', 'cancel',
		]);

		// Make sure temporary super users cannot access the view
		$this->assertNotTemporary();
	}

	/**
	 * Make sure I am not editing myself.
	 *
	 * @since   5.3.0
	 */
	protected function onBeforeEdit()
	{
		$this->assertNotMyself();
	}

	/**
	 * Make sure I am not trying to delete.
	 *
	 * @since   5.3.0
	 */
	protected function onBeforeRemove()
	{
		$this->assertNotMyself();
	}

	/**
	 * Make sure I am not trying to sneakily apply changes to myself.
	 *
	 * @since   5.3.0
	 */
	protected function onBeforeApplySave(&$data)
	{
		if (!isset($data['user_id']) || empty($data['user_id']))
		{
			// Save the form data to the session
			$session = $this->container->session;

			foreach ($this->formFieldsToSave as $field)
			{
				$type = ($field == 'groups') ? 'array' : 'raw';
				$session->set($field, $this->input->get($field, null, $type), 'admintools_tempsuper_wizard');
			}

			// Create or find a user
			/** @var TempSuperUsersModel $model */
			$model           = $this->getModel()->tmpInstance();
			$user_id         = $model->getUserIdFromInfo();
			$data['user_id'] = $user_id;

			$this->newUserId = $user_id;
		}

		$this->assertNotMyself($data['user_id']);
	}

	protected function onAfterApplySave()
	{
		// Remove the saved form data from the session
		$session = $this->container->session;

		foreach ($this->formFieldsToSave as $field)
		{
			$session->clear($field, 'admintools_tempsuper_wizard');
		}

		/** @var TempSuperUsersModel $model */
		$model  = $this->getModel();
		$userID = !empty($this->newUserId) ? $this->newUserId : $model->user_id;

		// Finally, activate and unblock the Super User (if it was a new record).
		if ($userID)
		{
			$user = $this->container->platform->getUser($userID);

			try
			{
				$lastVisitDate = new Date($user->lastvisitDate);
				$interval      = new DateInterval('P30D');
				$then          = $lastVisitDate->add($interval)->toSql();

				if ($then <= time())
				{
					throw new RuntimeException('Fake exception to force the last visit date to update.');
				}
			}
			catch (Exception $e)
			{
				$lastVisitDate = new Date();
			}

			$updates = [
				'block'         => 0,
				'sendEmail'     => 0,
				'lastvisitDate' => $lastVisitDate->toSql(),
				'activation'    => null,
				'otpKey'        => '',
				'otep'          => '',
				'requireReset'  => 0,
			];
			$user->bind($updates);

			$model->setNoCheckFlags(true);
			$user->save();
			$model->addUserToSafeId($userID);
			$model->setNoCheckFlags(false);
		}
	}

	/**
	 * Asserts that I am not trying to modify my own user.
	 *
	 * If you do not specify the user ID being edited / created we'll figure it out from the request using the model.
	 *
	 * @param   int|null  $editingID  The ID of the user being edited.
	 *
	 * @since   5.3.0
	 */
	protected function assertNotMyself($editingID = null)
	{
		if (is_null($editingID))
		{
			$model = $this->getModel();
			$ids   = $this->getIDsFromRequest($model);
		}
		else
		{
			$ids = [$editingID];
		}

		$myId = $this->container->platform->getUser()->id;

		foreach ($ids as $id)
		{
			if ($id == $myId)
			{
				throw new RuntimeException(Text::sprintf('COM_ADMINTOOLS_ERR_TEMPSUPERUSERS_CANTEDITSELF'), 403);
			}
		}
	}

	/**
	 * Asserts that I am not a temporary Super User myself
	 *
	 * @since   5.3.0
	 */
	protected function assertNotTemporary()
	{
		/** @var TempSuperUsersModel $model */
		$model = $this->getModel()->tmpInstance();
		$myId  = $this->container->platform->getUser()->id;

		try
		{
			// Try to find a temporary super user with my own ID
			$model->findOrFail($myId);
		}
		catch (RuntimeException $e)
		{
			// Could not find a temporary super user that's myself. Good!
			return;
		}

		// Uh oh, I am a temporary Super User.
		throw new RuntimeException(Text::sprintf('COM_ADMINTOOLS_ERR_TEMPSUPERUSERS_UNAVAILABLETOTEMP'), 403);
	}
}
