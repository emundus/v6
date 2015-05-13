<?php
$api_AUP = JPATH_SITE . '/components/com_alphauserpoints/helper.php';
if (JFile::exists($api_AUP))
{
	require_once $api_AUP;
	$aup = new AlphaUserPointsHelper();

	// Define which user will receive the points.
	$user = JFactory::getUser();
	$aupid = AlphaUserPointsHelper::getAnyUserReferreID($user->get('id'));

	// Replace these if you want to show a specific reference for the attributed points
	$keyreference='akaka';

	// Shown in the user details page - description of what the point is for
	$datareference='wooo';

	// Override the plugin default points
	$randompoints = 24.30;

	// Not sure what this is for - if set to be greater than $randompoints then this is the # of points assigned
	$referraluserpoints = 230;
	$aup->userpoints('plgaup_fabrik', $aupid, $referraluserpoints, $keyreference, $datareference, $randompoints);

}
