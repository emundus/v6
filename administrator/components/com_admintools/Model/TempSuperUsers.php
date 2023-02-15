<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Model;

// Protect from unauthorized access
defined('_JEXEC') || die();

use AtsystemFeatureNonewadmins;
use DateInterval;
use DateTimeZone;
use Exception;
use FOF40\Container\Container;
use FOF40\Date\Date;
use FOF40\Encrypt\Randval;
use FOF40\Model\DataModel;
use JDatabaseQuery;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\UserHelper;
use RuntimeException;

/**
 * A model for temporary super user management
 *
 * @property  int              $user_id
 * @property  string           $expiration
 *
 * @method    $this  user_id()     user_id(int $user_id)
 * @method    $this  expiration()  expiration(string $user_id)
 *
 * @property-read  JoomlaUsers $user
 *
 * @package     Akeeba\AdminTools\Admin\Model
 *
 * @since       5.3.0
 */
class TempSuperUsers extends DataModel
{
	/**
	 * Cache of user group IDs with Super User privileges
	 *
	 * @var   array
	 * @since 5.3.0
	 */
	protected $superUserGroups = [];

	public function __construct(Container $container, array $config = [])
	{
		$config['tableName']   = '#__admintools_tempsupers';
		$config['idFieldName'] = 'user_id';

		parent::__construct($container, $config);

		$this->addBehaviour('Filters');
		$this->addBehaviour('RelationFilters');

		$this->hasOne('user', 'JoomlaUsers', 'user_id', 'id');
		$this->with(['user']);
	}

	/**
	 * Build the SELECT query for returning records. Overridden to apply custom filters.
	 *
	 * @param   JDatabaseQuery  $query           The query being built
	 * @param   bool             $overrideLimits  Should I be overriding the limit state (limitstart & limit)?
	 *
	 * @return  void
	 */
	public function onAfterBuildQuery(JDatabaseQuery $query, $overrideLimits = false)
	{
		$db = $this->getDbo();

		$username = $this->getState('username', null, 'string');

		if ($username)
		{
			$this->whereHas('user', function (JDatabaseQuery $subQuery) use ($username, $db) {
				$subQuery->where($db->qn('username') . ' LIKE ' . $db->q('%' . $username . '%'));
			});
		}
	}

	public function onBeforeCheck()
	{
		// Make sure I am not editing myself
		if ($this->user_id == $this->container->platform->getUser()->id)
		{
			throw new RuntimeException(Text::_('COM_ADMINTOOLS_ERR_TEMPSUPERUSERS_CANTEDITSELF'), 403);
		}

		// Make sure I am not setting an expiration time in the past
		$timezone = $this->container->platform->getUser()->getParam('timezone', $this->container->platform->getConfig()->get('offset', 'GMT'));
		try
		{
			$tz = new DateTimeZone($timezone);
		}
		catch (Exception $e)
		{
			$tz = new DateTimeZone('GMT');
		}

		$jNow  = new Date();
		$jThen = new Date($this->expiration, $tz);

		if ($jThen->toUnix() < $jNow->toUnix())
		{
			throw new RuntimeException(Text::_('COM_ADMINTOOLS_ERR_TEMPSUPERUSERS_EXPIRATIONINPAST'), 500);
		}

		$this->expiration = $jThen->toSql();
	}

	public function onAfterDelete()
	{
		$userId = $this->user_id;

		if (empty($userId))
		{
			return;
		}

		/** @var JoomlaUsers $user */
		$user = $this->container->factory->model('JoomlaUsers');

		try
		{
			$user->findOrFail($userId);
		}
		catch (Exception $e)
		{
			return;
		}

		$this->setNoCheckFlags(true);
		$user->delete();
		$this->setNoCheckFlags(false);
	}

