<?php
/**
 * @package     FOF
 * @copyright   2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Form\Header;

defined('_JEXEC') or die;

/**
 * Access level field header
 */
class AccessLevel extends Selectable
{
	/**
	 * Method to get the list of access levels
	 *
	 * @return  array	A list of access levels.
	 *
	 * @since   2.0
	 */
	protected function getOptions()
	{
		$db    = $this->form->getContainer()->platform->getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id AS value, a.title AS text');
		$query->from('#__viewlevels AS a');
		$query->group('a.id, a.title, a.ordering');
		$query->order('a.ordering ASC');
		$query->order($query->qn('title') . ' ASC');

		// Get the options.
		$db->setQuery($query);
		$options = $db->loadObjectList();

		return $options;
	}
}
