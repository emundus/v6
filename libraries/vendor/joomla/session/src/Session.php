<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session;

use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\DispatcherInterface;

/**
 * Class for managing HTTP sessions
 *
 * Provides access to session-state values as well as session-level settings and lifetime management methods.
 * Based on the standard PHP session handling mechanism it provides more advanced features such as expire timeouts.
 *
 * @since  1.0
 */
class Session implements SessionInterface, DispatcherAwareInterface
{
	use DispatcherAwareTrait;

	/**
	 * Internal session state.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $state = SessionState::INACTIVE;

	/**
	 * Maximum age of unused session in seconds.
	 *
	 * @var    integer
	 * @since  1.0
	 */
	protected $expire = 900;

	/**
	 * The session store object.
	 *
	 * @var    StorageInterface
	 * @since  1.0
	 */
	protected $store;

	/**
	 * Container holding session validators.
	 *
	 * @var    ValidatorInterface[]
	 * @since  2.0.0
	 */
	protected $sessionValidators = [];

	/**
	 * Constructor
	 *
	 * @param   StorageInterface     $store       A StorageInterface implementation.
	 * @param   DispatcherInterface  $dispatcher  DispatcherInterface for the session to use.
	 * @param   array                $options     Optional parameters. Supported keys include:
	 *                                            - name: The session name
	 *                                            - id: The session ID
	 *                                            - expire: The session lifetime in seconds
	 *
	 * @since   1.0
	 */
	public function __construct(StorageInterface $store = null, DispatcherInterface $dispatcher = null, array $options = [])
	{
		$this->store = $store ?: new Storage\NativeStorage(new Handler\FilesystemHandler);

		if ($dispatcher)
		{
			$this->setDispatcher($dispatcher);
		}

		$this->setOptions($options);

		$this->setState(SessionState::INACTIVE);
	}

	/**
	 * Adds a validator to the session
	 *
	 * @param   ValidatorInterface  $validator  The session validator
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function addValidator(ValidatorInterface $validator): void
	{
		$this->sessionValidators[] = $validator;
	}

	/**
	 * Get expiration time in seconds
	 *
	 * @return  integer  The session expiration time in seconds
	 *
	 * @since   1.0
	 */
	public function getExpire()
	{
		return $this->expire;
	}

	/**
	 * Get current state of session
	 *
	 * @return  string  The session state
	 *
	 * @since   1.0
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * Get a session token.
	 *
	 * Tokens are used to secure forms from spamming attacks. Once a token has been generated the system will check the request to see if
	 * it is present, if not it will invalidate the session.
	 *
	 * @param   boolean  $forceNew  If true, forces a new token to be created
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function getToken($forceNew = false)
	{
		// Ensure the session token exists and create it if necessary
		if (!$this->has('session.token') || $forceNew)
		{
			$this->set('session.token', $this->createToken());
		}

		return $this->get('session.token');
	}

	/**
	 * Check if the session has the given token.
	 *
	 * @param   string   $token        Hashed token to be verified
	 * @param   boolean  $forceExpire  If true, expires the session
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function hasToken($token, $forceExpire = true)
	{
		$result = $this->get('session.token') === $token;

		if (!$result && $forceExpire)
		{
			$this->setState(SessionState::EXPIRED);
		}

		return $result;
	}

	/**
	 * Retrieve an external iterator.
	 *
	 * @return  \ArrayIterator  Return an ArrayIterator of $_SESSION.
	 *
	 * @since   1.0
	 */
	#[\ReturnTypeWillChange]
	public function getIterator()
	{
		return new \ArrayIterator($this->all());
	}

	/**
	 * Get session name
	 *
	 * @return  string  The session name
	 *
	 * @since   1.0
	 */
	public function getName()
	{
		return $this->store->getName();
	}

	/**
	 * Set the session name
	 *
	 * @param   string  $name  The session name
	 *
	 * @return  $this
	 *
	 * @since   2.0.0
	 */
	public function setName(string $name)
	{
		$this->store->setName($name);

		return $this;
	}

	/**
	 * Get session id
	 *
	 * @return  string  The session id
	 *
	 * @since   1.0
	 */
	public function getId()
	{
		return $this->store->getId();
	}

	/**
	 * Set the session ID
	 *
	 * @param   string  $id  The session ID
	 *
	 * @return  $this
	 *
	 * @since   2.0.0
	 */
	public function setId(string $id)
	{
		$this->store->setId($id);

		return $this;
	}

	/**
	 * Check if the session is active
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function isActive()
	{
		if ($this->getState() === SessionState::ACTIVE)
		{
			return $this->store->isActive();
		}

		return false;
	}

	/**
	 * Check whether this session is currently created
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function isNew()
	{
		$counter = $this->get('session.counter');

		return $counter === 1;
	}

	/**
	 * Check if the session is started
	 *
	 * @return  boolean
	 *
	 * @since   2.0.0
	 */
	public function isStarted(): bool
	{
		return $this->store->isStarted();
	}

