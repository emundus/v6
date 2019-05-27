<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
$user = JFactory::getSession()->get('emundusUser');
$db = JFactory::getDBO();
$query = $db->getQuery(true);


$fnum = $data[current(preg_grep("/fnum/", array_keys($data)))];

$campaign_id = 'campaign_id';
$attachment_id = 'attachment_id';

if (empty($fnum)) {
	return false;
}

$query
	->select($db->qn($campaign_id))
	->from($db->qn('#__emundus_campaign_candidature'))
	->where($db->qn('fnum') . " LIKE " . $db->q($fnum));

$db->setQuery($query);

$cid = $db->loadResult();

$files = array();

foreach ($this->data as $key=>$value) {
	if (substr($value, 0, 8) === "/images/") {
		array_push($files, $value);
	}
}

if (empty($files)) {
	return false;
}
$insert = array();
foreach ($files as $file) {
	$fileName = pathinfo($file, PATHINFO_FILENAME).'.'.pathinfo($file, PATHINFO_EXTENSION);
	$attachmentId = explode('_',$fileName)[1];

	$query->clear()
		->select($db->qn('id'))
		->from($db->qn('#__emundus_uploads'))
		->where($db->qn('fnum') . " LIKE " . $db->q($fnum) . " AND " . $db->qn($attachment_id) . " = " . $attachmentId . " AND " . $db->qn($campaign_id) . " = " . $cid);

	$db->setQuery($query);

	if ($db->loadResult()) {

		$query->clear()
			->update($db->quoteName('#__emundus_uploads'))
			->set($db->qn('filename') . " = " . $db->q($fileName))
			->where($db->qn($campaign_id) . ' = ' . $cid . " AND " . $db->qn($attachment_id) . " = " . $attachmentId . " AND " . $db->qn($fnum) . " LIKE " . $db->q($fnum));

		$db->setQuery($query);

		try {
			$db->execute();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage('Probrème survenu au téléchargement des fichiers', 'message');
		}
	}
	else {

		// Insert columns.
		$columns = array('user_id', 'fnum', $campaign_id, $attachment_id, 'filename', 'can_be_deleted', 'can_be_viewed');

		// Insert values.
		$values = array($user->id, $db->q($fnum), $cid, $attachmentId, $db->q($fileName), 1, 1);

		// Prepare the insert query.
		$query->clear()
			->insert($db->quoteName('#__emundus_uploads'))
			->columns($db->quoteName($columns))
			->values(implode(',', $values));


		$db->setQuery($query);

		try {
			$db->execute();
		} catch (Exception $e) {
			JFactory::getApplication()->enqueueMessage('Probrème survenu au téléchargement des fichiers', 'message');
		}
	}
}