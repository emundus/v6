<?php
/**
 * Supports a custom field which display list of countries
 *
 * @package     Joomla.RAD
 * @subpackage  Form
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class RADFormFieldCountries extends RADFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'Countries';

	/**
	 * The query.
	 *
	 * @var    string
	 */
	protected $query;

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   EventbookingTableField  $row    the table object store form field definitions
	 * @param   mixed                   $value  the initial value of the form field
	 */
	public function __construct($row, $value)
	{
		parent::__construct($row, $value);

		$this->query = 'SELECT name AS value, name AS text FROM #__eb_countries WHERE published = 1 ORDER BY name';
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
			$db->setQuery($this->query);
			$options   = [];
			$options[] = HTMLHelper::_('select.option', '', Text::_('EB_SELECT_COUNTRY'));

			$countries = $db->loadObjectList();

			foreach ($countries as $country)
			{
				$options[] = HTMLHelper::_('select.option', $country->value, Text::_($country->text));
			}
		}
		catch (Exception $e)
		{
			$options = [];
		}

		return $options;
	}
}
