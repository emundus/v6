<?php
/**
 * @version        $Id: gantryform.class.php 30069 2016-03-08 17:45:33Z matias $
 * @author         RocketTheme http://www.rockettheme.com
 * @copyright      Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * derived from Joomla with original copyright and license
 * @copyright      Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('GANTRY_VERSION') or die;

gantry_import('core.config.gantryformfield');
gantry_import('core.config.gantryformgroup');
gantry_import('core.utilities.gantryregistry');
gantry_import('core.utilities.gantrysimplexmlelement');
gantry_import('core.utilities.gantryarrayhelper');
gantry_import('core.config.gantryformhelper');


class GantryForm
{
	/**
	 * The GantryRegistry data store for form fields during display.
	 *
	 * @var        object
	 * @since    1.6
	 */
	protected $data;

	/**
	 * The form object errors array.
	 *
	 * @var        array
	 * @since    1.6
	 */
	protected $errors = array();

	/**
	 * The name of the form instance.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $name;

	/**
	 * The form object options for use in rendering and validation.
	 *
	 * @var        array
	 * @since    1.6
	 */
	protected $options = array();

	/**
	 * The form XML definition.
	 *
	 * @var        object
	 * @since    1.6
	 */
	protected $xml;

	/**
	 * Form instances.
	 *
	 * @var        array
	 * @since    1.6
	 */
	protected static $forms = array();


	var $control = null;

	/**
	 * Method to instantiate the form object.
	 *
	 * @param    string    $name        The name of the form.
	 * @param    array     $options     An array of form options.
	 *
	 * @return    void
	 * @since    1.6
	 */
	public function __construct(&$control, $name, array $options = array())
	{
		// Set the name for the form.
		$this->name = $name;

		$this->control = &$control;

		// Initialize the GantryRegistry data.
		$this->data = new GantryRegistry();

		// Set the options if specified.
		$this->options['control'] = isset($options['control']) ? $options['control'] : false;
	}

	/**
	 * Method to bind data to the form.
	 *
	 * @param    mixed    $data    An array or object of data to bind to the form.
	 *
	 * @return    boolean    True on success.
	 * @since    1.6
	 */
	public function bind($data)
	{
		// Make sure there is a valid GantryForm XML document.
		if (!$this->xml instanceof GantrySimpleXMLElement) {
			return false;
		}

		// The data must be an object or array.
		if (!is_object($data) && !is_array($data)) {
			return false;
		}

		// Convert the input to an array.
		if (is_object($data)) {
			if ($data instanceof GantryRegistry) {
				// Handle a GantryRegistry.
				$data = $data->toArray();
			} else if ($data instanceof JObject) {
				// Handle a JObject.
				$data = $data->getProperties();
			} else {
				// Handle other types of objects.
				$data = (array)$data;
			}
		}

		// Process the input data.
		foreach ($data as $k => $v) {

			if ($this->findField($k)) {
				// If the field exists set the value.
				$this->data->set($k, $v);
			} else if (is_object($v) || GantryArrayHelper::isAssociative($v)) {
				// If the value is an object or an associative array hand it off to the recursive bind level method.
				$this->bindLevel($k, $v);
			}
		}

		return true;
	}

	/**
	 * Method to bind data to the form for the group level.
	 *
	 * @param    string    $group    The dot-separated form group path on which to bind the data.
	 * @param    mixed     $data     An array or object of data to bind to the form for the group level.
	 *
	 * @return    void
	 * @since    1.6
	 */
	protected function bindLevel($group, $data)
	{
		// Ensure the input data is an array.
		settype($data, 'array');

		// Process the input data.
		foreach ($data as $k => $v) {

			if ($this->findField($k, $group)) {
				// If the field exists set the value.
				$this->data->set($group . '.' . $k, $v);
			} else if (is_object($v) || GantryArrayHelper::isAssociative($v)) {
				// If the value is an object or an associative array, hand it off to the recursive bind level method
				$this->bindLevel($group . '.' . $k, $v);
			}
		}
	}

	/**
	 * Method to filter the form data.
	 *
	 * @param    array     $data     An array of field values to filter.
	 * @param    string    $group    The dot-separated form group path on which to filter the fields.
	 *
	 * @return    mixed    boolean    True on sucess.
	 * @since    1.6
	 */
	public function filter($data, $group = null)
	{
		// Make sure there is a valid GantryForm XML document.
		if (!$this->xml instanceof GantrySimpleXMLElement) {
			return false;
		}

		// Initialize variables.
		$input  = new GantryRegistry($data);
		$output = new GantryRegistry();

		// Get the fields for which to filter the data.
		$fields = $this->findFieldsByGroup($group);
		if (!$fields) {
			// PANIC!
			return false;
		}

		// Filter the fields.
		foreach ($fields as $field) {
			// Initialise variables.
			$name = (string)$field['name'];

			// Get the field groups for the element.
			$attrs  = $field->xpath('ancestor::fields[@name]/@name');
			$groups = array_map('strval', $attrs ? $attrs : array());
			$group  = implode('.', $groups);

			// Get the field value from the data input.
			if ($group) {
				// Filter the value if it exists.
				if ($input->exists($group . '.' . $name)) {
					$output->set($group . '.' . $name, $this->filterField($field, $input->get($group . '.' . $name, (string)$field['default'])));
				}
			} else {
				// Filter the value if it exists.
				if ($input->exists($name)) {
					$output->set($name, $this->filterField($field, $input->get($name, (string)$field['default'])));
				}
			}
		}

		return $output->toArray();
	}

	/**
	 * Return all errors, if any.
	 *
	 * @return    array    Array of error messages or JException objects.
	 * @since    1.6
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Method to get a form field represented as a GantryFormField object.
	 *
	 * @param    string    $name     The name of the form field.
	 * @param    string    $group    The optional dot-separated form group path on which to find the field.
	 * @param    mixed     $value    The optional value to use as the default for the field.
	 *
	 * @return    mixed    The GantryFormField object for the field or boolean false on error.
	 * @since    1.6
	 */
	public function getField($name, $group = null, $value = null)
	{
		// Make sure there is a valid GantryForm XML document.
		if (!$this->xml instanceof GantrySimpleXMLElement) {
			return false;
		}

		// Attempt to find the field by name and group.
		$element = $this->findField($name, $group);

		// If the field element was not found return false.
		if (!$element) {
			return false;
		}

		return $this->loadField($element, $group, $value);
	}

	/**
	 * Method to get an attribute value from a field XML element.  If the attribute doesn't exist or
	 * is null then the optional default value will be used.
	 *
	 * @param    string    $name         The name of the form field for which to get the attribute value.
	 * @param    string    $attribute    The name of the attribute for which to get a value.
	 * @param    mixed     $default      The optional default value to use if no attribute value exists.
	 * @param    string    $group        The optional dot-separated form group path on which to find the field.
	 *
	 * @return    mixed    The attribute value for the field.
	 * @since    1.6
	 */
	public function getFieldAttribute($name, $attribute, $default = null, $group = null)
	{
		// Make sure there is a valid GantryForm XML document.
		if (!$this->xml instanceof GantrySimpleXMLElement) {
			// TODO: throw exception.
			return $default;
		}

		// Find the form field element from the definition.
		$element = $this->findField($name, $group);

		// If the element exists and the attribute exists for the field return the attribute value.
		if (($element instanceof GantrySimpleXMLElement) && ((string)$element[$attribute])) {
			return (string)$element[$attribute];
		} // Otherwise return the given default value.
		else {
			return $default;
		}
	}

	/**
	 * Method to get an array of GantryFormField objects in a given fieldset by name.  If no name is
	 * given then all fields are returned.
	 *
	 * @param    string    $set    The optional name of the fieldset.
	 *
	 * @return    array    The array of GantryFormField objects in the fieldset.
	 * @since    1.6
	 */
	public function getFieldset($set = null, $forcegroup = array())
	{
		// Initialise variables.
		$fields = array();

		// Get all of the field elements in the fieldset.
		if ($set) {
			$elements = $this->findFieldsByFieldset($set);
		} // Get all fields.
		else {
			$elements = $this->findFieldsByGroup();
		}

		// If no field elements were found return empty.
		if (empty($elements)) {
			return $fields;
		}

		// Build the result array from the found field elements.
		foreach ($elements as $element) {
			// Get the field groups for the element.
			$attrs  = $element->xpath('ancestor::fields[@name]/@name');
			$groups = array_map('strval', $attrs ? $attrs : array());
			if (!empty($forcegroup)) {
				array_shift($groups);
				foreach (array_reverse($forcegroup) as $fgroup) {
					array_unshift($groups, $fgroup);
				}
			}
			$group = implode('.', $groups);

			// If the field is successfully loaded add it to the result array.
			if ($field = $this->loadField($element, $group)) {
				$fields[$field->id] = $field;
			}
		}

		return $fields;
	}

	/**
	 * Method to get an array of fieldset objects optionally filtered over a given field group.
	 *
	 * @param    string    $group    The dot-separated form group path on which to filter the fieldsets.
	 *
	 * @return    array    The array of fieldset objects.
	 * @since    1.6
	 */
	public function getFieldsets($group = null)
	{
		// Initialise variables.
		$fieldsets = array();
		$sets      = array();

		// Make sure there is a valid GantryForm XML document.
		if (!$this->xml instanceof GantrySimpleXMLElement) {
			return $fieldsets;
		}

		if ($group) {
			// Get the fields elements for a given group.
			$elements =  $this->findGroup($group);

			foreach ($elements as & $element) {
				// Get an array of <fieldset /> elements and fieldset attributes within the fields element.
				if ($tmp = $element->xpath('descendant::fieldset[@name] | descendant::field[@fieldset]/@fieldset')) {
					$sets = array_merge($sets, (array)$tmp);
				}
			}
		} else {
			// Get an array of <fieldset /> elements and fieldset attributes.
			$sets = $this->xml->xpath('//fieldset[@name] | //field[@fieldset]/@fieldset');
		}

		// If no fieldsets are found return empty.
		if (empty($sets)) {
			return $fieldsets;
		}

		// Process each found fieldset.
		foreach ($sets as $set) {
			// Are we dealing with a fieldset element?
			if ((string)$set['name']) {

				// Only create it if it doesn't already exist.
				if (empty($fieldsets[(string)$set['name']])) {

					// Build the fieldset object.
					$fieldset = (object)array('name' => '', 'label' => '', 'description' => '');
					foreach ($set->attributes() as $name => $value) {
						$fieldset->{$name} = (string)$value;
					}

					// Add the fieldset object to the list.
					$fieldsets[$fieldset->name] = $fieldset;
				}
			} // Must be dealing with a fieldset attribute.
			else {

				// Only create it if it doesn't already exist.
				if (empty($fieldsets[(string)$set])) {

					// Attempt to get the fieldset element for data (throughout the entire form document).
					$tmp = $this->xml->xpath('//fieldset[@name="' . (string)$set . '"]');

					// If no element was found, build a very simple fieldset object.
					if (empty($tmp)) {
						$fieldset = (object)array('name' => (string)$set, 'label' => '', 'description' => '');
					} // Build the fieldset object from the element.
					else {
						$fieldset = (object)array('name' => '', 'label' => '', 'description' => '');
						foreach ($tmp[0]->attributes() as $name => $value) {
							$fieldset->{$name} = (string)$value;
						}
					}

					// Add the fieldset object to the list.
					$fieldsets[$fieldset->name] = $fieldset;
				}
			}
		}

		return $fieldsets;
	}

	/**
	 * Method to get the form control. This string serves as a container for all form fields. For
	 * example, if there is a field named 'foo' and a field named 'bar' and the form control is
	 * empty the fields will be rendered like: <input name="foo" /> and <input name="bar" />.  If
	 * the form control is set to 'gantry' however, the fields would be rendered like:
	 * <input name="gantry[foo]" /> and <input name="gantry[bar]" />.
	 *
	 * @return    string    The form control string.
	 * @since    1.6
	 */
	public function getFormControl()
	{
		return (string)$this->options['control'];
	}

	/**
	 * Method to get an array of GantryFormField objects in a given field group by name.
	 *
	 * @param    string     $group     The dot-separated form group path for which to get the form fields.
	 * @param    boolean    $nested    True to also include fields in nested groups that are inside of the
	 *                                 group for which to find fields.
	 *
	 * @return    array    The array of GantryFormField objects in the field group.
	 * @since    1.6
	 */
	public function getGroup($group, $nested = false)
	{
		// Initialise variables.
		$fields = array();

		// Get all of the field elements in the field group.
		$elements = $this->findFieldsByGroup($group, $nested);

		// If no field elements were found return empty.
		if (empty($elements)) {
			return $fields;
		}

		// Build the result array from the found field elements.
		foreach ($elements as $element) {
			// If the field is successfully loaded add it to the result array.
			if ($field = $this->loadField($element, $group)) {
				$fields[$field->id] = $field;
			}
		}

		return $fields;
	}

	/**
	 * Method to get a form field markup for the field input.
	 *
	 * @param    string    $name     The name of the form field.
	 * @param    string    $group    The optional dot-separated form group path on which to find the field.
	 * @param    mixed     $value    The optional value to use as the default for the field.
	 *
	 * @return    string    The form field markup.
	 * @since    1.6
	 */
	public function getInput($name, $group = null, $value = null)
	{
		// Attempt to get the form field.
		if ($field = $this->getField($name, $group, $value)) {
			return $field->input;
		}

		return '';
	}

	/**
	 * Method to get a form field markup for the field input.
	 *
	 * @param    string    $name     The name of the form field.
	 * @param    string    $group    The optional dot-separated form group path on which to find the field.
	 *
	 * @return    string    The form field markup.
	 * @since    1.6
	 */
	public function getLabel($name, $group = null)
	{
		// Attempt to get the form field.
		if ($field = $this->getField($name, $group)) {
			return $field->label;
		}

		return '';
	}

	/**
	 * Method to get the form name.
	 *
	 * @return    string    The name of the form.
	 * @since    1.6
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Method to get the value of a field.
	 *
	 * @param    string    $name         The name of the field for which to get the value.
	 * @param    string    $group        The optional dot-separated form group path on which to get the value.
	 * @param    mixed     $default      The optional default value of the field value is empty.
	 *
	 * @return    mixed    The value of the field or the default value if empty.
	 * @since    1.6
	 */
	public function getValue($name, $group = null, $default = null)
	{
		// If a group is set use it.
		if ($group) {
			$return = $this->data->get($group . '.' . $name, $default);
		} else {
			$return = $this->data->get($name, $default);
		}

		return $return;
	}

	/**
	 * Method to load the form description from an XML string or object.
	 *
	 * The replace option works per field.  If a field being loaded already exists in the current
	 * form definition then the behavior or load will vary depending upon the replace flag.  If it
	 * is set to true, then the existing field will be replaced in it's exact location by the new
	 * field being loaded.  If it is false, then the new field being loaded will be ignored and the
	 * method will move on to the next field to load.
	 *
	 * @param    string    $data         The name of an XML string or object.
	 * @param    string    $replace      Flag to toggle whether form fields should be replaced if a field
	 *                                   already exists with the same group/name.
	 * @param    string    $xpath        An optional xpath to search for the fields.
	 *
	 * @return    boolean    True on success, false otherwise.
	 * @since    1.6
	 */
	public function load($data, $replace = true, $xpath = false)
	{
		// If the data to load isn't already an XML element or string return false.
		if ((!$data instanceof GantrySimpleXMLElement) && (!is_string($data))) {
			return false;
		}

		// Attempt to load the XML if a string.
		if (is_string($data)) {
			$data = GantryForm::getXML($data, false);

			// Make sure the XML loaded correctly.
			if (!$data) {
				return false;
			}
		}

		// If we have no XML definition at this point let's make sure we get one.
		if (empty($this->xml)) {
			// If no XPath query is set to search for fields, and we have a <form />, set it and return.
			if (!$xpath && ($data->getName() == 'form')) {
				$this->xml = $data;

				// Synchronize any paths found in the load.
				$this->syncPaths();

				return true;
			} // Create a root element for the form.
			else {
				$this->xml = new GantrySimpleXMLElement('<form></form>');
			}
		}

		// Get the XML elements to load.
		$elements = array();
		if ($xpath) {
			$elements = $data->xpath($xpath);
		} elseif ($data->getName() == 'form') {
			$elements = $data->children();
		}

		// If there is nothing to load return true.
		if (empty($elements)) {
			return true;
		}

		// Load the found form elements.
		foreach ($elements as $element) {
			// Get an array of fields with the correct name.
			$fields = $element->xpath('descendant-or-self::field');
			foreach ($fields as $field) {
				// Get the group names as strings for anscestor fields elements.
				$attrs  = $field->xpath('ancestor::fields[@name]/@name');
				$groups = array_map('strval', $attrs ? $attrs : array());

				// Check to see if the field exists in the current form.
				if ($current = $this->findField((string)$field['name'], implode('.', $groups))) {

					// If set to replace found fields remove it from the current definition.
					if ($replace) {
						$dom = dom_import_simplexml($current);
						$dom->parentNode->removeChild($dom);
					} // Else remove it from the incoming definition so it isn't replaced.'
					else {
						unset($field);
					}
				}
			}

			// Merge the new field data into the existing XML document.
			self::addNode($this->xml, $element);
		}

		// Synchronize any paths found in the load.
		$this->syncPaths();

		return true;
	}

	/**
	 * Method to load the form description from an XML file.
	 *
	 * The reset option works on a group basis. If the XML file references
	 * groups that have already been created they will be replaced with the
	 * fields in the new XML file unless the $reset parameter has been set
	 * to false.
	 *
	 * @param    string    $file         The filesystem path of an XML file.
	 * @param    string    $replace      Flag to toggle whether form fields should be replaced if a field
	 *                                   already exists with the same group/name.
	 * @param    string    $xpath        An optional xpath to search for the fields.
	 *
	 * @return    boolean    True on success, false otherwise.
	 * @since    1.6
	 */
	public function loadFile($file, $reset = true, $xpath = false)
	{
		// Check to see if the path is an absolute path.
		if (!is_file($file)) {

			// Not an absolute path so let's attempt to find one using JPath.
			$file = GantryFormHelper::find(self::addFormPath(), $file . '.xml');

			// If unable to find the file return false.
			if (!$file) {
				return false;
			}
		}
		// Attempt to load the XML file.
		$xml = GantryForm::getXML($file, true);

		return $this->load($xml, $reset, $xpath);
	}

	/**
	 * Method to remove a field from the form definition.
	 *
	 * @param    string    $name         The name of the form field for which remove.
	 * @param    string    $group        The optional dot-separated form group path on which to find the field.
	 *
	 * @return    boolean    True on success.
	 * @since    1.6
	 */
	public function removeField($name, $group = null)
	{
		// Make sure there is a valid GantryForm XML document.
		if (!$this->xml instanceof GantrySimpleXMLElement) {
			// TODO: throw exception.
			return false;
		}

		// Find the form field element from the definition.
		$element = $this->findField($name, $group);

		// If the element exists remove it from the form definition.
		if ($element instanceof GantrySimpleXMLElement) {
			$dom = dom_import_simplexml($element);
			$dom->parentNode->removeChild($dom);
		}

		return true;
	}

	/**
	 * Method to remove a group from the form definition.
	 *
	 * @param    string    $group    The dot-separated form group path for the group to remove.
	 *
	 * @return    boolean    True on success.
	 * @since    1.6
	 */
	public function removeGroup($group)
	{
		// Make sure there is a valid GantryForm XML document.
		if (!$this->xml instanceof GantrySimpleXMLElement) {
			// TODO: throw exception.
			return false;
		}

		// Get the fields elements for a given group.
		$elements = $this->findGroup($group);
		foreach ($elements as & $element) {
			$dom = dom_import_simplexml($element);
			$dom->parentNode->removeChild($dom);
		}

		return true;
	}

	/**
	 * Method to reset the form data store and optionally the form XML definition.
	 *
	 * @param    boolean    $xml    True to also reset the XML form definition.
	 *
	 * @return    boolean    True on success.
	 * @since    1.6
	 */
	public function reset($xml = false)
	{
		unset($this->data);
		$this->data = new GantryRegistry();

		if ($xml) {
			unset($this->xml);
			$this->xml = new GantrySimpleXMLElement('<form></form>');
		}

		return true;
	}

	/**
	 * Method to set a field XML element to the form definition.  If the replace flag is set then
	 * the field will be set whether it already exists or not.  If it isn't set, then the field
	 * will not be replaced if it already exists.
	 *
	 * @param    object     $element      The XML element object representation of the form field.
	 * @param    string     $group        The optional dot-separated form group path on which to set the field.
	 * @param    boolean    $replace      True to replace an existing field if one already exists.
	 *
	 * @return    boolean    True on success.
	 * @since    1.6
	 */
	public function setField(& $element, $group = null, $replace = true)
	{
		// Make sure there is a valid GantryForm XML document.
		if (!$this->xml instanceof GantrySimpleXMLElement) {
			// TODO: throw exception.
			return false;
		}

		// Make sure the element to set is valid.
		if (!$element instanceof GantrySimpleXMLElement) {
			// TODO: throw exception.
			return false;
		}

		// Find the form field element from the definition.
		$old =  $this->findField((string)$element['name'], $group);

		// If an existing field is found and replace flag is false do nothing and return true.
		if (!$replace && !empty($old)) {
			return true;
		}

		// If an existing field is found and replace flag is true remove the old field.
		if ($replace && !empty($old) && ($old instanceof GantrySimpleXMLElement)) {
			$dom = dom_import_simplexml($old);
			$dom->parentNode->removeChild($dom);
		}


		// If no existing field is found find a group element and add the field as a child of it.
		if ($group) {

			// Get the fields elements for a given group.
			$fields =  $this->findGroup($group);

			// If an appropriate fields element was found for the group, add the element.
			if (isset($fields[0]) && ($fields[0] instanceof GantrySimpleXMLElement)) {
				self::addNode($fields[0], $element);
			}
		} else {
			// Set the new field to the form.
			self::addNode($this->xml, $element);
		}

		// Synchronize any paths found in the load.
		$this->syncPaths();

		return true;
	}

	/**
	 * Method to set an attribute value for a field XML element.
	 *
	 * @param    string    $name         The name of the form field for which to set the attribute value.
	 * @param    string    $attribute    The name of the attribute for which to set a value.
	 * @param    mixed     $value        The value to set for the attribute.
	 * @param    string    $group        The optional dot-separated form group path on which to find the field.
	 *
	 * @return    boolean    True on success.
	 * @since    1.6
	 */
	public function setFieldAttribute($name, $attribute, $value, $group = null)
	{
		// Make sure there is a valid GantryForm XML document.
		if (!$this->xml instanceof GantrySimpleXMLElement) {
			// TODO: throw exception.
			return false;
		}

		// Find the form field element from the definition.
		$element =  $this->findField($name, $group);

		// If the element doesn't exist return false.
		if (!$element instanceof GantrySimpleXMLElement) {
			return false;
		} // Otherwise set the attribute and return true.
		else {
			$element[$attribute] = $value;

			// Synchronize any paths found in the load.
			$this->syncPaths();

			return true;
		}
	}

	/**
	 * Method to set some field XML elements to the form definition.  If the replace flag is set then
	 * the fields will be set whether they already exists or not.  If it isn't set, then the fields
	 * will not be replaced if they already exist.
	 *
	 * @param    object     $elements     The array of XML element object representations of the form fields.
	 * @param    string     $group        The optional dot-separated form group path on which to set the fields.
	 * @param    boolean    $replace      True to replace existing fields if they already exist.
	 *
	 * @return    boolean    True on success.
	 * @since    1.6
	 */
	public function setFields(& $elements, $group = null, $replace = true)
	{
		// Make sure there is a valid GantryForm XML document.
		if (!$this->xml instanceof GantrySimpleXMLElement) {
			// TODO: throw exception.
			return false;
		}

		// Make sure the elements to set are valid.
		foreach ($elements as $element) {
			if (!($element instanceof GantrySimpleXMLElement)) {
				// TODO: throw exception.
				return false;
			}
		}

		// Set the fields.
		$return = true;
		foreach ($elements as $element) {
			if (!$this->setField($element, $group, $replace)) {
				$return = false;
			}
		}

		// Synchronize any paths found in the load.
		$this->syncPaths();

		return $return;
	}

	/**
	 * Method to set the value of a field. If the field does not exist in the form then the method
	 * will return false.
	 *
	 * @param    string    $name     The name of the field for which to set the value.
	 * @param    string    $group    The optional dot-separated form group path on which to find the field.
	 * @param    mixed     $value    The value to set for the field.
	 *
	 * @return    boolean    True on success.
	 * @since    1.6
	 */
	public function setValue($name, $group = null, $value = null)
	{
		// If the field does not exist return false.
		if (!$this->findField($name, $group)) {
			return false;
		}

		// If a group is set use it.
		if ($group) {
			$this->data->set($group . '.' . $name, $value);
		} else {
			$this->data->set($name, $value);
		}

		return true;
	}


	/**
	 * Method to apply an input filter to a value based on field data.
	 *
	 * @param    string    $element      The XML element object representation of the form field.
	 * @param    mixed     $value        The value to filter for the field.
	 *
	 * @return    mixed    The filtered value.
	 * @since    1.6
	 */
	protected function filterField($element, $value)
	{
		gantry_import('core.utilities.gantryfilterinput');


		// Make sure there is a valid GantrySimpleXMLElement.
		if (!($element instanceof GantrySimpleXMLElement)) {
			return false;
		}

		// Get the field filter type.
		$filter = (string)$element['filter'];

		// Process the input value based on the filter.
		$return = null;

		switch (strtoupper($filter)) {
			// Access Control Rules.
			case 'RULES':
				$return = array();
				foreach ((array)$value as $action => $ids) {
					// Build the rules array.
					$return[$action] = array();
					foreach ($ids as $id => $p) {
						if ($p !== '') {
							$return[$action][$id] = ($p == '1' || $p == 'true') ? true : false;
						}
					}
				}
				break;

			// Do nothing, thus leaving the return value as null.
			case 'UNSET':
				break;

			// No Filter.
			case 'RAW':
				$return = $value;
				break;

			// Filter the input as an array of integers.
			case 'INT_ARRAY':
				// Make sure the input is an array.
				if (is_object($value)) {
					$value = get_object_vars($value);
				}
				$value = is_array($value) ? $value : array($value);

				GantryArrayHelper::toInteger($value);
				$return = $value;
				break;

			// Filter safe HTML.
			case 'SAFEHTML':
				$return = GantryFilterInput::getInstance(null, null, 1, 1)->clean($value, 'string');
				break;

			// Convert a date to UTC based on the server timezone offset.
			case 'SERVER_UTC':
				if (intval($value) > 0) {
					// Get the server timezone setting.
					$offset = JFactory::getConfig()->get('offset');

					// Return a MySQL formatted datetime string in UTC.
					$return = JFactory::getDate($value, $offset)->toMySQL();
				} else {
					$return = '';
				}
				break;

			// Convert a date to UTC based on the user timezone offset.
			case 'USER_UTC':
				if (intval($value) > 0) {
					// Get the user timezone setting defaulting to the server timezone setting.
					$offset = JFactory::getUser()->getParam('timezone', JFactory::getConfig()->get('offset'));

					// Return a MySQL formatted datetime string in UTC.
					$return = JFactory::getDate($value, $offset)->toMySQL();
				} else {
					$return = '';
				}
				break;

			default:
				// Check for a callback filter.
				if (strpos($filter, '::') !== false && is_callable(explode('::', $filter))) {
					$return = call_user_func(explode('::', $filter), $value);
				} // Filter using a callback function if specified.
				else if (function_exists($filter)) {
					$return = call_user_func($filter, $value);
				} // Filter using JFilterInput. All HTML code is filtered by default.
				else {
					$return = GantryFilterInput::getInstance()->clean($value, $filter);
				}
				break;
		}

		return $return;
	}

	/**
	 * Method to get a form field represented as an XML element object.
	 *
	 * @param    string    $name     The name of the form field.
	 * @param    string    $group    The optional dot-separated form group path on which to find the field.
	 *
	 * @return    mixed    The XML element object for the field or boolean false on error.
	 * @since    1.6
	 */
	protected function findField($name, $group = null)
	{
		// Initialise variables.
		$element = false;
		$fields  = array();

		// Make sure there is a valid GantryForm XML document.
		if (!$this->xml instanceof GantrySimpleXMLElement) {
			return false;
		}

		// Let's get the appropriate field element based on the method arguments.
		if ($group) {

			// Get the fields elements for a given group.
			$elements =  $this->findGroup($group);

			// Get all of the field elements with the correct name for the fields elements.
			foreach ($elements as $element) {
				// If there are matching field elements add them to the fields array.
				if ($tmp = $element->xpath('descendant::field[@name="' . $name . '"]')) {
					$fields = array_merge($fields, $tmp);
				}
			}

			// Make sure something was found.
			if (!$fields) {
				return false;
			}

			// Use the first correct match in the given group.
			$groupNames = explode('.', $group);
			foreach ($fields as & $field) {
				// Get the group names as strings for anscestor fields elements.
				$attrs = $field->xpath('ancestor::fields[@name]/@name');
				$names = array_map('strval', $attrs ? $attrs : array());

				// If the field is in the exact group use it and break out of the loop.
				if ($names == (array)$groupNames) {
					$element = & $field;
					break;
				}
			}
		} else {
			// Get an array of fields with the correct name.
			$fields = $this->xml->xpath('//field[@name="' . $name . '"]');

			// Make sure something was found.
			if (!$fields) {
				return false;
			}

			// Search through the fields for the right one.
			foreach ($fields as & $field) {
				// If we find an ancestor fields element with a group name then it isn't what we want.
				if ($field->xpath('ancestor::fields[@name]')) {
					continue;
				} // Found it!
				else {
					$element = & $field;
					break;
				}
			}
		}

		return $element;
	}

	/**
	 * Method to get an array of <field /> elements from the form XML document which are
	 * in a specified fieldset by name.
	 *
	 * @param    string    $name    The name of the fieldset.
	 *
	 * @return    mixed    Boolean false on error or array of GantrySimpleXMLElement objects.
	 * @since    1.6
	 */
	protected function & findFieldsByFieldset($name)
	{
		// Initialise variables.
		$false = false;

		// Make sure there is a valid GantryForm XML document.
		if (!$this->xml instanceof GantrySimpleXMLElement) {
			return $false;
		}

		/*
		 * Get an array of <field /> elements that are underneath a <fieldset /> element
		 * with the appropriate name attribute, and also any <field /> elements with
		 * the appropriate fieldset attribute.
		 */
		$fields = $this->xml->xpath('//fieldset[@name="' . $name . '"]//field | //field[@fieldset="' . $name . '"]');

		return $fields;
	}

	/**
	 * Method to get an array of <field /> elements from the form XML document which are
	 * in a control group by name.
	 *
	 * @param    mixed      $group     The optional dot-separated form group path on which to find the fields.
	 *                                 Null will return all fields. False will return fields not in a group.
	 * @param    boolean    $nested    True to also include fields in nested groups that are inside of the
	 *                                 group for which to find fields.
	 *
	 * @return    mixed    Boolean false on error or array of GantrySimpleXMLElement objects.
	 * @since    1.6
	 */
	protected function & findFieldsByGroup($group = null, $nested = false)
	{
		// Initialise variables.
		$false  = false;
		$fields = array();

		// Make sure there is a valid GantryForm XML document.
		if (!$this->xml instanceof GantrySimpleXMLElement) {
			return $false;
		}

		// Get only fields in a specific group?
		if ($group) {

			// Get the fields elements for a given group.
			$elements = $this->findGroup($group);

			// Get all of the field elements for the fields elements.
			foreach ($elements as $element) {

				// If there are field elements add them to the return result.
				if ($tmp = $element->xpath('descendant::field')) {

					// If we also want fields in nested groups then just merge the arrays.
					if ($nested) {
						$fields = array_merge($fields, $tmp);
					} // If we want to exclude nested groups then we need to check each field.
					else {
						$groupNames = explode('.', $group);
						foreach ($tmp as $field) {
							// Get the names of the groups that the field is in.
							$attrs = $field->xpath('ancestor::fields[@name]/@name');
							$names = array_map('strval', $attrs ? $attrs : array());

							// If the field is in the specific group then add it to the return list.
							if ($names == (array)$groupNames) {
								$fields = array_merge($fields, array($field));
							}
						}
					}
				}
			}
		} else if ($group === false) {
			// Get only field elements not in a group.
			$fields = $this->xml->xpath('descendant::fields[not(@name)]/field | descendant::fields[not(@name)]/fieldset/field ');
		} else {
			// Get an array of all the <field /> elements.
			$fields = $this->xml->xpath('//field');
		}

		return $fields;
	}

	/**
	 * Method to get a form field group represented as an XML element object.
	 *
	 * @param    string    $group    The dot-separated form group path on which to find the group.
	 *
	 * @return    mixed    An array of XML element objects for the group or boolean false on error.
	 * @since    1.6
	 */
	protected function &findGroup($group)
	{
		// Initialise variables.
		$false  = false;
		$groups = array();
		$tmp    = array();

		// Make sure there is a valid GantryForm XML document.
		if (!$this->xml instanceof GantrySimpleXMLElement) {
			return $false;
		}

		// Make sure there is actually a group to find.
		$group = explode('.', $group);
		if (!empty($group)) {

			// Get any fields elements with the correct group name.
			$elements = $this->xml->xpath('//fields[@name="' . (string)$group[0] . '"]');

			// Check to make sure that there are no parent groups for each element.
			foreach ($elements as $element) {
				if (!$element->xpath('ancestor::fields[@name]')) {
					$tmp[] = $element;
				}
			}

			// Iterate through the nested groups to find any matching form field groups.
			for ($i = 1, $n = count($group); $i < $n; $i++) {
				// Initialise some loop variables.
				$validNames = array_slice($group, 0, $i + 1);
				$current    = $tmp;
				$tmp        = array();

				// Check to make sure that there are no parent groups for each element.
				foreach ($current as $element) {
					// Get any fields elements with the correct group name.
					$children = $element->xpath('descendant::fields[@name="' . (string)$group[$i] . '"]');

					// For the found fields elements validate that they are in the correct groups.
					foreach ($children as $fields) {
						// Get the group names as strings for anscestor fields elements.
						$attrs = $fields->xpath('ancestor-or-self::fields[@name]/@name');
						$names = array_map('strval', $attrs ? $attrs : array());

						// If the group names for the fields element match the valid names at this
						// level add the fields element.
						if ($validNames == $names) {
							$tmp[] = $fields;
						}
					}
				}
			}

			// Only include valid XML objects.
			foreach ($tmp as $element) {
				if ($element instanceof GantrySimpleXMLElement) {
					$groups[] = $element;
				}
			}
		}

		return $groups;
	}

	/**
	 * Method to load, setup and return a GantryFormField object based on field data.
	 *
	 * @param    string    $element      The XML element object representation of the form field.
	 * @param    string    $group        The optional dot-separated form group path on which to find the field.
	 * @param    mixed     $value        The optional value to use as the default for the field.
	 *
	 * @return    mixed    The GantryFormField object for the field or boolean false on error.
	 * @since    1.6
	 */
	protected function loadField($element, $group = null, $value = null)
	{
		// Make sure there is a valid GantrySimpleXMLElement.
		if (!$element instanceof GantrySimpleXMLElement) {
			return false;
		}

		// Get the field type.
		$type = $element['type'] ? (string)$element['type'] : 'text';

		// Load the GantryFormField object for the field.
		$field = $this->loadFieldType($type);

		// If the object could not be loaded, get a text field object.
		if ($field === false) {
			$field = $this->loadFieldType('text');
		}

		// Get the value for the form field if not set. Default to the 'default' attribute for the field.
		if ($value === null) {
			$value = $this->getValue((string)$element['name'], $group, (string)$element['default']);
		}

		// Setup the GantryFormField object.
		$field->setForm($this);

		if ($field->setup($element, $value, $group)) {
			return $field;
		} else {
			return false;
		}
	}

	/**
	 * Method to load a form field object given a type.
	 *
	 * @param    string     $type    The field type.
	 * @param    boolean    $new     Flag to toggle whether we should get a new instance of the object.
	 *
	 * @return    mixed    GantryFormField object on success, false otherwise.
	 * @since    1.6
	 */
	protected function loadFieldType($type, $new = true)
	{
		return GantryFormHelper::loadFieldType($type, $new);
	}

	/**
	 * Method to synchronize any field, form or rule paths contained in the XML document.
	 *
	 * @return    boolean    True on success.
	 * @since    1.6
	 */
	protected function syncPaths()
	{
		// Make sure there is a valid GantryForm XML document.
		if (!$this->xml instanceof GantrySimpleXMLElement) {
			return false;
		}

		// Get any addfieldpath attributes from the form definition.
		$paths = $this->xml->xpath('//*[@addfieldpath]/@addfieldpath');
		$paths = array_map('strval', $paths ? $paths : array());

		// Add the field paths.
		foreach ($paths as $path) {
			$path = JPATH_ROOT . '/' . ltrim($path, '/\\');
			self::addFieldPath($path);
		}

		// Get any addformpath attributes from the form definition.
		$paths = $this->xml->xpath('//*[@addformpath]/@addformpath');
		$paths = array_map('strval', $paths ? $paths : array());

		// Add the form paths.
		foreach ($paths as $path) {
			$path = JPATH_ROOT . '/' . ltrim($path, '/\\');
			self::addFormPath($path);
		}

		return true;
	}


	/**
	 * Proxy for {@link GantryFormHelper::addFieldPath()}.
	 *
	 * @param    mixed    $new    A path or array of paths to add.
	 *
	 * @return    array    The list of paths that have been added.
	 * @since    1.6
	 */
	public static function addFieldPath($new = null)
	{
		return GantryFormHelper::addFieldPath($new);
	}

	/**
	 * Proxy for {@link GantryFormHelper::addFormPath()}.
	 *
	 * @param    mixed    $new    A path or array of paths to add.
	 *
	 * @return    array    The list of paths that have been added.
	 * @since    1.6
	 */
	public static function addFormPath($new = null)
	{
		return GantryFormHelper::addFormPath($new);
	}

	/**
	 * Method to get an instance of a form.
	 *
	 * @param    string    $name         The name of the form.
	 * @param    string    $data         The name of an XML file or string to load as the form definition.
	 * @param    array     $options      An array of form options.
	 * @param    string    $replace      Flag to toggle whether form fields should be replaced if a field
	 *                                   already exists with the same group/name.
	 * @param    string    $xpath        An optional xpath to search for the fields.
	 *
	 * @return    object    GantryForm instance.
	 * @throws    Exception if an error occurs.
	 * @since    1.6
	 */
	public static function getInstance(&$control, $name, $data = null, $options = array(), $replace = true, $xpath = false)
	{
		// Reference to array with form instances
		$forms = &self::$forms;

		// Only instantiate the form if it does not already exist.
		if (!isset($forms[$name])) {

			$data = trim($data);

			if (empty($data)) {
				throw new Exception(JText::_('JLIB_FORM_ERROR_NO_DATA'));
			}

			// Instantiate the form.
			$forms[$name] = new GantryForm($control, $name, $options);

			// Load the data.
			if (substr(trim($data), 0, 1) == '<') {
				if ($forms[$name]->load($data, $replace, $xpath) == false) {
					throw new Exception(JText::_('JLIB_FORM_ERROR_XML_FILE_DID_NOT_LOAD'));

					return false;
				}
			} else {
				if ($forms[$name]->loadFile($data, $replace, $xpath) == false) {
					throw new Exception(JText::_('JLIB_FORM_ERROR_XML_FILE_DID_NOT_LOAD'));

					return false;
				}
			}
		}

		return $forms[$name];
	}

	/**
	 * Adds a new child SimpleXMLElement node to the source.
	 *
	 * @param    SimpleXMLElement    The source element on which to append.
	 * @param    SimpleXMLElement    The new element to append.
	 */
	protected static function addNode(SimpleXMLElement $source, SimpleXMLElement $new)
	{
		// Add the new child node.
		$node = $source->addChild($new->getName(), trim($new));

		// Add the attributes of the child node.
		foreach ($new->attributes() as $name => $value) {
			$node->addAttribute($name, $value);
		}

		// Add any children of the new node.
		foreach ($new->children() as $child) {
			self::addNode($node, $child);
		}
	}

	protected static function mergeNode(SimpleXMLElement $source, SimpleXMLElement $new)
	{
		// Update the attributes of the child node.
		foreach ($new->attributes() as $name => $value) {
			if (isset($source[$name])) {
				$source[$name] = (string)$value;
			} else {
				$source->addAttribute($name, $value);
			}
		}

		// What to do with child elements?
	}

	/**
	 * Merges new elements into a source <fields> element.
	 *
	 * @param    SimpleXMLElement    The source element.
	 * @param    SimpleXMLElement    The new element to merge.
	 *
	 * @return    void
	 * @since    1.6
	 */
	protected static function mergeNodes(SimpleXMLElement $source, SimpleXMLElement $new)
	{
		// The assumption is that the inputs are at the same relative level.
		// So we just have to scan the children and deal with them.

		// Update the attributes of the child node.
		foreach ($new->attributes() as $name => $value) {
			if (isset($source[$name])) {
				$source[$name] = (string)$value;
			} else {
				$source->addAttribute($name, $value);
			}
		}

		foreach ($new->children() as $child) {
			$type = $child->getName();
			$name = $child['name'];

			// Does this node exist?
			$fields = $source->xpath($type . '[@name="' . $name . '"]');

			if (empty($fields)) {
				// This node does not exist, so add it.
				self::addNode($source, $child);
			} else {
				// This node does exist.
				switch ($type) {
					case 'field':
						self::mergeNode($fields[0], $child);
						break;

					default:
						self::mergeNodes($fields[0], $child);
						break;
				}
			}
		}
	}

	public static function getXML($data, $isFile = true)
	{


		// Disable libxml errors and allow to fetch error information as needed
		//libxml_use_internal_errors(true);

		if ($isFile) {
			// Try to load the xml file
			$xml = simplexml_load_file($data, 'GantrySimpleXMLElement');
		} else {
			// Try to load the xml string
			$xml = simplexml_load_string($data, 'GantrySimpleXMLElement');
		}

		if (empty($xml)) {
			//TODO handle errors
			// There was an error
//			JError::raiseWarning(100, JText::_('JLIB_UTIL_ERROR_XML_LOAD'));
//
//			if ($isFile) {
//				JError::raiseWarning(100, $data);
//			}
//
//			foreach (libxml_get_errors() as $error) {
//				JError::raiseWarning(100, 'XML: '.$error->message);
//			}
		}

		return $xml;
	}


	/**
	 * Method to get an array of GantryFormField objects in a given fieldset by name.  If no name is
	 * given then all fields are returned.
	 *
	 * @param    string    $set    The optional name of the fieldset.
	 *
	 * @return    array    The array of GantryFormField objects in the fieldset.
	 * @since    1.6
	 */
	//TODO Rename this to something better
	public function getFullFieldset($set = null, $xml = null)
	{
		// Initialize variables.
		$fields = array();

		// Get all of the field elements in the fieldset.
		if ($set) {
			$elements = $this->findFullFieldsByFieldset($set, $xml);
		} // Get all fields.
		else {
			$elements = $this->findFieldsByGroup();
		}

		// If no field elements were found return empty.
		if (empty($elements)) {
			return $fields;
		}

		// Build the result array from the found field elements.
		foreach ($elements as $element) {

			// Get the field groups for the element.
			$attrs  = $element->xpath('ancestor::fields[@name]/@name');
			$groups = array_map('strval', $attrs ? $attrs : array());
			$group  = implode('.', $groups);
			$type   = $element->getName();

			if ($type == 'field') {
				// If the field is successfully loaded add it to the result array.
				if ($field = $this->loadField($element, $group)) {
					$fields[$field->id] = $field;
				}
			} else if ($type == 'fields') {
				if ($field = $this->loadGroup($element, $group)) {
					$fields[$field->id] = $field;
				}
			}
		}

		return $fields;
	}

	/**
	 * Method to get an array of <field /> elements from the form XML document which are
	 * in a specified fieldset by name.
	 *
	 * @param    string    $name    The name of the fieldset.
	 *
	 * @return    mixed    Boolean false on error or array of GantrySimpleXMLElement objects.
	 * @since    1.6
	 */
	//TODO Rename this to something better
	protected function & findFullFieldsByFieldset($name, $xml = null)
	{
		// Initialize variables.
		$false = false;

		if (null == $xml) {
			$xml =& $this->xml;
		}

		// Make sure there is a valid GantryForm XML document.
		if (!$xml instanceof GantrySimpleXMLElement) {
			return $false;
		}

		/*
		 * Get an array of <field /> elements that are underneath a <fieldset /> element
		 * with the appropriate name attribute, and also any <field /> elements with
		 * the appropriate fieldset attribute.
		 */
		$fields = $xml->xpath('//fieldset[@name="' . $name . '"]/field | //fieldset[@name="' . $name . '"]/fields');
		return $fields;
	}


	public function  & getSubFields(&$xml, $groups_xpath = 'ancestor::fields[@name]/@name')
	{
		// Initialize variables.
		$fields = array();


		$elements = $this->findSubFields($xml);

		// If no field elements were found return empty.
		if (empty($elements)) {
			return $fields;
		}

		// Build the result array from the found field elements.
		foreach ($elements as $element) {

			// Get the field groups for the element.
			$attrs  = $element->xpath($groups_xpath);
			$groups = array_map('strval', $attrs ? $attrs : array());

			$group = implode('.', $groups);
			$type  = $element->getName();

			if ($type == 'field') {
				// If the field is successfully loaded add it to the result array.
				if ($field = $this->loadField($element, $group)) {
					$fields[$field->id] = $field;
				}
			} else if ($type == 'fields') {
				if ($field = $this->loadGroup($element, $group)) {
					$fields[$field->id] = $field;
				}
			}
		}
		return $fields;
	}

	protected function &findSubFields(&$xml)
	{
		$fields = $xml->xpath('field | fields');
		return $fields;
	}

	/**
	 * Method to load, setup and return a GantryFormField object based on field data.
	 *
	 * @param    string    $element      The XML element object representation of the form field.
	 * @param    string    $group        The optional dot-separated form group path on which to find the field.
	 * @param    mixed     $value        The optional value to use as the default for the field.
	 *
	 * @return    mixed    The GantryFormField object for the field or boolean false on error.
	 * @since    1.6
	 */
	protected function loadGroup($element, $group = null, $value = null)
	{
		// Make sure there is a valid GantrySimpleXMLElement.
		if (!$element instanceof GantrySimpleXMLElement) {
			return false;
		}

		// Get the field type.
		$type = $element['type'] ? (string)$element['type'] : 'text';

		// Load the GantryFormField object for the field.
		$field = $this->loadGroupType($type);

		// If the object could not be loaded, get a text field object.
		if ($field === false) {
			$field = $this->loadGroupType('');
		}

		// Get the value for the form field if not set. Default to the 'default' attribute for the field.
		if ($value === null) {
			$value = $this->getValue((string)$element['name'], $group, (string)$element['default']);
		}

		// Setup the GantryFormField object.
		$field->setForm($this);

		if ($field->setup($element, $value, $group)) {
			return $field;
		} else {
			return false;
		}
	}

	/**
	 * Method to load a form field object given a type.
	 *
	 * @param    string     $type    The field type.
	 * @param    boolean    $new     Flag to toggle whether we should get a new instance of the object.
	 *
	 * @return    mixed    GantryFormField object on success, false otherwise.
	 * @since    1.6
	 */
	protected function loadGroupType($type, $new = true)
	{
		return GantryFormHelper::loadGroupType($type, $new);
	}


	/**
	 * Method to add a path to the list of field include paths.
	 *
	 * @param    mixed    $new    A path or array of paths to add.
	 *
	 * @return    array    The list of paths that have been added.
	 * @since    1.6
	 */
	public static function addGroupPath($new = null)
	{
		return GantryFormHelper::addGroupPath($new);
	}

	public function initialize()
	{
		$fields = $this->getFullFieldset();
		foreach ($fields as $field) {
			$fieldTypes[] = get_class($field);
		}
		$fieldTypes = array_unique($fieldTypes);
		foreach ($fieldTypes as $fieldType) {
			call_user_func(array($fieldType, 'initialize'));
		}
	}

	public function finalize()
	{
		$fields = $this->getFullFieldset();
		foreach ($fields as $field) {
			$fieldTypes[] = get_class($field);
		}
		$fieldTypes = array_unique($fieldTypes);
		foreach ($fieldTypes as $fieldType) {
			call_user_func(array($fieldType, 'finalize'));
		}
	}
}
