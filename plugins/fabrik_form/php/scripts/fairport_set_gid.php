<?php
	/* Script created by Chad Windnagle with s-go consulting, LLC
	** Mon Jan 21 2012
	** Script sets and unsets users from joomla acl table pending the selection status of the
	** fabrik approved or unapproved element
	*/
	
	// No direct access
	defined('_JEXEC') or die('Restricted access');
	$app 		=	JFactory::getApplication();
	$input		=	$app->input;
	$uid		=	$input->get('festos_vendors___juserID', array(), 'array');
	$uid		=	JArrayHelper::getValue($uid, 0, '');
	
	$approved 	=	$input->get('festos_vendors___statusApproved', array(), 'array');
	$approved 	=	JArrayHelper::getValue($approved, 0, '');
	
	
	if($approved)
	{
		setApproved($uid, '17'); // add to approved category
		unsetApproved($uid, '18'); // remove from the not approved category
	}
	else
	{
		setApproved($uid, '18'); // add to the approved category
		unsetApproved($uid, '17'); // remove from the approved category
	}
	
	
	
	// functions to set and unset the tables
		
	function setApproved($uid, $gid)
	{
		$user = JFactory::getUser($uid);
		$groups = $user->getAuthorisedGroups();
		if (!in_array($gid, $groups))
		{
			$groups[] = $gid;
			$user->groups = $groups;
			$user->save();
		}
		
	}
	
	function unsetApproved($uid, $gid)
	{
		$user = JFactory::getUser($uid);
		$groups = $user->getAuthorisedGroups();
		if (in_array($gid, $groups))
		{
			$groups[] = $gid;
			unset($user->groups[$gid]);
			$user->save();
		}
	}
?>