<?php
/**
 * @package         SCLogin
 * @copyright (c)   2009-2021 by SourceCoast - All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version         Release v9.0.215
 * @build-date      2022/09/06
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('groupedlist');

// Import the com_menus helper.
require_once realpath(JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');
class JFormFieldRegistrationLink extends JFormFieldGroupedList
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.6
     */
    public $type = 'RegistrationLink';

    /**
     * The menu type.
     *
     * @var    string
     * @since  3.2
     */
    protected $menuType;

    /**
     * The language.
     *
     * @var    array
     * @since  3.2
     */
    protected $language;

    /**
     * The published status.
     *
     * @var    array
     * @since  3.2
     */
    protected $published;

    /**
     * The disabled status.
     *
     * @var    array
     * @since  3.2
     */
    protected $disable;

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param   string  $name  The property name for which to the the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since   3.2
     */
    public function __get($name)
    {
        switch ($name)
        {
            case 'menuType':
            case 'language':
            case 'published':
            case 'disable':
                return $this->$name;
        }

        return parent::__get($name);
    }

    /**
     * Method to set certain otherwise inaccessible properties of the form field object.
     *
     * @param   string  $name   The property name for which to the the value.
     * @param   mixed   $value  The value of the property.
     *
     * @return  void
     *
     * @since   3.2
     */
    public function __set($name, $value)
    {
        switch ($name)
        {
            case 'menuType':
                $this->menuType = (string) $value;
                break;

            case 'language':
            case 'published':
            case 'disable':
                $value = (string) $value;
                $this->$name = $value ? explode(',', $value) : array();
                break;

            default:
                parent::__set($name, $value);
        }
    }

    /**
     * Method to attach a Form object to the field.
     *
     * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
     * @param   mixed             $value    The form field value to validate.
     * @param   string            $group    The field name group control value. This acts as as an array container for the field.
     *                                      For example if the field has name="foo" and the group value is set to "bar" then the
     *                                      full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @see     FormField::setup()
     * @since   3.2
     */
    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        $result = parent::setup($element, $value, $group);

        if ($result == true)
        {
            $this->menuType  = (string) $this->element['menu_type'];
            $this->published = $this->element['published'] ? explode(',', (string) $this->element['published']) : array();
            $this->disable   = $this->element['disable'] ? explode(',', (string) $this->element['disable']) : array();
            $this->language  = $this->element['language'] ? explode(',', (string) $this->element['language']) : array();
        }

        return $result;
    }

    /**
     * Method to get the field option groups.
     *
     * @return  array  The field option objects as a nested array in groups.
     *
     * @since   1.6
     */
    protected function getGroups()
    {
        $groups = array();

        $menuType = $this->menuType;

        // Get the menu items.
        $items = MenusHelper::getMenuLinks($menuType, 0, 0, $this->published, $this->language);

        // Build group for a specific menu type.
        if ($menuType)
        {
            // Initialize the group.
            $groups[$menuType] = array();

            // Build the options array.
            foreach ($items as $link)
            {
                $groups[$menuType][] = HTMLHelper::_('select.option', $link->value, $link->text, 'value', 'text', in_array($link->type, $this->disable));
            }
        }
        // Build groups for all menu types.
        else
        {
            // Build the groups arrays.
            foreach ($items as $menu)
            {
                // Initialize the group.
                $groups[$menu->menutype] = array();

                // Build the options array.
                foreach ($menu->links as $link)
                {
                    $groups[$menu->menutype][] = HTMLHelper::_(
                        'select.option', $link->value, $link->text, 'value', 'text',
                        in_array($link->type, $this->disable)
                    );
                }
            }
        }

        // Merge any additional groups in the XML definition.
        $groups = array_merge(parent::getGroups(), $groups);

        return $groups;
    }

    protected function getInput()
    {
        $register_type = $this->form->getValue('register_type', 'params', null);
        $notice_display = $register_type == "custom" ? "none" : "block";
        $input_display = $register_type == "custom" ? "block" : "none";
        $html = '<div id="reglinknotice" style="display:'.$notice_display.'; clear:both;">'.Text::_("MOD_SCLOGIN_LOGIN_CUSTOM_REG_LINK_NOTICE").'</div>';

        return $html.'<div id="registrationlink" style="display:'.$input_display.'">'.parent::getInput().'</div>';
    }
}
