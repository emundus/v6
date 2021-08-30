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

class RADSynchronizerEasysocial
{
	public function getData($userId, $mappings)
	{
		$data = [];
		$db   = Factory::getDbo();
		$sql  = 'SELECT cf.unique_key , fv.data FROM #__social_fields AS cf ' . ' INNER JOIN #__social_fields_data AS fv ' .
			' ON cf.id = fv.field_id ' . ' WHERE fv.uid = ' . $userId;
		$db->setQuery($sql);
		$rows = $db->loadObjectList('unique_key');
		foreach ($mappings as $fieldName => $mappingFieldName)
		{
			if ($mappingFieldName && isset($rows[$mappingFieldName]))
			{
				if (stristr($rows[$mappingFieldName]->data, ","))
				{
					$rows[$mappingFieldName]->data = explode(',', $rows[$mappingFieldName]->data);
				}
				$data[$fieldName] = $rows[$mappingFieldName]->data;
				if ($fieldName == 'address' && is_array($data[$fieldName]))
				{
					$data[$fieldName] = implode(',', $data[$fieldName]);
				}
			}
		}

		return $data;
	}
}
