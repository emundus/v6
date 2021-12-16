<?php
/**
 * @package   AkeebaLoginGuard
 * @copyright Copyright (c)2016-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die();

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\Component\Modules\Administrator\Helper\ModulesHelper as ModulesHelperJ4;

// Prevent PHP fatal errors if this somehow gets accidentally loaded multiple times
if (class_exists('JFormFieldModulesPosition'))
{
	return;
}

// Load the base form field class
FormHelper::loadFieldClass('list');

/**
 * Yes/No switcher, compatible with Joomla 3 and 4
 *
 * @noinspection PhpUnused
 */
class JFormFieldModulesPosition extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.4.2
	 */
	protected $type = 'ModulesPosition';

	/**
	 * Client name.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $client;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to get the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   4.0.0
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'client':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to set the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'client':
				$this->$name = (string) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a Form object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     FormField::setup()
	 * @since   4.0.0
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$result = parent::setup($element, $value, $group);

		if ($result === true)
		{
			$this->client = $this->element['client'] ? (string) $this->element['client'] : 'site';
		}

		return $result;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.4.2
	 */
	public function getOptions()
	{
		Factory::getApplication()->getLanguage()->load('com_modules');
		$clientId = $this->client === 'administrator' ? 1 : 0;

		if (version_compare(JVERSION, '3.999.999', 'le'))
		{
			if (!class_exists('ModulesHelper'))
			{
				require_once JPATH_ADMINISTRATOR . '/components/com_modules/helpers/modules.php';
			}

			$options = \ModulesHelper::getPositions($clientId);
		}
		else
		{
			$options  = ModulesHelperJ4::getPositions($clientId);
		}

		return array_merge(parent::getOptions(), $options);
	}
}