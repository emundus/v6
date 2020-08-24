<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

/**
 * Allows Joomla! to use MySQL's UTF8MB4 connection type, supporting proper multibyte UTF-8 characters such as Emoji
 */
class AtsystemFeatureUtf8mb4 extends AtsystemFeatureAbstract
{
	protected $loadOrder = 0;

	public function isEnabled()
	{
		// This feature only applies to Joomla 3.7 and earlier
		return version_compare(JVERSION, '3.7.999', 'le');
	}

	public function onAfterInitialise()
	{
		$db = $this->container->db;

		// If it's not MySQL I don't have to do anything at all
		if (stristr($db->name, 'mysql') === false)
		{
			return;
		}

		// Get the current collation
		$collation = $db->getCollation();

		// If it's not a UTF-8 multibyte (utf8mb4) collation I don't have to do anything at all
		if (substr($collation, 0, 8) != 'utf8mb4_')
		{
			return;
		}

		// Try to force a UTF8MB4 connection
		try
		{
			$db->setQuery('SET NAMES utf8mb4 COLLATE ' . $collation)->execute();

			return;
		}
		catch (Exception $e)
		{
			// If we failed don't worry, the next statement will revert the connection to plain old UTF-8
		}

		$db->setQuery('SET NAMES utf8')->execute();
	}
}
