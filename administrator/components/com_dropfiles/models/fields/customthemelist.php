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

defined('_JEXEC') || die;

jimport('joomla.form.formfield');


/**
 * Class JFormFieldJavaButton
 */
class JFormFieldCustomThemeList extends JFormField
{
    /**
     * Type
     *
     * @var string
     */
    protected $type = 'customthemelist';

    /**
     * Method to get the field input button
     *
     * @return string
     *
     * @since 5.1
     */
    protected function getInput()
    {
        $html = array();
        $themes = $this->getThemes();
        if (!is_array($themes) || empty($themes)) {
            return '<span>' . JText::_('COM_DROPFILES_CONFIG_CLONED_THEME_LIST_NO_THEME') . '</span>';
        }
        // The reindex button
        $html[] = '<ul class="custom-themes">';
        foreach ($themes as $theme) {
            $html[] = '<li style="list-style: none;margin-bottom: 5px;">';
            $html[] = '<a href="' . $theme['url'] . '" style="text-decoration:none;" class="" title="' . $theme['name'] . '">' . $theme['name'] . '</a>';
        }
        $html[] = '  </li>';
        $html[] = '</ul>';

        return implode("\n", $html);
    }

    /**
     * Get custom theme
     *
     * @return array
     *
     * @since 5.1
     */
    protected function getThemes()
    {
        $themes = array();
        $path_dropfilespluginbase = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesPluginBase.php';
        JLoader::register('DropfilesPluginBase', $path_dropfilespluginbase);
        $defaultThemes = DropfilesPluginBase::getDropfilesThemes();

        JPluginHelper::importPlugin('dropfilesthemes');
        JLoader::register('DropfilesBase', JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesBase.php');
        $availableThemes = DropfilesBase::getDropfilesThemes();

        foreach ($availableThemes as $theme) {
            if (!in_array($theme['id'], $defaultThemes)) {
                $plg_id = JPluginHelper::getPlugin('dropfilesthemes', $theme['id'])->id;
                $themes[] = array(
                    'name' => $theme['name'],
                    'url'  => JURI::base() . 'index.php?option=com_plugins&task=plugin.edit&extension_id=' . $plg_id . '#attrib-' . $theme['id'] . '_settings'
                );
            }
        }
        return $themes;
    }
}