	/**
	 * Get data from the session store
	 *
	 * @param   string  $name     Name of a variable
	 * @param   mixed   $default  Default value of a variable if not set
	 *
	 * @return  mixed  Value of a variable
	 *
	 * @since   1.0
	 */
	public function get($name, $default = null)
	{
		if (!$this->isActive())
		{
			$this->start();
		}

		return $this->store->get($name, $default);
	}

	/**
	 * Set data into the session store.
	 *
	 * @param   string  $name   Name of a variable.
	 * @param   mixed   $value  Value of a variable.
	 *
	 * @return  mixed  Old value of a variable.
	 *
	 * @since   1.0
	 */
	public function set($name, $value = null)
	{
		if (!$this->isActive())
		{
			$this->start();
		}

		return $this->store->set($name, $value);
	}

	/**
	 * Check whether data exists in the session store
	 *
	 * @param   string  $name  Name of variable
	 *
	 * @return  boolean  True if the variable exists
	 *
	 * @since   1.0
	 */
	public function has($name)
	{
		if (!$this->isActive())
		{
			$this->start();
		}

		return $this->store->has($name);
	}

	/**
	 * Unset a variable from the session store
	 *
	 * @param   string  $name  Name of variable
	 *
	 * @return  mixed   The value from session or NULL if not set
	 *
	 * @since   2.0.0
	 */
	public function remove(string $name)
	{
		if (!$this->isActive())
		{
			$this->start();
		}

		return $this->store->remove($name);
	}

	/**
	 * Clears all variables from the session store
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function clear()
	{
		if (!$this->isActive())
		{
			$this->start();
		}

		$this->store->clear();
	}

	/**
	 * Retrieves all variables from the session store
	 *
	 * @return  array
	 *
	 * @since   2.0.0
	 */
	public function all(): array
	{
		if (!$this->isActive())
		{
			$this->start();
		}

		return $this->store->all();
	}

	/**
	 * Start a session.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function start()
	{
		if ($this->isStarted())
		{
			return;
		}

		$this->store->start();

		$this->setState(SessionState::ACTIVE);

		// Initialise the session
		$this->setCounter();
		$this->setTimers();

		// Perform security checks
		if (!$this->validate())
		{
			// If the session isn't valid because it expired try to restart it or destroy it.
			if ($this->getState() === SessionState::EXPIRED)
			{
				$this->restart();
			}
			else
			{
				$this->destroy();
			}
		}

		if ($this->dispatcher)
		{
			if (!empty($this->dispatcher->getListeners('onAfterSessionStart')))
			{
				trigger_deprecation(
					'joomla/session',
					'2.0.0',
					'The `onAfterSessionStart` event is deprecated and will be removed in 3.0, use the %s::START event instead.',
					SessionEvents::class
				);

				// Dispatch deprecated event
				$this->dispatcher->dispatch('onAfterSessionStart', new SessionEvent('onAfterSessionStart', $this));
			}

			// Dispatch new event
			$this->dispatcher->dispatch(SessionEvents::START, new SessionEvent(SessionEvents::START, $this));
		}
	}

	/**
	 * Frees all session variables and destroys all data registered to a session
	 *
	 * This method resets the $_SESSION variable and destroys all of the data associated
	 * with the current session in its storage (file or DB). It forces new session to be
	 * started after this method is called.
	 *
	 * @return  boolean
	 *
	 * @see     session_destroy()
	 * @see     session_unset()
	 * @since   1.0
	 */
	public function destroy()
	{
		// Session was already destroyed
		if ($this->getState() === SessionState::DESTROYED)
		{
			return true;
		}

		$this->clear();
		$this->fork(true);

		$this->setState(SessionState::DESTROYED);

		return true;
	}

	/**
	 * Restart an expired or locked session.
	 *
	 * @return  boolean  True on success
	 *
	 * @see     destroy
	 * @since   1.0
	 */
	public function restart()
	{
		// Backup existing session data
		$data = $this->all();

		$this->destroy();

		if ($this->getState() !== SessionState::DESTROYED)
		{
			// @TODO :: generated error here
			return false;
		}

		// Restart the session
		$this->store->start();

		$this->setState(SessionState::ACTIVE);

		// Initialise the session
		$this->setCounter();
		$this->setTimers();

		// Restore the data
		foreach ($data as $key => $value)
		{
			$this->set($key, $value);
		}

		// If the restarted session cannot be validated then it will be destroyed
		if (!$this->validate(true))
		{
			$this->destroy();
		}

		if ($this->dispatcher)
		{
			if (!empty($this->dispatcher->getListeners('onAfterSessionRestart')))
			{
				trigger_deprecation(
					'joomla/session',
					'2.0.0',
					'The `onAfterSessionRestart` event is deprecated and will be removed in 3.0, use the %s::RESTART event instead.',
					SessionEvents::class
				);

				// Dispatch deprecated event
				$this->dispatcher->dispatch('onAfterSessionRestart', new SessionEvent('onAfterSessionRestart', $this));
			}

			// Dispatch new event
			$this->dispatcher->dispatch(SessionEvents::RESTART, new SessionEvent(SessionEvents::RESTART, $this));
		}

		return true;
	}

