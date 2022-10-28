<?php
/**
 * @package     RAD
 * @subpackage  Synchronizer
 *
 * @copyright   Copyright (C) 2015 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class RADSynchronizerCommunitybuilder
{
	public function getData($userId, $mappings)
	{
		$data  = [];
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__comprofiler')
			->where('user_id=' . $userId);
		$db->setQuery($query);
		$profile = $db->loadObject();
		if ($profile)
		{
			foreach ($mappings as $fieldName => $mappingFieldName)
			{
				if ($mappingFieldName && isset($profile->{$mappingFieldName}))
				{
					if (stristr($profile->{$mappingFieldName}, "|*|"))
					{
						$profile->{$mappingFieldName} = explode('|*|', $profile->{$mappingFieldName});
					}
					$data[$fieldName] = $profile->{$mappingFieldName};
				}
			}
		}

		return $data;
	}
}
