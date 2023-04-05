<?php
/**
 * Part of the Joomla Framework Session Package
 *
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Session\Handler;

use Joomla\Session\HandlerInterface;

/**
 * Filesystem session storage handler
 *
 * @since  2.0.0
 */
class FilesystemHandler extends \SessionHandler implements HandlerInterface
{
	/**
	 * Constructor
	 *
	 * @param   string  $path  Path of directory to save session files.  Leave null to use the PHP configured path.
	 *
	 * @since   2.0.0
	 * @throws  \InvalidArgumentException
	 * @throws  \RuntimeException
	 */
	public function __construct(string $path = '')
	{
		$pathConfig = ini_get('session.save_path');

		// If the paths are empty, then we can't use this handler
		if (empty($path) && empty($pathConfig))
		{
			throw new \InvalidArgumentException('Invalid argument $path');
		}

		// If path is empty or equal to the the PHP configured path, set only the handler and use the PHP path directly
		if (empty($path) || $path === $pathConfig)
		{
			if (!headers_sent())
			{
				ini_set('session.save_handler', 'files');
			}

			return;
		}

		$baseDir = $path;

		if ($count = substr_count($path, ';'))
		{
			if ($count > 2)
			{
				throw new \InvalidArgumentException(sprintf('Invalid argument $path "%s"', $path));
			}

			// Characters after the last semi-colon are the path
			$baseDir = ltrim(strrchr($path, ';'), ';');
		}

		// Create the directory if it doesn't exist
		if (!is_dir($baseDir))
		{
			if (!mkdir($baseDir, 0755))
			{
				throw new \RuntimeException(sprintf('Could not create session directory "%s"', $baseDir));
			}
		}

		if (!headers_sent())
		{
			ini_set('session.save_path', $path);
			ini_set('session.save_handler', 'files');
		}
	}

	/**
	 * Test to see if the HandlerInterface is available
	 *
	 * @return  boolean  True on success, false otherwise
	 *
	 * @since   2.0.0
	 */
	public static function isSupported(): bool
	{
		return true;
	}
}
