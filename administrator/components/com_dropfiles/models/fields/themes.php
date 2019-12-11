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
defined('_JEXEC') || die;

jimport('joomla.form.formfield');
JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 */
class JFormFieldThemes extends JFormFieldList
{
    /**
     * Type
     *
     * @var string
     */
    protected $type = 'Themes';


    /**
     * Method to get a list of themes
     *
     * @return array  The field option objects.
     */
    protected function getOptions()
    {
        // JComponentHelper::getParams('com_dropfiles');
        JFactory::getApplication();
        JPluginHelper::importPlugin('dropfilesthemes');
        $dispatcher = JDispatcher::getInstance();
        $themes = $dispatcher->trigger('getThemeName');

        $options = array();
        if (!empty($themes)) {
            foreach ($themes as $theme) {
                $options[] = JHtml::_('select.option', $theme['id'], $theme['name']);
            }
        }

        return $options;
    }
}
