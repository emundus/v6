<?php

$db = JFactory::getDBO();
$user = JFactory::getUser();

$profile = $data['jos_emundus_users___profile_raw'][0];
$user_id = $data['jos_emundus_users___user_id_raw'];

if ($profile == 1002) {

	$db = JFactory::getDbo();
	$query = $db->getQuery(true);

	// If the user is DRH: check if he has a company already.
	$query->select($db->quoteName('id'))
		->from($db->quoteName('#__emundus_user_entreprise'))
		->where($db->quoteName('user').' = '.$user_id.' AND '.$db->quoteName('profile').' = 1002');
	$db->setQuery($query);

	try {
		if (!empty($db->loadResult())) {
			return;
		}
	} catch (Exception $e) {
		return;
	}

	$current_user = JFactory::getUser($user_id);

	// Set the user param to notify him of the fact that he needs a company.
	$current_user->setParam('needs_company', 'true');

	// Get the raw User Parameters
	$params = $current_user->getParameters();

	// Set the user table instance to include the new token.
	$table = JTable::getInstance('user', 'JTable');
	$table->load($user_id);
	$table->params = $params->toString();
	$table->store();
}