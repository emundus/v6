<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Helper\UserGroupsHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Field to load a dropdown list of available user groups
 *
 * @since  3.2
 */
class UsergrouplistField extends ListField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   3.2
     */
    protected $type = 'UserGroupList';

    /**
     * Cached array of the category items.
     *
     * @var    array
     * @since  3.2
     */
    protected static $options = [];

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
     * @since   1.7.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        if (\is_string($value) && strpos($value, ',') !== false) {
            $value = explode(',', $value);
        }

        return parent::setup($element, $value, $group);
    }

    /**
     * Method to get the options to populate list
     *
     * @return  array  The field option objects.
     *
     * @since   3.2
     */
    protected function getOptions()
    {
        $options        = parent::getOptions();
        $checkSuperUser = (int) $this->getAttribute('checksuperusergroup', 0);

        // Cache user groups base on checksuperusergroup attribute value
        if (!isset(static::$options[$checkSuperUser])) {
            $groups       = UserGroupsHelper::getInstance()->getAll();
            $cacheOptions = [];

            foreach ($groups as $group) {
                // Don't list super user groups.
                if ($checkSuperUser && Access::checkGroup($group->id, 'core.admin')) {
                    continue;
                }

                $cacheOptions[] = (object) [
                    'text'  => str_repeat('- ', $group->level) . $group->title,
                    'value' => $group->id,
                    'level' => $group->level,
                ];
            }

            static::$options[$checkSuperUser] = $cacheOptions;
        }

        return array_merge($options, static::$options[$checkSuperUser]);
    }
}
