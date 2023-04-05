<?php
/**
 * Create a list from an SQL query
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       1.6
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\ListField;

require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php';

FormHelper::loadFieldClass('list');

/**
 * Renders a SQL element
 *
 * @package  Fabrik
 * @since    3.0
 */

class JFormFieldSQL2 extends ListField
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $name = 'SQL';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */

	protected function getInput()
	{
		$db = FabrikWorker::getDbo(true);
		$attributes = $this->element->attributes();
		$check = strtolower((string)$attributes->checkexists) == 'true' ? true : false;

		if ($check)
		{
			$q = explode(" ", (string)$attributes->query);
			$i = array_search('FROM', $q);

			if (!$i)
			{
				$i = array_search('from', $q);
			}

			$i++;
			$tbl = $db->replacePrefix($q[$i]);
			$db->setQuery("SHOW TABLES");
			$rows = $db->loadColumn();
			$found = in_array($tbl, $rows) ? true : false;

			if (!$found)
			{
				$this->addOption(htmlspecialchars($tbl . ' not found'), ['value'=>'']);
				return array(HTMLHelper::_('select.option', $tbl . ' not found', ''));
			}
		}

		$db->setQuery((string)$attributes->query);
		$rows = $db->loadObjectList();

		if (strtolower((string)$attributes->add_select) == 'true')
		{
			$this->addOption(htmlspecialchars(Text::_('COM_FABRIK_PLEASE_SELECT')), ['value'=>'']);
		}

		foreach ($rows as $row) {
			$this->addOption(htmlspecialchars($row->text), ['value'=>$row->value]);
		}

		return parent::getInput();
	}

}
