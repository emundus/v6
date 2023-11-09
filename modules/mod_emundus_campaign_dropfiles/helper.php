<?php
defined('_JEXEC') or die('Access Deny');

use Joomla\CMS\Date\Date;

jimport('joomla.access.access');

class modEmundusCampaignDropfilesHelper
{

	public function getFiles($column = null, $cid = null, $fnum = null)
	{
		$files = [];

		$db        = JFactory::getDbo();
		$query     = $db->getQuery(true);
		$jinput    = JFactory::getApplication()->input;
		$id        = $jinput->get('id') ? $jinput->getInt('id', null) : $jinput->getInt('cid', null);
		$id        = empty($id) ? $cid : $id;
		$groupUser = JFactory::getUser()->getAuthorisedGroups();
		$dateTime  = new Date('now', 'UTC');
		$now       = $dateTime->toSQL();

		// If empty id module is probably on a form
		if (!empty($id)) {
			if (!empty($fnum)) {
				// we should check current campaign workflow and get files from it if there are any
				require_once(JPATH_ROOT . '/components/com_emundus/models/campaign.php');
				$m_campaign    = new EmundusModelCampaign;
				$current_phase = $m_campaign->getCurrentCampaignWorkflow($fnum);

				if (!empty($current_phase->id) && !empty($current_phase->documents)) {
					foreach ($current_phase->documents as $key => $document) {
						$file_ext = explode('.', $document->href);
						$file_ext = end($file_ext);

						$file                 = new stdClass();
						$file->id             = $key;
						$file->catid          = 0;
						$file->title_file     = $document->title;
						$file->ext            = $file_ext;
						$file->title_category = 'Documents';
						$file->href           = $document->href;

						$files[] = $file;
					}

					return $files;
				}
			}

			$current_profile = JFactory::getSession()->get('emundusUser')->profile;

			if (!empty($column)) {
				try {
					$query->clear()
						->select([$db->quoteName('df.id', 'id'), $db->quoteName('df.catid', 'catid'), $db->quoteName('df.title', 'title_file'), $db->quoteName('df.ext', 'ext'), $db->quoteName('cat.path', 'title_category')])
						->from($db->quoteName('#__emundus_campaign_workflow_repeat_' . $column, 'cdf'))
						->leftJoin($db->quoteName('jos_emundus_campaign_workflow', 'cw') . ' ON ' . $db->quoteName('cw.id') . ' = ' . $db->quoteName('cdf.parent_id'))
						->leftJoin($db->quoteName('jos_dropfiles_files', 'df') . ' ON ' . $db->quoteName('df.id') . ' = ' . $db->quoteName('cdf.' . $column))
						->leftJoin($db->quoteName('jos_categories', 'cat') . ' ON ' . $db->quoteName('cat.id') . ' = ' . $db->quoteName('df.catid'))
						->where($db->quoteName('cw.profile') . ' = ' . $db->quote($current_profile));
					$db->setQuery($query);
					$files = $db->loadObjectList();
				}
				catch (Exception $e) {
					return false;
				}
			}
			else {
				$query
					->clear()
					->select([$db->quoteName('df.id', 'id'), $db->quoteName('df.catid', 'catid'), $db->quoteName('df.title', 'title_file'), $db->quoteName('df.ext', 'ext'), $db->quoteName('cat.path', 'title_category')])
					->from($db->quoteName('jos_dropfiles_files', 'df'))
					->leftJoin($db->quoteName('jos_categories', 'cat') . ' ON ' . $db->quoteName('cat.id') . ' = ' . $db->quoteName('df.catid'))
					->where($db->quoteName('df.publish') . ' <= ' . $db->quote($now))
					->andWhere([$db->quoteName('df.publish_down') . ' >= ' . $db->quote($now), $db->quoteName('df.publish_down') . ' = ' . $query->quote('0000-00-00 00:00:00')])
					->andWhere($db->quoteName('df.state') . ' = 1')
					->andWhere($db->quoteName('cat.extension') . ' = ' . $db->quote('com_dropfiles'))
					->andWhere('json_valid(`cat`.`params`)')
					->andWhere('json_extract(`cat`.`params`, "$.idCampaign") LIKE ' . $db->quote('"' . $id . '"'))
					->andWhere($db->quoteName('cat.access') . ' IN (' . implode(' , ', $groupUser) . ')')
					->group('df.ordering');

				try {
					$db->setQuery($query);
					$files = $db->loadObjectList();
				}
				catch (Exception $e) {
					return false;
				}
			}

			foreach ($files as $file) {
				$file->href = 'files/' . $file->catid . '/' . $file->title_category . '/' . $file->id . '/' . $file->title_file . '.' . $file->ext;
			}

			return $files;
		}
	}
}
