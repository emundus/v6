<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Field to load a list of possible item count limits
 *
 * @since  3.2
 */
class LimitboxField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.2
     */
    public $type = 'Limitbox';

    /**
     * Cached array of the category items.
     *
     * @var    array
     * @since  3.2
     */
    protected static $options = [];

    /**
     * Default options
     *
     * @var  array
     */
    protected $defaultLimits = [5, 10, 15, 20, 25, 30, 50, 100, 200, 500];

    /**
     * Method to get the options to populate to populate list
     *
     * @return  array  The field option objects.
     *
     * @since   3.2
     */
    protected function getOptions()
    {
        // Accepted modifiers
        $hash = md5($this->element->asXML());

        if (!isset(static::$options[$hash])) {
            static::$options[$hash] = parent::getOptions();

            $options = [];
            $limits = $this->defaultLimits;

            // Limits manually specified
            if (isset($this->element['limits'])) {
                $limits = explode(',', $this->element['limits']);
            }

            // User wants to add custom limits
            if (isset($this->element['append'])) {
                $limits = array_unique(array_merge($limits, explode(',', $this->element['append'])));
            }

            // User wants to remove some default limits
            if (isset($this->element['remove'])) {
                $limits = array_diff($limits, explode(',', $this->element['remove']));
            }

            // Order the options
            asort($limits);

            // Add an option to show all?
            $showAll = isset($this->element['showall']) ? (string) $this->element['showall'] === 'true' : true;

            if ($showAll) {
                $limits[] = 0;
            }

            if (!empty($limits)) {
                foreach ($limits as $value) {
                    $options[] = (object) [
                        'value' => $value,
                        'text' => ($value != 0) ? Text::_('J' . $value) : Text::_('JALL'),
                    ];
                }

                static::$options[$hash] = array_merge(static::$options[$hash], $options);
            }
        }

        return static::$options[$hash];
    }
}
