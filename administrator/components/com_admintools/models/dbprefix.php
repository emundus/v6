<?php
/**
 *  @package AdminTools
 *  @copyright Copyright (c)2010-2013 Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 *  @version $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * A feature to change the site's database prefix - Model
 */
class AdmintoolsModelDbprefix extends FOFModel
{
	/**
	 * Checks if the site is using the default database prefix (jos_)
	 * @return unknown_type
	 */
	public function isDefaultPrefix()
	{
		$prefix = $this->getCurrentPrefix();
		return ($prefix == 'jos_');
	}
	
	/**
	 * Returns the currently used database prefix
	 * @return string
	 */
	public function getCurrentPrefix()
	{
		$config = JFactory::getConfig();
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$prefix = $config->get('dbprefix','');
		} else {
			$prefix = $config->getValue('config.dbprefix','');
		}
		return $prefix;
	}
	
	/**
	 * Gets a random database prefix of a specified length. For instance, if the requested
	 * length is 3, it will consist of three random letters and an underscore. 
	 * @param $length int The requested alpha portion length of the prefix (3-6)
	 * @return string
	 */
	public function getRandomPrefix($length = 3)
	{
		$validchars = 'abcdefghijklmnopqrstuvwxyz';
		$charslength = strlen($validchars);
		
		if($length < 3) $length = 3;
		if($length > 6) $length = 6;
		
		$prefix = '';
		
		for($i = 0; $i < $length; $i++)
		{
			$rand = rand(0, $charslength - 1);
			$prefix .= substr($validchars, $rand, 1);
		}
		
		$prefix .= '_';
		
		return $prefix;
	}
	
	/**
	 * Validates a prefix. The prefix must be 3-6 lowercase characters followed by
	 * an underscore and must not alrady exist in the current database. It must
	 * also not be jos_ or bak_.
	 * 
	 * @param $prefix string The prefix to check
	 * @return string|bool The validated prefix or false if the prefix is invalid
	 */
	public function validatePrefix($prefix)
	{
		// Check that the prefix is not jos_ or bak_
		if( ($prefix == 'jos_') || ($prefix == 'bak_') ) return false;
		
		// Check that we're not trying to reuse the same prefix
		$config = JFactory::getConfig();
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$oldprefix = $config->get('dbprefix','');
		} else {
			$oldprefix = $config->getValue('config.dbprefix','');
		}
		if($prefix == $oldprefix) return false;
		
		// Check the length
		$pLen = strlen($prefix);
		if( ($pLen < 4) || ($pLen > 6) ) return false;
		
		// Check that the prefix ends with an underscore
		if( substr($prefix,-1) != '_' ) return false;
		
		// Check that the part before the underscore is lowercase letters
		$valid = preg_match('/[\w]_/i', $prefix);
		if($valid === 0) return false;
		
		// Turn the prefix into lowercase
		$prefix = strtolower($prefix);
		
		// Check if the prefix already exists in the database
		$db = $this->getDBO();
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$dbname = $config->get('db','');
		} else {
			$dbname = $config->getValue('config.db','');
		}
		$sql = "SHOW TABLES WHERE `Tables_in_{$dbname}` like '{$prefix}%'";
		$db->setQuery($sql);
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$existing_tables = $db->loadColumn();
		} else {
			$existing_tables = $db->loadResultArray();
		}
		if(count($existing_tables)) {
			// Sometimes we have false alerts, e.g. a prefix of dev_ will match tables starting with dev15_ or dev16_
			$realCount = 0;
			foreach($existing_tables as $check) {
				if(substr($check,0,$pLen) == $prefix) {
					$realCount++;
					break;
				}
			}
			if($realCount) return false;
		}
		
		return $prefix;
	}
	
	/**
	 * Updates the configuration.php file with the given prefix
	 * @param $prefix string The prefix to write to the configuration.php file
	 * @return bool False if writing to the file was not possible
	 */
	public function updateConfiguration($prefix)
	{
		// Load the configuration and replace the db prefix
		$config = JFactory::getConfig();
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$oldprefix = $config->get('dbprefix', $prefix);
		} else {
			$oldprefix = $config->getValue('config.dbprefix', $prefix);
		}
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$config->set('dbprefix', $prefix);
		} else {
			$config->setValue('config.dbprefix', $prefix);
		}
		
		$newConfig = $config->toString('PHP', 'config', array('class' => 'JConfig'));
		// On some occasions, Joomla! 1.6 ignores the configuration and produces "class c". Let's fix this!
		$newConfig = str_replace('class c {','class JConfig {',$newConfig);
		
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$config->set('dbprefix', $oldprefix);
		} else {
			$config->setValue('config.dbprefix', $oldprefix);
		}
		
		// Try to write out the configuration.php
		$filename = JPATH_ROOT.DIRECTORY_SEPARATOR.'configuration.php';
		$result = self::write($filename, $newConfig);
		if($result !== false) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Performs the actual schema change
	 * @param $prefix string The new prefix
	 * @return bool False if the schema could not be changed
	 */
	public function changeSchema($prefix)
	{
		$config = JFactory::getConfig();
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$oldprefix = $config->get('dbprefix','');
			$dbname = $config->get('db','');
		} else {
			$oldprefix = $config->getValue('config.dbprefix','');
			$dbname = $config->getValue('config.db','');
		}
		
		$db = $this->getDBO();
		$sql = "SHOW TABLES WHERE `Tables_in_{$dbname}` like '{$oldprefix}%'";
		$db->setQuery($sql);
		
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$oldTables = $db->loadColumn();
		} else {
			$oldTables = $db->loadResultArray();
		}
		
		if(empty($oldTables)) return false;

		foreach($oldTables as $table)
		{
			$newTable = $prefix . substr($table, strlen($oldprefix));
			$sql = "RENAME TABLE `$table` TO `$newTable`";
			$db->setQuery($sql);
			if(!$db->execute()) {
				// Something went wrong; I am pulling the plug and hope for the best
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Performs the actual database changes and configuration updates
	 * @param $prefix string The new prefix
	 * @return bool|string True on success, the error message string on failure
	 */
	public function performChanges($prefix)
	{
		// Cache the old prefix
		$config = JFactory::getConfig();
		if(version_compare(JVERSION, '3.0', 'ge')) {
			$oldprefix = $config->get('config','');
		} else {
			$oldprefix = $config->getValue('config.dbprefix','');
		}
		
		// Validate the prefix
		$prefix = $this->validatePrefix($prefix);
		if($prefix === false) {
			return JText::sprintf('ATOOLS_ERR_DBPREFIX_INVALIDPREFIX', $prefix);
		}
		
		// Try to change the configuration.php
		if(!$this->updateConfiguration($prefix)) {
			return JText::_('ATOOLS_ERR_DBPREFIX_CANTSAVECONFIGURATION');
		}

		// Try to perform the database changes
		if(!$this->changeSchema($prefix)) {
			// Revert the configuration.php
			$this->updateConfiguration($oldprefix);
			// and return an error string
			return JText::_('ATOOLS_ERR_DBPREFIX_COULDNTCHANGESCHEMA');
		}
		
		// All done. Hopefully nothing broke.
		return true;
	}
	
	private function write($file, $buffer)
	{
		// Initialize variables
		JLoader::import('joomla.client.helper');
		$FTPOptions = JClientHelper::getCredentials('ftp');

		// If the destination directory doesn't exist we need to create it
		if (!file_exists(dirname($file))) {
			JLoader::import('joomla.filesystem.folder');
			JFolder::create(dirname($file));
		}

		if ($FTPOptions['enabled'] == 1) {
			// Connect the FTP client
			JLoader::import('joomla.client.ftp');
			if(version_compare(JVERSION,'3.0','ge')) {
				$ftp = JClientFTP::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);
			} else {
				$ftp = JFTP::getInstance($FTPOptions['host'], $FTPOptions['port'], array(), $FTPOptions['user'], $FTPOptions['pass']);
			}

			// Translate path for the FTP account and use FTP write buffer to file
			$file = JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $file), '/');
			$ret = $ftp->write($file, $buffer);
		} else {
			$ret = @file_put_contents($file, $buffer);
		}
		if(!$ret) {
			JLoader::import('joomla.filesystem.file');
			JFile::write($file, $buffer);
		}
		return $ret;
	}
	
}