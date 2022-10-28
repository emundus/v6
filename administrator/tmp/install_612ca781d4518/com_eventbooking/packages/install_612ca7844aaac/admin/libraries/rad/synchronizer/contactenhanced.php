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

class RADSynchronizerContactenhanced
{
	public function getData($userId, $mappings)
	{
		$data  = [];
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__ce_details')
			->where('user_id=' . $userId);
		$db->setQuery($query);
		$row = $db->loadObject();
		if ($row)
		{
			foreach ($mappings as $fieldName => $mappingFieldName)
			{
				if ($mappingFieldName && isset($row->{$mappingFieldName}))
				{
					$data[$fieldName] = $row->{$mappingFieldName};
				}
			}
		}

		return $data;
	}
}
