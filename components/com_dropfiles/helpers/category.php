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
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

// no direct access
defined('_JEXEC') || die;

jimport('joomla.application.categories');


/**
 * Class DropfilesCategories
 */
class DropfilesCategories extends JCategories
{
    /**
     * DropfilesCategories constructor.
     *
     * @param array $options Options
     *
     * @return void
     * @since  version
     */
    public function __construct($options = array())
    {
        $options['table'] = '#__dropfiles_files';
        $options['extension'] = 'com_dropfiles';
        parent::__construct($options);
    }
}