	/**
	 * Returns the new Super User data, either what was saved in the session or random values if the information was not
	 * present in the session
	 *
	 * @since   5.3.0
	 */
	public function getNewUserData()
	{
		$ret     = [
			'expiration' => null,
			'username'   => null,
			'password'   => null,
			'password2'  => null,
			'email'      => null,
			'name'       => null,
			'groups'     => [],
		];
		$session = $this->container->session;
		$rand    = new Randval();

		foreach ($ret as $field => $v)
		{
			$ret[$field] = $session->get($field, null, 'admintools_tempsuper_wizard');
		}

		if (empty($ret['expiration']) || ($ret['expiration'] == $this->getDbo()->getNullDate()))
		{
			$jDate             = new Date();
			$interval          = new DateInterval('P15D');
			$ret['expiration'] = $jDate->add($interval)->toRFC822();
		}

		if (empty($ret['username']))
		{
			$ret['username'] = 'temp' . $rand->getRandomPassword(12);
		}

		if (empty($ret['password']) && empty($ret['password2']))
		{
			$ret['password']  = $rand->getRandomPassword(32);
			$ret['password2'] = $ret['password'];
		}

		if (empty($ret['email']))
		{
			$ret['email'] = $rand->getRandomPassword(12) . '@example.com';
		}

		if (empty($ret['name']))
		{
			$ret['name'] = Text::_('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_DEFAULTNAME');
		}

		if (empty($ret['groups']))
		{
			$superUserGroups = $this->getSuperUserGroups();
			$ret['groups']   = [
				array_shift($superUserGroups),
			];
		}

		return $ret;
	}

	/**
	 * Find an eligible super user or create a new one, then return the user ID. This is used by the Controller to
	 * create a new record.
	 *
	 * @return  int
	 *
	 * @since   5.3.0
	 */
	public function getUserIdFromInfo()
	{
		$this->setNoCheckFlags(true);

		$info = $this->getNewUserData();

		$info['block']         = 0;
		$info['sendEmail']     = 0;
		$info['lastvisitDate'] = (new Date())->toSql();
		$info['activation']    = '';
		$info['otpKey']        = '';
		$info['otep']          = '';
		$info['requireReset']  = 0;

		// Do I have an eligible existing user?
		$userId = $this->findExistingUser($info['username']);

		if (empty($userId))
		{
			// Make sure $info['groups'] is defined and defines at least one Super User group
			$superUserGroups = $this->getSuperUserGroups();
			$usedSUGroups    = array_intersect($info['groups'], $superUserGroups);

			if (empty($usedSUGroups))
			{
				$this->setNoCheckFlags(false);

				throw new RuntimeException(Text::_('COM_ADMINTOOLS_ERR_TEMPSUPERUSERS_NOTASUPERUSER'), 500);
			}

			// Create a new user
			$user = $this->container->platform->getUser(0);

			// Set the user's default language to whatever the site's current language is
			$info['params'] = [
				'language' => self::getContainer()->platform->getConfig()->get('language'),
			];

			$user->bind($info);

			$saved = $user->save();

			if (!$saved)
			{
				$this->setNoCheckFlags(false);

				throw new RuntimeException($user->getError());
			}

			return $user->id;
		}

		// Make sure I am not trying to edit myself
		if ($userId == $this->container->platform->getUser()->id)
		{
			$this->setNoCheckFlags(false);

			throw new RuntimeException(Text::_('COM_ADMINTOOLS_ERR_TEMPSUPERUSERS_CANTEDITSELF'), 403);
		}

		// Apply changes to the existing user
		$user = $this->container->platform->getUser($userId);

		unset($info['username']);

		$user->bind($info);

		$saved = $user->save();

		$this->setNoCheckFlags(false);

		if (!$saved)
		{
			throw new RuntimeException($user->getError());
		}

		return $userId;
	}

