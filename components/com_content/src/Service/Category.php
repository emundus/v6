<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Site\Service;

use Joomla\CMS\Categories\Categories;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Content Component Category Tree
 *
 * @since  1.6
 */
class Category extends Categories
{
    /**
     * Class constructor
     *
     * @param   array  $options  Array of options
     *
     * @since   1.7.0
     */
    public function __construct($options = [])
    {
        $options['table']     = '#__content';
        $options['extension'] = 'com_content';

        parent::__construct($options);
    }
}
