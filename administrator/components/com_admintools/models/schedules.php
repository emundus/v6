<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\Engine\Platform;
use Akeeba\Engine\Util\Comconfig;

class AdmintoolsModelSchedules extends F0FModel
{
	public function __construct(array $config)
	{
		parent::__construct($config);

		// Load the Akeeba Engine autoloader
		define('AKEEBAENGINE', 1);
		require_once JPATH_ADMINISTRATOR . '/components/com_admintools/engine/Autoloader.php';

		// Load the platform
		\Akeeba\Engine\Platform::addPlatform('filescan', JPATH_ADMINISTRATOR . '/components/com_admintools/platform/Filescan');
	}


	public function getPaths()
	{
		$ret = (object) array(
			'cli'      => (object) array(
				'supported' => false,
				'path'      => false
			),
			'altcli'   => (object) array(
				'supported' => false,
				'path'      => false
			),
			'frontend' => (object) array(
				'supported' => false,
				'path'      => false,
			),
			'info'     => (object) array(
				'windows'   => false,
				'php_path'  => false,
				'root_url'  => false,
				'secret'    => '',
				'feenabled' => false,
			)
		);

		// Get the profile ID
		$profileid = 1;

		// Get the absolute path to the site's root
		$absolute_root = rtrim(realpath(JPATH_ROOT), DIRECTORY_SEPARATOR);
		// Is this Windows?
		$ret->info->windows = (DIRECTORY_SEPARATOR == '\\') || (substr(strtoupper(PHP_OS), 0, 3) == 'WIN');
		// Get the pseudo-path to PHP CLI
		if ($ret->info->windows)
		{
			$ret->info->php_path = 'c:\path\to\php.exe';
		}
		else
		{
			$ret->info->php_path = '/path/to/php';
		}
		// Get front-end backup secret key
		$ret->info->secret    = Comconfig::getValue('frontend_secret_word', '');
		$ret->info->feenabled = Comconfig::getValue('frontend_enable', false);
		// Get root URL
		$ret->info->root_url = rtrim(Comconfig::getValue('siteurl', ''), '/');

		// Get information for CLI CRON script
		$ret->cli->supported = true;
		$ret->cli->path      = $absolute_root . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR . 'admintools-filescanner.php';

		// Get information for alternative CLI CRON script
		$ret->altcli->supported = true;

		if (trim($ret->info->secret) && $ret->info->feenabled)
		{
			$ret->altcli->path = $absolute_root . DIRECTORY_SEPARATOR . 'cli' . DIRECTORY_SEPARATOR . 'admintools-filescanner-alt.php';
		}

		// Get information for front-end backup
		$ret->frontend->supported = true;
		if (trim($ret->info->secret) && $ret->info->feenabled)
		{
			$ret->frontend->path = 'index.php?option=com_admintools&view=filescanner&key='
			                       . urlencode($ret->info->secret);
		}

		return $ret;
	}
}