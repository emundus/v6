<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.stats
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Stats\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base field for the Stats Plugin.
 *
 * @since  3.5
 */
abstract class AbstractStatsField extends FormField
{
    /**
     * Get the layouts paths
     *
     * @return  array
     *
     * @since   3.5
     */
    protected function getLayoutPaths()
    {
        $template = Factory::getApplication()->getTemplate();

        return [
            JPATH_ADMINISTRATOR . '/templates/' . $template . '/html/layouts/plugins/system/stats',
            JPATH_PLUGINS . '/system/stats/layouts',
            JPATH_SITE . '/layouts',
        ];
    }
}
