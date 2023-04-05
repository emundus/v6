<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Validator;

use Joomla\Input\Input;
use Joomla\Session\SessionInterface;
use Joomla\Session\ValidatorInterface;

/**
 * Interface for validating a part of the session
 *
 * @since  2.0.0
 */
class ForwardedValidator implements ValidatorInterface
{
	/**
	 * The Input object.
	 *
	 * @var    Input
	 * @since  2.0.0
	 */
	private $input;

	/**
	 * The session object.
	 *
	 * @var    SessionInterface
	 * @since  2.0.0
	 */
	private $session;

	/**
	 * Constructor
	 *
	 * @param   Input             $input    The input object
	 * @param   SessionInterface  $session  DispatcherInterface for the session to use.
	 *
	 * @since   2.0.0
	 */
	public function __construct(Input $input, SessionInterface $session)
	{
		$this->input   = $input;
		$this->session = $session;
	}

	/**
	 * Validates the session
	 *
	 * @param   boolean  $restart  Flag if the session should be restarted
	 *
	 * @return  void
	 *
	 * @since   2.0.0
	 */
	public function validate(bool $restart = false): void
	{
		if ($restart)
		{
			$this->session->set('session.client.forwarded', null);
		}

		$xForwardedFor = $this->input->server->getString('HTTP_X_FORWARDED_FOR', '');

		// Record proxy forwarded for in the session in case we need it later
		if (!empty($xForwardedFor) && filter_var($xForwardedFor, FILTER_VALIDATE_IP) !== false)
		{
			$this->session->set('session.client.forwarded', $xForwardedFor);
		}
	}
}
