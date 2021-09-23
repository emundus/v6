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

jimport('joomla.application.component.modeladmin');

/**
 * Class DropfilesModelConfig
 */
class DropfilesModelConfig extends JModelAdmin
{
    /**
     * Method to get config form
     *
     * @param array   $data     Data
     * @param boolean $loadData Load data
     *
     * @return boolean
     * @since  version
     */
    public function getForm($data = array(), $loadData = true)
    {
        //Get the theme
        $theme = $this->getCurrentTheme();

        // Add the search path for the admin component config.xml file.
        JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/com_dropfiles');

        // Get the form.
        $xmlform = '<form>
            <fieldset>
                
            </fieldset>
        </form>';
        $form = $this->loadForm(
            'com_dropfiles.config',
            $xmlform,
            array('control' => 'jform', 'load_data' => $loadData)
        );
        if (empty($form)) {
            return false;
        }

        // If type is already known we can load the plugin form
        JPluginHelper::importPlugin('dropfilesthemes');
        $app = JFactory::getApplication();
        $app->triggerEvent('onConfigForm', array($theme, &$form));

        if (isset($loadData) && $loadData) {
            // Get the data for the form.
            $data = $this->loadFormData();
            $form->bind($data);
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return mixed    The data for the form.
     * @since  1.6
     */
//    protected function loadFormData()
//    {
//        // Check the session for previously entered form data.
//        $data = $this->getParams();
//        return array('params'=>$data);
//    }

    /**
     * Save data
     *
     * @param array $data Data
     *
     * @return boolean
     * @since  version
     */
    public function save($data)
    {
        $dbo = $this->getDbo();
        $query = 'UPDATE #__dropfiles SET params=' . $dbo->quote(json_encode($data['params']));
        $query .= ' WHERE id=' . (int)$data['id'];
        $dbo->setQuery($query);
        if ($dbo->execute()) {
            return true;
        }

        return false;
    }


    /**
     * Get the params from a gallery id
     *
     * @param integer $id Dropfiles id
     *
     * @return boolean|mixed
     * @since  version
     */
    public function getParams($id)
    {
        $dbo = $this->getDbo();
        $query = 'SELECT params FROM #__dropfiles WHERE id=' . (int)$id;
        $dbo->setQuery($query);
        if ($dbo->execute()) {
            return json_decode($dbo->loadResult());
        }
        return false;
    }


    /**
     * Method to get current theme
     *
     * @return string
     * @since  version
     */
    public function getCurrentTheme()
    {
        $dbo = $this->getDbo();
        $dbo->setQuery();
        if ($dbo->execute()) {
            return $dbo->loadResult();
        }

        return 'default';
    }

    /**
     * Method to set theme
     *
     * @param string  $theme  Theme name
     * @param integer $id     Category id
     * @param string  $params Params
     *
     * @return boolean
     * @since  version
     */
    public function setTheme($theme, $id, $params)
    {
        $dbo = $this->getDbo();
        $query = 'UPDATE #__dropfiles SET theme=' . $dbo->quote($theme) . ', params='. $dbo->quote($params) .' WHERE id=' . (int)$id;
        $dbo->setQuery($query);
        if ($dbo->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Clone theme with params
     *
     * @param string $cloneTheme Source theme name
     * @param string $newTheme   New theme name
     *
     * @return array
     *
     * @since 5.1
     */
    public function cloneTheme($cloneTheme = '', $newTheme = '')
    {
        if ($cloneTheme === '' || $newTheme === '' || trim($cloneTheme) === trim($newTheme)) {
            return array('success' => false, 'message' => 'Theme name empty!'); // todo: translate
        }
        $ds = DIRECTORY_SEPARATOR;
        $pluginsPath = JPATH_ROOT . $ds . 'plugins' . $ds . 'dropfilesthemes';
        $cloneThemePath = $pluginsPath . $ds . $cloneTheme;
        $newThemePath = $pluginsPath . $ds . $newTheme;

        if (!file_exists($pluginsPath) || !file_exists($cloneThemePath) || file_exists($newThemePath)) {
            return array('success' => false, 'message' => sprintf(JText::_('COM_DROPFILES_CONFIG_THEME_CLONED_EXISTS'), $newThemePath)); // todo: translate
        }

        $this->copyFolder($cloneThemePath, $newThemePath);
        $this->renameTheme($newThemePath, $cloneTheme, $newTheme);
        // Rename main file
        rename($newThemePath . $ds . $cloneTheme . '.php', $newThemePath . $ds . $newTheme . '.php');
        rename($newThemePath . $ds . $cloneTheme . '.xml', $newThemePath . $ds . $newTheme . '.xml');
        // Rename language file
        rename($newThemePath . $ds . 'language' . $ds . 'en-GB' . $ds . 'en-GB.plg_dropfilesthemes_' . $cloneTheme . '.ini', $newThemePath . $ds . 'language' . $ds . 'en-GB' . $ds . 'en-GB.plg_dropfilesthemes_' . $newTheme . '.ini');
        // Copy thumbnail
        $source = JPATH_ROOT . $ds . 'components' . $ds . 'com_dropfiles' . $ds . 'assets' . $ds . 'images' . $ds . 'theme' . $ds . strtolower($cloneTheme) . '.png';
        if (!in_array(strtolower($cloneTheme), array('default', 'ggd', 'tree', 'table'))) {
            $source = $pluginsPath . $ds . strtolower($cloneTheme) . $ds . strtolower($cloneTheme) . '.png';
        }
        $target = $pluginsPath . $ds . strtolower($newTheme) . $ds . strtolower($newTheme) . '.png';
        if (file_exists($source)) {
            copy($source, $target);
        }

        // Get clone theme params
        $params = $this->generateNewThemeParams($cloneTheme, $newTheme);

        // Activate new plugin with default params from clone theme
        $this->enableTheme($newTheme, $params);

        return array('success' => true, 'message' => JText::_('COM_DROPFILES_CONFIG_THEME_CLONED_SUCCESS'));
    }

    /**
     * Generate new theme params from clone theme
     *
     * @param string $cloneTheme Source theme name
     * @param string $newTheme   New theme name
     *
     * @return object
     *
     * @since 5.1
     */
    protected function generateNewThemeParams($cloneTheme, $newTheme)
    {
        $newParams = new stdClass;

        // Get default theme params
        $prefix = strtolower($cloneTheme) . '_';

        if (in_array(strtolower($cloneTheme), array('default', 'ggd', 'tree', 'table'))) {
            $params = JComponentHelper::getParams('com_dropfiles')->toArray();
        } else {
            // Get params from plugins
            $plugin = JPluginHelper::getPlugin('dropfilesthemes', strtolower($cloneTheme));
            $params = new JRegistry($plugin->params);
            $params = $params->toArray();
        }

        // Filter all params key start with clone theme name
        foreach ($params as $key => $value) {
            if (strpos($key, $prefix) === 0) {
                $newKey = str_replace(strtolower($cloneTheme) . '_', strtolower($newTheme) . '_', $key);
                $newParams->$newKey = (string) $value;
            }
        }

        return $newParams;
    }

    /**
     * Enable new theme plugin
     *
     * @param string $themeName New theme name
     * @param object $params    Default params
     *
     * @return void
     * @since  5.1
     */
    protected function enableTheme($themeName, $params = null)
    {
        if ($params === null) {
            $params = new stdClass;
        }

        $params = json_encode($params);

        $db                 = JFactory::getDbo();
        $meta               = new StdClass;
        $meta->name         = 'Dropfiles themes - ' . $themeName;
        $meta->type         = 'plugin';
        $meta->creationDate = '5-May-2013';
        $meta->author       = 'JoomUnited';
        $meta->copyright    = '';
        $meta->authorEmail  = 'contact@joomunited.com';
        $meta->authorUrl    = 'http://www.joomunited.com';
        $meta->version      = '5.7.7';
        $meta->description  = ucfirst($themeName) . ' theme for Dropfiles';
        $meta->group        = '';
        $meta->filename     = $themeName;

        $query = 'INSERT INTO `#__extensions` (name, type, element, folder, client_id, enabled, access, protected, manifest_cache, params, custom_data) VALUE (' . $db->quote($meta->name) . ', \'plugin\', ' . $db->quote($themeName) . ', \'dropfilesthemes\', 0, 1, 1, 0, ' . $db->quote(stripslashes(json_encode($meta))) . ', ' . $db->quote($params) . ',"");';
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Search and replace theme name in cloned theme
     *
     * @param string $newThemePath New theme path
     * @param string $cloneTheme   Source theme name
     * @param string $newTheme     New theme name
     *
     * @return void
     *
     * @since 5.1
     */
    protected function renameTheme($newThemePath, $cloneTheme, $newTheme)
    {
        $directory = opendir($newThemePath);
        $ds = DIRECTORY_SEPARATOR;
        // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found -- Loop for each $file in directory
        while (($file = readdir($directory)) !== false) {
            if (($file !== '.') && ($file !== '..')) {
                $file_path = $newThemePath . $ds . $file;
                if (is_dir($file_path)) {
                    $this->renameTheme($file_path, $cloneTheme, $newTheme);
                } else {
                    $ext           = pathinfo($file, PATHINFO_EXTENSION);
                    $file_contents = file_get_contents($file_path);
                    // Add class holder to keep icons
                    if ($file === 'tpl.php') {
                        $file_contents = str_replace('dropfiles-content-' . $cloneTheme, 'dropfiles-content-tttttt dropfiles-content-' . $cloneTheme, $file_contents);
                    }
                    if ($ext === 'js') {
                        $file_contents = str_replace('preventDefault', 'dafug893jnb', $file_contents);
                    }
                    if ($cloneTheme === 'table') {
                        if (strtolower($ext) === 'css') {
                            $file_contents = str_replace('-table', '-' . $newTheme, $file_contents);
                            $file_contents = str_replace('.table-download-category', '.' . $newTheme . '-download-category', $file_contents);
                        } elseif (strtolower($ext) === 'js') {
                            $file_contents = str_replace('-table', '-' . $newTheme, $file_contents);
                        } elseif (strtolower($ext) === 'php') {
                            if ($file === 'tpl.php') {
                                $file_contents = str_replace('_table', '_p45jdi', $file_contents);
                                $file_contents = str_replace('table_', 'p45jdi_', $file_contents);
                                $file_contents = str_replace('table-', 'p45jdi-', $file_contents);
                                $file_contents = str_replace('-table', '-p45jdi', $file_contents);
                                $file_contents = str_replace('p45jdi', $newTheme, $file_contents);
                            } else {
                                $file_contents = str_replace('table', $newTheme, $file_contents);
                                $file_contents = str_replace(
                                    $newTheme . 'class',
                                    'tableclass',
                                    $file_contents
                                );
                                $file_contents = str_replace($newTheme . '-', 'table-', $file_contents);
                                $file_contents = str_replace($newTheme . ' ', 'table ', $file_contents);
                                $file_contents = str_replace(
                                    'PlgDropfilesthemesTable',
                                    'PlgDropfilesthemes' . ucfirst(str_replace('_', '', $newTheme)),
                                    $file_contents
                                );
                            }
                        } elseif (strtolower($ext) === 'xml') {
                            $file_contents = str_replace('name="table_', 'name="' . strtolower($newTheme) . '_', $file_contents);
                        }
                    } else {
                        if ($cloneTheme === 'default') {
                            $file_contents = str_replace('default=', 'p45jdi=', $file_contents);
                        }
                        $file_contents = str_replace($cloneTheme, $newTheme, $file_contents);
                        $file_contents = str_replace(
                            ucfirst($cloneTheme),
                            ucfirst(str_replace('_', '', $newTheme)),
                            $file_contents
                        );

                        if ($cloneTheme === 'default') {
                            $file_contents = str_replace('p45jdi=', 'default=', $file_contents);
                            if (strtolower($ext) === 'xml') {
                                $file_contents = str_replace('name="params', 'p45jdi="params', $file_contents);
                                $file_contents = str_replace('name="', 'name="' . $newTheme . '_', $file_contents);
                                $file_contents = str_replace('p45jdi="params', 'name="params', $file_contents);
                            }

                            if ($file === 'tpl.php' || $file === 'default.php') {
                                $file_contents = str_replace('$this->params, \'', '$this->params, \'' . $newTheme . '_', $file_contents);
                            }
                        }
                    }
                    if (strtolower($ext) === 'xml' && $file !== 'form.xml') {
                        $file_contents = str_replace(strtoupper($cloneTheme) . '_PARAM', strtoupper($newTheme) . '_PARAM', $file_contents);
                        $file_contents = str_replace('<!--config', '<config', $file_contents);
                        $file_contents = str_replace('config-->', 'config>', $file_contents);
                    }
                    if (strtolower($ext) === 'ini') {
                        $file_contents = str_replace(strtoupper($cloneTheme) . '_PARAM', strtoupper($newTheme) . '_PARAM', $file_contents);

                        if (strtolower($cloneTheme) === 'default') {
                            $file_contents = str_replace('COM_PLUGINS_SETTINGS', 'COM_PLUGINS_' . strtoupper($newTheme) . '_SETTINGS', $file_contents);
                        } else {
                            $file_contents = str_replace('COM_PLUGINS_' . strtoupper($cloneTheme) . '_SETTINGS', 'COM_PLUGINS_' . strtoupper($newTheme) . '_SETTINGS', $file_contents);
                        }
                    }
                    if ($file === 'tpl.php') {
                        $file_contents = str_replace('dropfiles-content-tttttt', 'dropfiles-content-' . $cloneTheme, $file_contents);
                    }
                    if ($ext === 'js') {
                        $file_contents = str_replace('dafug893jnb', 'preventDefault', $file_contents);
                    }
                    file_put_contents($file_path, $file_contents);
                }
            }
        }
    }

    /**
     * Copy folder
     *
     * @param string $src Path
     * @param string $dst Destination path
     *
     * @return void
     * @since  5.1
     */
    protected function copyFolder($src, $dst)
    {
        $dir = opendir($src);
        $ds = DIRECTORY_SEPARATOR;
        if (mkdir($dst)) {
            // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found -- Loop for each folder in $dir
            while (false !== ($file = readdir($dir))) {
                if (($file !== '.') && ($file !== '..')) {
                    if (is_dir($src . $ds . $file)) {
                        $this->copyFolder($src . $ds . $file, $dst . $ds . $file);
                    } else {
                        copy($src . $ds . $file, $dst . $ds . $file);
                    }
                }
            }
            closedir($dir);
        }
    }
}
