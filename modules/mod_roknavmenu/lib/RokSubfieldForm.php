<?php
/**
 * @version   $Id: RokSubfieldForm.php 10623 2013-05-23 23:37:15Z steph $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
if(!class_exists('RokSubfieldForm')){
class RokSubfieldForm extends JForm
{
    static $instances = array();

    protected $original_params;

    public static function getInstanceFromForm(JForm $form)
    {
        if (!array_key_exists($form->getName(), self::$instances))
        {
            self::$instances[$form->getName()] = new RokSubfieldForm($form);
        }
        self::$instances[$form->getName()]->updateDataParams();
        return self::$instances[$form->getName()];
    }

    public function __construct(JForm &$form)
    {
        $form_vars = get_object_vars($form);
        foreach ($form_vars as $form_var_name => $form_var_value)
        {
            $this->$form_var_name = $form_var_value;
        }
    }

    public function makeSubfieldsVisable(){
        $subs = $this->xml->xpath('//subfields/fieldset/field');
        foreach($subs as $sub){
            $field =& $this->xml->config[0]->fields->fieldset[0]->addChild('field');
            foreach($sub->attributes() as $aname=>$aval){
                $field->addAttribute($aname,$aval);
            }
        }

    }

    /**
	 * Method to get an array of <field /> elements from the form XML document which are
	 * in a specified fieldset by name.
	 *
	 * @param	string	$name	The name of the fieldset.
	 *
	 * @return	mixed	Boolean false on error or array of JXMLElement objects.
	 * @since	1.6
	 */
	protected function & findFieldsBySubFieldset($subtype, $name)
	{
		// Initialise variables.
		$false = false;

        // Make sure there is a valid JForm XML document.
        $version = new JVersion();
		if (!($this->xml instanceof SimpleXMLElement) && (version_compare($version->getShortVersion(), '3.0', '>='))) {
            return false;
		} elseif (!($this->xml instanceof JXMLElement) && (version_compare($version->getShortVersion(), '3.0', '<'))) {
            return false;
        }

		/*
		 * Get an array of <field /> elements that are underneath a <fieldset /> element
		 * with the appropriate name attribute, and also any <field /> elements with
		 * the appropriate fieldset attribute.
		 */
		$fields = $this->xml->xpath('//subfields[@name="'.$subtype.'"]/fieldset[@name="'.$name.'"]/field');

		return $fields;
	}


    /**
	 * Method to get an array of JFormField objects in a given fieldset by name.  If no name is
	 * given then all fields are returned.
	 *
	 * @param	string	$set	The optional name of the fieldset.
	 *
	 * @return	array	The array of JFormField objects in the fieldset.
	 * @since	1.6
	 */
	public function getSubFieldset($subtype, $set, $group="params")
	{
		// Initialise variables.
		$fields = array();

    	$elements = $this->findFieldsBySubFieldset($subtype, $set);

		// If no field elements were found return empty.
		if (empty($elements)) {
			return $fields;
		}

		// Build the result array from the found field elements.
		foreach ($elements as $element)
		{
			// If the field is successfully loaded add it to the result array.
			if ($field = $this->loadField($element, $group)) {
				$fields[$field->id] = $field;
			}
		}

		return $fields;
	}

    public function setOriginalParams($original_params)
    {
        $this->original_params = $original_params;
    }

    public function getOriginalParams()
    {
        return $this->original_params;
    }

    public function updateDataParams()
    {
        if (isset($this->original_params) && (is_array($this->original_params))){
            foreach($this->original_params as $param_name => $param_value){
                $this->data->set('params.'.$param_name, $param_value);
            }
        }
    }

    /**
	 * Method to get an array of fieldset objects optionally filtered over a given field group.
	 *
	 * @param	string	$group	The dot-separated form group path on which to filter the fieldsets.
	 *
	 * @return	array	The array of fieldset objects.
	 * @since	1.6
	 */
	public function getSubFieldsets($subfield_type)
	{
		// Initialise variables.
		$fieldsets = array();
		$sets = array();

        // Make sure there is a valid JForm XML document.
        $version = new JVersion();
		if (!($this->xml instanceof SimpleXMLElement) && (version_compare($version->getShortVersion(), '3.0', '>='))) {
            return $fieldsets;
		} elseif (!($this->xml instanceof JXMLElement) && (version_compare($version->getShortVersion(), '3.0', '<'))) {
            return $fieldsets;
        }

        // Get an array of <fieldset /> elements and fieldset attributes.
        $sets = $this->xml->xpath('//subfields[@name="'.$subfield_type.'"]/fieldset');

		// If no fieldsets are found return empty.
		if (empty($sets)) {
			return $fieldsets;
		}

		// Process each found fieldset.
		foreach ($sets as $set)
		{
            // Only create it if it doesn't already exist.
            if (empty($fieldsets[(string) $set['name']])) {

                // Build the fieldset object.
                $fieldset = (object) array('name' => '', 'label' => '', 'description' => '');
                foreach ($set->attributes() as $name => $value)
                {
                    $fieldset->$name = (string) $value;
                }

                // Add the fieldset object to the list.
                $fieldsets[$fieldset->name] = $fieldset;
            }

		}

		return $fieldsets;
	}
}
}