	/**
	 * Temporarily disable Admin Tools security checks for creating / editing Super Users.
	 *
	 * * The "Backend Edit Admin User" feature will prevent creating / editing temporary Super Users. Before doing
	 *   anything with the temporary user records we set a special flag to disable it for the next request.
	 * * The "Monitor Super User accounts" will automatically disable our newly created temporary Super User. We work
	 *   around it by setting the special session flag which tells this feature to replace the Super Users list.
	 *
	 * @param   bool  $noChecks
	 */
	public function setNoCheckFlags($noChecks = true)
	{
		// Workaround for "Backend Edit Admin User"
		if (class_exists('AtsystemFeatureNonewadmins') && method_exists('AtsystemFeatureNonewadmins', 'setTempDisableFlag'))
		{
			AtsystemFeatureNonewadmins::setTempDisableFlag($noChecks);
		}

		// Workaround for "Monitor Super User accounts"
		$this->container->platform->unsetSessionVar('superuserslist.createnew', 'com_admintools');

		if ($noChecks)
		{
			$this->container->platform->setSessionVar('superuserslist.createnew', true, 'com_admintools');
		}
	}

	/**
	 * Adds a new user into the list of "safe ids", otherwise at the next session load it will be disabled by the feature
	 * "Monitor Super User accounts"
	 *
	 * @param int $userid ID of the new user that should be injected into the list
	 *
	 */
	public function addUserToSafeId($userid)
	{
		$db    = $this->container->db;
		$query = $db->getQuery(true)
					->select($db->quoteName('value'))
					->from($db->quoteName('#__admintools_storage'))
					->where($db->quoteName('key') . ' = ' . $db->quote('superuserslist'));
		$db->setQuery($query);

		try
		{
			$jsonData = $db->loadResult();
		}
		catch (\Exception $e)
		{
			return;
		}

		$userList = [];

		if (!empty($jsonData))
		{
			$userList = json_decode($jsonData, true);
		}

		$userList[] = $userid;

		$db   = $this->container->db;
		$data = json_encode($userList);

		$query = $db->getQuery(true)
			->delete($db->quoteName('#__admintools_storage'))
			->where($db->quoteName('key') . ' = ' . $db->quote('superuserslist'));
		$db->setQuery($query);
		$db->execute();

		$object = (object) [
			'key'   => 'superuserslist',
			'value' => $data,
		];

		$db->insertObject('#__admintools_storage', $object);
	}

	/**
	 * Returns all Joomla! user groups
	 *
	 * @return  array
	 *
	 * @since   5.3.0
	 */
	protected function getSuperUserGroups()
	{
		if (empty($this->superUserGroups))
		{
			// Get all groups
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->select([$db->qn('id')])
				->from($db->qn('#__usergroups'));

			$this->superUserGroups = $db->setQuery($query)->loadColumn(0);

			// This should never happen (unless your site is very dead, in which case I feel terribly sorry for you...)
			if (empty($this->superUserGroups))
			{
				$this->superUserGroups = [];
			}

			$this->superUserGroups = array_filter($this->superUserGroups, function ($group) {
				return Access::checkGroup($group, 'core.admin');
			});
		}

		return $this->superUserGroups;
	}

	protected function findExistingUser($username)
	{
		/** @var JoomlaUsers $model */
		$model = $this->container->factory->model('JoomlaUsers')->tmpInstance();

		// Make sure the user exists. Return 0 otherwise.
		$id = UserHelper::getUserId($username);

		if (empty($id))
		{
			return 0;
		}

		$user = $this->container->platform->getUser($id);

		// Make sure the user is a Super User
		if (!$user->authorise('core.admin'))
		{
			throw new RuntimeException(Text::_('COM_ADMINTOOLS_ERR_TEMPSUPERUSERS_NOTSUPER'), 500);
		}

		// Make sure the user was already blocked
		if (!$user->block)
		{
			throw new RuntimeException(Text::_('COM_ADMINTOOLS_ERR_TEMPSUPERUSERS_NOTBLOCKED'), 500);
		}

		return $id;
	}
}
