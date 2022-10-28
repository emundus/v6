<?php
/**
 * Supports an custom SQL select list
 *
 * @package     Joomla.RAD
 * @subpackage  Form
 */

use Joomla\CMS\Factory;

class RADFormFieldSQL extends RADFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	protected $type = 'SQL';

	/**
	 * The query.
	 *
	 * @var    string
	 */
	protected $query;

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   JTable  $row    the table object store form field definitions
	 * @param   mixed   $value  the initial value of the form field
	 */
	public function __construct($row, $value)
	{
		parent::__construct($row, $value);

		$this->query = $row->default_values;
	}

	/**
	 * Method to get the custom field options.
	 * Use the query attribute to supply a query to generate the list.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		try
		{
			$db = Factory::getDbo();

			foreach ($this->replaceData as $key => $value)
			{
				$this->query = str_replace('[' . strtoupper($key) . ']', $value, $this->query);
			}

			$this->query = str_replace('[EVENT_ID]', (int) $this->eventId, $this->query);

			$db->setQuery($this->query);

			$options = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$options = [];
		}

		return $options;
	}
}
