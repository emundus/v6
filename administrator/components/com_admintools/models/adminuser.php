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
 * A feature to get rid of Joomla!'s default administrator user (ID 62/42)
 */
class AdmintoolsModelAdminuser extends FOFModel
{
	/**
	 * Determines if the default Super Administrator account is in use with a simple
	 * trick. If a user exists with an ID less than the default Joomla! Super User ID
	 * (42 in 1.6 or 62 in 1.5) then the default Super Administrator account is considered
	 * to not be in use.
	 * 
	 * @return bool
	 */
	public function hasDefaultAdmin()
	{
		$id = 42;
		
		// Check if a user with a low ID is present
		$db = $this->getDBO();
		$query = $db->getQuery(true)
			->select(array(
				'COUNT(*)'
			))->from($db->quoteName('#__users'))
			->where($db->quoteName('id').' < '.$db->quote($id));
		$db->setQuery($query);
		$isuser = $db->loadResult();
		
		if(!$isuser) {
			// If now low-ID user exists, check if a user with ID of 62/42 exists
			$query = $db->getQuery(true)
			->select(array(
				'COUNT(*)'
			))->from($db->quoteName('#__users'))
			->where($db->quoteName('id').' = '.$db->quote($id));
			$db->setQuery($query);
			$defaultuser = $db->loadResult();
			if($defaultuser) {
				// Is that user blocker?
				$user = JFactory::getUser($id);
				if($user->block) {
					// The user is blocked, therefore you're not using the default SA account.
					return false;
				} else {
					// The user is not blocked. You are sing the default SA account.
					return true;
				}
			} else {
				return false;
			}
		} else {
			// Low-ID user exists; no default admin is present ;)
			return false;
		}
	}
	
	/**
	 * Get the default user's username
	 * @return string
	 */
	public function getDefaultUsername()
	{
		$id = 42;
		
		$user = JFactory::getUser($id);
		return $user->username;
	}
	
	/**
	 * Creates a new Super Administrator with a low ID and swaps his privileges with the old administrator user
	 * @param $newid int The new ID to use. Leave null to use a random one (recommended!)
	 */
	public function swapAccounts($newid = null)
	{
		$maxid = 41;
		
		if(empty($newid)) {
			$newid = rand(1,$maxid);
		}
		
		// Load the existing user
		$db = $this->getDBO();
		$query = $db->getQuery(true)
			->select(array(
				'*'
			))->from($db->quoteName('#__users'))
			->where($db->quoteName('id').' = '.$db->quote($maxid + 1));
		$db->setQuery($query);
		$olduser = $db->loadAssoc();
		
		// Create a copy of the old user's data and update the ID
		$newuser = $olduser;
		$newuser['id'] = $newid;

		// Insert the new user to the database
		$query = $db->getQuery(true)
			->insert($db->quoteName('#__users'));
		$sql = 'INSERT INTO `#__users` ';
		$keys = array(); $values = array();
		foreach($newuser as $k => $v)
		{
			$keys[] = $db->quoteName($k);
			$values[] = $db->Quote($v);
		}
		$query->columns($keys);
		$query->values(implode(', ',$values));
		$db->setQuery($query);
		$db->execute();
		
		JLoader::import('joomla.database.table.user');
		$userTable = JTable::getInstance('user');
		
		
		// Time to promote the new user to a Super Administrator!
		$ugmap = (object)array(
			'user_id'	=> $newid,
			'group_id'	=> 8
		);
		$db->insertObject('#__user_usergroup_map', $ugmap);

		// Reset the old user's password to something stupid and block his access completely!
		JLoader::import('joomla.user.helper');
		$prefix = $this->getState('prefix',null);
		if(empty($prefix)) {
			$prefix = JUserHelper::genRandomPassword(4);
		}
		$password = JUserHelper::genRandomPassword(32);
		$salt = JUserHelper::genRandomPassword(32);
		
		$olduser['username'] = $prefix.'_'.$olduser['username'];
		$olduser['password'] = JUserHelper::getCryptedPassword($password, $salt);
		$olduser['email'] = $prefix.'_'.$olduser['email'];
		$olduser['block'] = 1;
		$olduser['sendEmail'] = 0;
		$userTable->save($olduser);
	}
}