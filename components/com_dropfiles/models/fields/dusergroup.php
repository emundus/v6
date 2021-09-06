<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barr?re (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('JPATH_PLATFORM') || die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Helper\UserGroupsHelper;

FormHelper::loadFieldClass('list');

/**
 * Field to load a dropdown list of available user groups
 */
class JFormFieldDUserGroup extends JFormFieldList
{
    /**
     * The form field type.
     *
     * @var string
     */
    protected $type = 'UserGroupList';

    /**
     * Cached array of the category items.
     *
     * @var array
     */
    protected static $options = array();

    /**
     * Method to attach a JForm object to the field.
     *
     * @param \SimpleXMLElement $element The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param mixed             $value   The form field value to validate.
     * @param string            $group   The field name group control value. This acts as an array container for the field.
     *                                     For example if the field has name="foo" and the group value is set to "bar" then the
     *                                     full field name would end up being "bar[foo]".
     *
     * @return boolean  True on success.
     *
     * @since 5.5
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        if (is_string($value) && strpos($value, ',') !== false) {
            $value = explode(',', $value);
        }

        return parent::setup($element, $value, $group);
    }

    /**
     * Method to get the options to populate list
     *
     * @return array  The field option objects.
     *
     * @since 5.5
     */
    protected function getOptions()
    {
        $options        = parent::getOptions();
        $checkSuperUser = (int) $this->getAttribute('checksuperusergroup', 0);

        // Cache user groups base on checksuperusergroup attribute value
        if (!isset(static::$options[$checkSuperUser])) {
            $groups       = UserGroupsHelper::getInstance()->getAll();
            $isSuperUser  = Factory::getUser()->authorise('core.admin');
            $cacheOptions = array();

            $inherit = new stdClass;
            $inherit->id = -1;
            $inherit->level = 0;
            $inherit->title = 'Inherited';
            array_unshift($groups, $inherit);

            foreach ($groups as $group) {
                // Don't show super user groups to non super users.
                if ($checkSuperUser && !$isSuperUser && Access::checkGroup($group->id, 'core.admin')) {
                    continue;
                }

                $cacheOptions[] = (object) array(
                    'text'  => str_repeat('- ', $group->level) . $group->title,
                    'value' => $group->id,
                    'level' => $group->level,
                );
            }

            static::$options[$checkSuperUser] = $cacheOptions;
        }

        return array_merge($options, static::$options[$checkSuperUser]);
    }
}
