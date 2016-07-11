<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsModelWafblacklists extends F0FModel
{
	public function buildQuery($overrideLimits = false)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
                    ->select(array('*'))
        			->from($db->quoteName('#__admintools_wafblacklists'));

        if($verb = $this->getState('fverb'))
        {
            $query->where($db->qn('verb').' = '.$db->q($verb));
        }

		$fltOption = $this->getState('foption', null, 'string');
		if ($fltOption)
		{
			$fltOption = '%' . $fltOption . '%';
			$query->where($db->quoteName('option') . ' LIKE ' . $db->quote($fltOption));
		}

		$fltView = $this->getState('fview', null, 'string');
		if ($fltView)
		{
			$fltView = '%' . $fltView . '%';
			$query->where($db->quoteName('view') . ' LIKE ' . $db->quote($fltView));
		}

		$fltQuery = $this->getState('fquery', null, 'string');
		if ($fltQuery)
		{
			$fltQuery = '%' . $fltQuery . '%';
			$query->where($db->quoteName('query') . ' LIKE ' . $db->quote($fltQuery));
		}

        if($content = $this->getState('fquery_content'))
        {
            $query->where($db->qn('query_content').' LIKE '.$db->q($db->escape($content), false));
        }

		if (!$overrideLimits)
		{
			$order = $this->getState('filter_order', null, 'cmd');
			if (!in_array($order, array_keys($this->getTable()->getData())))
			{
				$order = 'id';
			}
			$dir = $this->getState('filter_order_Dir', 'ASC', 'cmd');
			$query->order($db->qn($order) . ' ' . $dir);
		}

		return $query;
	}
}