	/**
	 * Create a new session and copy variables from the old one
	 *
	 * @param   boolean  $destroy  Whether to delete the old session or leave it to garbage collection.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.0
	 */
	public function fork($destroy = false)
	{
		$result = $this->store->regenerate($destroy);

		if ($result)
		{
			$this->setTimers();
		}

		return $result;
	}

	/**
	 * Writes session data and ends session
	 *
	 * Session data is usually stored after your script terminated without the need
	 * to call {@link Session::close()}, but as session data is locked to prevent concurrent
	 * writes only one script may operate on a session at any time. When using
	 * framesets together with sessions you will experience the frames loading one
	 * by one due to this locking. You can reduce the time needed to load all the
	 * frames by ending the session as soon as all changes to session variables are
	 * done.
	 *
	 * @return  void
	 *
	 * @see     session_write_close()
	 * @since   1.0
	 */
	public function close()
	{
		$this->store->close();
		$this->setState(SessionState::CLOSED);
	}

	/**
	 * Perform session data garbage collection
	 *
	 * @return  integer|boolean  Number of deleted sessions on success or boolean false on failure or if the function is unsupported
	 *
	 * @see     session_gc()
	 * @since   2.0.0
	 */
	public function gc()
	{
		if (!$this->isActive())
		{
			$this->start();
		}

		return $this->store->gc();
	}

	/**
	 * Aborts the current session
	 *
	 * @return  boolean
	 *
	 * @see     session_abort()
	 * @since   2.0.0
	 */
	public function abort(): bool
	{
		if (!$this->isActive())
		{
			return true;
		}

		return $this->store->abort();
	}

	/**
	 * Create a token string
	 *
	 * @return  string
	 *
	 * @since   1.3.1
	 */
	protected function createToken(): string
	{
		/*
		 * We are returning a 32 character string.
		 * The bin2hex() function will double the length of the hexadecimal value returned by random_bytes(),
		 * so generate the token from a 16 byte random value
		 */
		return bin2hex(random_bytes(16));
	}

	/**
	 * Set counter of session usage
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.0
	 */
	protected function setCounter()
	{
		$counter = $this->get('session.counter', 0);
		$counter++;

		$this->set('session.counter', $counter);

		return true;
	}

	/**
	 * Set the session expiration
	 *
	 * @param   integer  $expire  Maximum age of unused session in seconds
	 *
	 * @return  $this
	 *
	 * @since   1.3.0
	 */
	protected function setExpire($expire)
	{
		$this->expire = $expire;

		return $this;
	}

	/**
	 * Set the session state
	 *
	 * @param   string  $state  Internal state
	 *
	 * @return  $this
	 *
	 * @since   1.3.0
	 */
	protected function setState($state)
	{
		$this->state = $state;

		return $this;
	}

	/**
	 * Set the session timers
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.0
	 */
	protected function setTimers()
	{
		if (!$this->has('session.timer.start'))
		{
			$start = time();

			$this->set('session.timer.start', $start);
			$this->set('session.timer.last', $start);
			$this->set('session.timer.now', $start);
		}

		$this->set('session.timer.last', $this->get('session.timer.now'));
		$this->set('session.timer.now', time());

		return true;
	}

	/**
	 * Set additional session options
	 *
	 * @param   array  $options  List of parameter
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.0
	 */
	protected function setOptions(array $options)
	{
		// Set name
		if (isset($options['name']))
		{
			$this->setName($options['name']);
		}

		// Set id
		if (isset($options['id']))
		{
			$this->setId($options['id']);
		}

		// Set expire time
		if (isset($options['expire']))
		{
			$this->setExpire($options['expire']);
		}

		// Sync the session maxlifetime
		if (!headers_sent())
		{
			ini_set('session.gc_maxlifetime', $this->getExpire());
		}

		return true;
	}

	/**
	 * Do some checks for security reasons
	 *
	 * If one check fails, session data has to be cleaned.
	 *
	 * @param   boolean  $restart  Reactivate session
	 *
	 * @return  boolean  True on success
	 *
	 * @see     http://shiflett.org/articles/the-truth-about-sessions
	 * @since   1.0
	 */
	protected function validate($restart = false)
	{
		// Allow to restart a session
		if ($restart)
		{
			$this->setState(SessionState::ACTIVE);
		}

		// Check if session has expired
		if ($this->expire)
		{
			$curTime = $this->get('session.timer.now', 0);
			$maxTime = $this->get('session.timer.last', 0) + $this->expire;

			// Empty session variables
			if ($maxTime < $curTime)
			{
				$this->setState(SessionState::EXPIRED);

				return false;
			}
		}

		try
		{
			foreach ($this->sessionValidators as $validator)
			{
				$validator->validate($restart);
			}
		}
		catch (Exception\InvalidSessionException $e)
		{
			$this->setState(SessionState::ERROR);

			return false;
		}

		return true;
	}
}
