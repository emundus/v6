<?php
defined( '_JEXEC' ) or die();

$firstname = $data['jos_emundus_users___firstname'];
$lastname = $data['jos_emundus_users___lastname'];

$emundusUser = JFactory::getSession()->get('emundusUser');

if(!empty($firstname)) {
	$emundusUser->firstname = $firstname;
}

if(!empty($lastname)) {
	$emundusUser->lastname = $lastname;
}

if(!empty($firstname) || !empty($lastname))
{
	$user = JFactory::getUser();
	$user->set('name', $firstname . ' ' . $lastname);
	$user->save();

	$emundusUser->name = $firstname . ' ' . $lastname;
	JFactory::getSession()->set('emundusUser', $emundusUser);
}
