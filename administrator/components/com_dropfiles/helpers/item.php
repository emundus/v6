<?php
/**
 * Dropfiles
 *
 * @package    Joomla.Administrator
 * @subpackage com_menus
 *
 * @copyright Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @since     1.6
 */

defined('_JEXEC') || die;

use Joomla\Registry\Registry;

jimport('joomla.application.component.modeladmin');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.tablenested');
jimport('joomla.filesystem.path');

require_once JPATH_ROOT . '/administrator/components/com_menus/helpers/menus.php';

JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_menus/tables');

/**
 * MenusModelItem class
 *
 * Menu Item Model for Menus.
 */
class MenusModelItem extends JModelAdmin
{
    /**
     * The type alias for this content type.
     *
     * @var string
     */
    public $typeAlias = 'com_menus.item';

    /**
     * The context used for the associations table
     *
     * @var string
     */
    protected $associationsContext = 'com_menus.item';

    /**
     * Text prefix
     *
     * @var string The prefix to use with controller messages.
     */
    protected $text_prefix = 'COM_MENUS_ITEM';

    /**
     * Help key
     *
     * @var string The help screen key for the menu item.
     */
    protected $helpKey = 'JHELP_MENUS_MENU_ITEM_MANAGER_EDIT';

    /**
     * Help url
     *
     * @var string The help screen base URL for the menu item.
     */
    protected $helpURL;

    /**
     * Help local
     *
     * @var boolean True to use local lookup for the help screen.
     */
    protected $helpLocal = false;

    /**
     * Batch copy/move command. If set to false,
     * the batch copy/move command is not supported
     *
     * @var string
     */
    protected $batch_copymove = 'menu_id';

    /**
     * Allowed batch commands
     *
     * @var array
     */
    protected $batch_commands = array(
        'assetgroup_id' => 'batchAccess',
        'language_id'   => 'batchLanguage'
    );

    /**
     * Method to test whether a record can be deleted.
     *
     * @param object $record A record object.
     *
     * @return boolean  True if allowed to delete the record. Defaults to the permission set in the component.
     *
     * @since 1.6
     */
    protected function canDelete($record)
    {
        if (!empty($record->id)) {
            if ($record->published !== -2) {
                return false;
            }

            return parent::canDelete($record);
        }
    }

    /**
     * Batch copy menu items to a new menu or parent.
     *
     * @param integer $value    The new menu or sub-item.
     * @param array   $pks      An array of row IDs.
     * @param array   $contexts An array of item contexts.
     *
     * @return mixed An array of new IDs on success, boolean false on failure.
     *
     * @internal param array $contexts An array of item contexts.
     * @since    1.6
     */
    protected function batchCopy($value, $pks, $contexts = array())
    {
        // $value comes as {menutype}.{parent_id}
        $parts    = explode('.', $value);
        $menuType = $parts[0];
        $parentId = (int) Joomla\Utilities\ArrayHelper::getValue($parts, 1, 0);

        $table    = $this->getTable();
        $db       = $this->getDbo();
        $query    = $db->getQuery(true);
        $newIds   = array();

        // Check that the parent exists
        if ($parentId) {
            if (!$table->load($parentId)) {
                $error = $table->getError();
                if ($error) {
                    // Fatal error
                    $this->setError($error);

                    return false;
                } else {
                    // Non-fatal error
                    $this->setError(JText::_('JGLOBAL_BATCH_MOVE_PARENT_NOT_FOUND'));
                    $parentId = 0;
                }
            }
        }

        // If the parent is 0, set it to the ID of the root item in the tree
        if (empty($parentId)) {
            $parentId = $table->getRootId();
            if (!$parentId) {
            //    $this->setError($db->getErrorMsg());

                return false;
            }
        }

        // Check that user has create permission for menus
        $user = JFactory::getUser();

        if (!$user->authorise('core.create', 'com_menus')) {
            $this->setError(JText::_('COM_MENUS_BATCH_MENU_ITEM_CANNOT_CREATE'));

            return false;
        }

        // We need to log the parent ID
        $parents = array();

        // Calculate the emergency stop count as a precaution against a runaway loop bug
        $query->select('COUNT(id)')
            ->from($db->quoteName('#__menu'));
        $db->setQuery($query);

        try {
            $count = $db->loadResult();
        } catch (RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Parent exists so we let's proceed
        while (!empty($pks) && $count > 0) {
            // Pop the first id off the stack
            $pk = array_shift($pks);

            $table->reset();

            // Check that the row actually exists
            if (!$table->load($pk)) {
                $error = $table->getError();
                if ($error) {
                    // Fatal error
                    $this->setError($error);

                    return false;
                } else {
                    // Not fatal error
                    $this->setError(JText::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }

            // Copy is a bit tricky, because we also need to copy the children
            $query->clear()
                ->select('id')
                ->from($db->quoteName('#__menu'))
                ->where('lft > ' . (int)$table->lft)
                ->where('rgt < ' . (int)$table->rgt);
            $db->setQuery($query);
            $childIds = $db->loadColumn();

            // Add child ID's to the array only if they aren't already there.
            foreach ($childIds as $childId) {
                if (!in_array($childId, $pks)) {
                    array_push($pks, $childId);
                }
            }

            // Make a copy of the old ID and Parent ID
            $oldId = $table->id;
            $oldParentId = $table->parent_id;

            // Reset the id because we are making a copy.
            $table->id = 0;

            // If we a copying children, the Old ID will turn up in the parents list
            // otherwise it's a new top level item
            $table->parent_id = isset($parents[$oldParentId]) ? $parents[$oldParentId] : $parentId;
            $table->menutype = $menuType;

            // Set the new location in the tree for the node.
            $table->setLocation($table->parent_id, 'last-child');

            // TODO: Deal with ordering?
            // $table->ordering = 1;
            $table->level = null;
            $table->lft = null;
            $table->rgt = null;
            $table->home = 0;

            // Alter the title & alias
            list($title, $alias) = $this->generateNewTitle($table->parent_id, $table->alias, $table->title);
            $table->title = $title;
            $table->alias = $alias;

            // Check the row.
            if (!$table->check()) {
                $this->setError($table->getError());

                return false;
            }
            // Store the row.
            if (!$table->store()) {
                $this->setError($table->getError());

                return false;
            }

            // Get the new item ID
            $newId = $table->get('id');

            // Add the new ID to the array
            $newIds[$pk] = $newId;

            // Now we log the old 'parent' to the new 'parent'
            $parents[$oldId] = $table->id;
            $count--;
        }

        // Rebuild the hierarchy.
        if (!$table->rebuild()) {
            $this->setError($table->getError());

            return false;
        }

        // Rebuild the tree path.
        if (!$table->rebuildPath($table->id)) {
            $this->setError($table->getError());

            return false;
        }

        // Clean the cache
        $this->cleanCache();

        return $newIds;
    }

    /**
     * Batch move menu items to a new menu or parent.
     *
     * @param integer $value    The new menu or sub-item.
     * @param array   $pks      An array of row IDs.
     * @param array   $contexts An array of item contexts.
     *
     * @return boolean True on success.
     *
     * @internal param array $contexts An array of item contexts.
     * @since    1.6
     */
    protected function batchMove($value, $pks, $contexts = array())
    {
        // $value comes as {menutype}.{parent_id}
        $parts    = explode('.', $value);
        $menuType = $parts[0];
        $parentId = (int) Joomla\Utilities\ArrayHelper::getValue($parts, 1, 0);

        $table    = $this->getTable();
        $db       = $this->getDbo();
        $query    = $db->getQuery(true);

        // Check that the parent exists.
        if ($parentId) {
            if (!$table->load($parentId)) {
                $error = $table->getError();
                if ($error) {
                    // Fatal error
                    $this->setError($error);

                    return false;
                } else {
                    // Non-fatal error
                    $this->setError(JText::_('JGLOBAL_BATCH_MOVE_PARENT_NOT_FOUND'));
                    $parentId = 0;
                }
            }
        }

        // Check that user has create and edit permission for menus
        $user = JFactory::getUser();

        if (!$user->authorise('core.create', 'com_menus')) {
            $this->setError(JText::_('COM_MENUS_BATCH_MENU_ITEM_CANNOT_CREATE'));

            return false;
        }

        if (!$user->authorise('core.edit', 'com_menus')) {
            $this->setError(JText::_('COM_MENUS_BATCH_MENU_ITEM_CANNOT_EDIT'));

            return false;
        }

        // We are going to store all the children and just moved the menutype
        $children = array();

        // Parent exists so we let's proceed
        foreach ($pks as $pk) {
            // Check that the row actually exists
            if (!$table->load($pk)) {
                $error = $table->getError();
                if ($error) {
                    // Fatal error
                    $this->setError($error);

                    return false;
                } else {
                    // Not fatal error
                    $this->setError(JText::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }

            // Set the new location in the tree for the node.
            $table->setLocation($parentId, 'last-child');

            // Set the new Parent Id
            $table->parent_id = $parentId;

            // Check if we are moving to a different menu
            if ($menuType !== $table->menutype) {
                // Add the child node ids to the children array.
                $query->clear()
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__menu'))
                    ->where($db->quoteName('lft') . ' BETWEEN ' . (int)$table->lft . ' AND ' . (int)$table->rgt);
                $db->setQuery($query);
                $children = array_merge($children, (array)$db->loadColumn());
            }

            // Check the row.
            if (!$table->check()) {
                $this->setError($table->getError());

                return false;
            }

            // Store the row.
            if (!$table->store()) {
                $this->setError($table->getError());

                return false;
            }

            // Rebuild the tree path.
            if (!$table->rebuildPath()) {
                $this->setError($table->getError());

                return false;
            }
        }

        // Process the child rows
        if (!empty($children)) {
            // Remove any duplicates and sanitize ids.
            $children = array_unique($children);
            Joomla\Utilities\ArrayHelper::toInteger($children);

            // Update the menutype field in all nodes where necessary.
            $query->clear()
                ->update($db->quoteName('#__menu'))
                ->set($db->quoteName('menutype') . ' = ' . $db->quote($menuType))
                ->where($db->quoteName('id') . ' IN (' . implode(',', $children) . ')');
            $db->setQuery($query);

            try {
                $db->execute();
            } catch (RuntimeException $e) {
                $this->setError($e->getMessage());

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to check if you can save a record.
     *
     * @return   boolean
     * @internal param array $data An array of input data.
     * @internal param string $key The name of the key for the primary key.
     * @since    1.6
     */
    protected function canSave()
    {
        return JFactory::getUser()->authorise('core.edit', $this->option);
    }

    /**
     * Method to get the row form.
     *
     * @param array   $data     Data for the form.
     * @param boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return mixed  A JForm object on success, false on failure
     *
     * @since 1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // The folder and element vars are passed when saving the form.
        if (empty($data)) {
            $item = $this->getItem();

            // The type should already be set.
            $this->setState('item.link', $item->link);
        } else {
            $this->setState('item.link', Joomla\Utilities\ArrayHelper::getValue($data, 'link'));
            $this->setState('item.type', Joomla\Utilities\ArrayHelper::getValue($data, 'type'));
        }

        // Get the form.
        $form = $this->loadForm('com_menus.item', 'item', array('control' => 'jform', 'load_data' => $loadData), true);

        if (empty($form)) {
            return false;
        }

        // Modify the form based on access controls.
        if (!$this->canEditState((object)$data)) {
            // Disable fields for display.
            $form->setFieldAttribute('menuordering', 'disabled', 'true');
            $form->setFieldAttribute('published', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is an article you can edit.
            $form->setFieldAttribute('menuordering', 'filter', 'unset');
            $form->setFieldAttribute('published', 'filter', 'unset');
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return mixed  The data for the form.
     *
     * @since 1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = array_merge(
            (array)$this->getItem(),
            (array)JFactory::getApplication()->getUserState('com_menus.edit.item.data', array())
        );

        // For a new menu item, pre-select some filters (Status, Language, Access)
        // in edit form if those have been selected in Menu Manager
        if ((int) $this->getItem()->id === 0) {
            // Get selected fields
            $filters           = JFactory::getApplication()->getUserState('com_menus.items.filter');
            $data['published'] = (isset($filters['published']) ? $filters['published'] : null);
            $data['language']  = (isset($filters['language']) ? $filters['language'] : null);
            $data['access']    = (isset($filters['access']) ? $filters['access'] : null);
        }

        $this->preprocessData('com_menus.item', $data);

        return $data;
    }

    /**
     * Get the necessary data to load an item help screen.
     *
     * @return object  An object with key, url, and local properties for loading the item help screen.
     *
     * @since 1.6
     */
    public function getHelp()
    {
        return (object)array('key' => $this->helpKey, 'url' => $this->helpURL, 'local' => $this->helpLocal);
    }

    /**
     * Method to get a menu item.
     *
     * @param integer $pk An optional id of the object to get, otherwise the id from the model state is used.
     *
     * @return mixed  Menu item data object on success, false on failure.
     *
     * @since 1.6
     */
    public function getItem($pk = null)
    {
        $pk = (!empty($pk)) ? $pk : (int)$this->getState('item.id');

        // Get a level row instance.
        $table = $this->getTable();
        $args = array();

        // Attempt to load the row.
        $table->load($pk);

        // Check for a table object error.
        $error = $table->getError();
        if ($error) {
            $this->setError($error);

            return false;
        }

        // Prime required properties.
        $type = $this->getState('item.type');
        if ($type) {
            $table->type = $type;
        }

        if (empty($table->id)) {
            $table->parent_id = $this->getState('item.parent_id');
            $table->menutype = $this->getState('item.menutype');
            $table->params = '{}';
        }

        // If the link has been set in the state, possibly changing link type.
        $link = $this->getState('item.link');
        if ($link) {
            // Check if we are changing away from the actual link type.
            if (MenusHelper::getLinkKey($table->link) !== MenusHelper::getLinkKey($link)) {
                $table->link = $link;
            }
        }

        switch ($table->type) {
            case 'alias':
                $table->component_id = 0;
                $args = array();

                parse_str(parse_url($table->link, PHP_URL_QUERY), $args);
                break;

            case 'separator':
            case 'heading':
                $table->link = '';
                $table->component_id = 0;
                break;

            case 'url':
                $table->component_id = 0;

                $args = array();
                parse_str(parse_url($table->link, PHP_URL_QUERY), $args);
                break;

            case 'component':
            default:
                // Enforce a valid type.
                $table->type = 'component';

                // Ensure the integrity of the component_id field is maintained,
                // particularly when changing the menu item type.
                $args = array();
                parse_str(parse_url($table->link, PHP_URL_QUERY), $args);

                if (isset($args['option'])) {
                    // Load the language file for the component.
                    $lang = JFactory::getLanguage();
                    $lang->load($args['option'], JPATH_ADMINISTRATOR, null, false, true)
                    || $lang->load(
                        $args['option'],
                        JPATH_ADMINISTRATOR . '/components/' . $args['option'],
                        null,
                        false,
                        true
                    );

                    // Determine the component id.
                    $component = JComponentHelper::getComponent($args['option']);

                    if (isset($component->id)) {
                        $table->component_id = $component->id;
                    }
                }
                break;
        }

        // We have a valid type, inject it into the state for forms to use.
        $this->setState('item.type', $table->type);

        // Convert to the JObject before adding the params.
        $properties = $table->getProperties(1);
        $result = Joomla\Utilities\ArrayHelper::toObject($properties);

        // Convert the params field to an array.
        $registry = new Registry;
        $registry->loadString($table->params);
        $result->params = $registry->toArray();

        // Merge the request arguments in to the params for a component.
        if ($table->type === 'component') {
            // Note that all request arguments become reserved parameter names.
            $result->request = $args;
            $result->params = array_merge($result->params, $args);
        }

        if ($table->type === 'alias') {
            // Note that all request arguments become reserved parameter names.
            $result->params = array_merge($result->params, $args);
        }

        if ($table->type === 'url') {
            // Note that all request arguments become reserved parameter names.
            $result->params = array_merge($result->params, $args);
        }

        // Load associated menu items
        $assoc = JLanguageAssociations::isEnabled();

        if ($assoc) {
            if ($pk !== null) {
                $result->associations = MenusHelper::getAssociations($pk);
            } else {
                $result->associations = array();
            }
        }

        $result->menuordering = $pk;

        return $result;
    }

    /**
     * Get the list of modules not in trash.
     *
     * @return mixed  An array of module records (id, title, position), or false on error.
     *
     * @since 1.6
     */
    public function getModules()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        /**
         * Join on the module-to-menu mapping table.
         * We are only interested if the module is displayed on ALL or THIS menu item (or the inverse ID number).
         * sqlsrv changes for modulelink to menu manager
         */
        $query->select('a.id, a.title, a.position, a.published, map.menuid')
            ->from('#__modules AS a')
            ->join(
                'LEFT',
                sprintf(
                    '#__modules_menu AS map ON map.moduleid = a.id AND map.menuid IN (0, %1$d, -%1$d)',
                    $this->getState('item.id')
                )
            )
            ->select('(SELECT COUNT(*) FROM #__modules_menu WHERE moduleid = a.id AND menuid < 0) AS '
                . $db->quoteName('except'));

        // Join on the asset groups table.
        $query->select('ag.title AS access_title')
            ->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access')
            ->where('a.published >= 0')
            ->where('a.client_id = 0')
            ->order('a.position, a.ordering');

        $db->setQuery($query);

        try {
            $result = $db->loadObjectList();
        } catch (RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        return $result;
    }

    /**
     * Get the list of all view levels
     *
     * @return array  An array of all view levels (id, title).
     *
     * @since 3.4
     */
    public function getViewLevels()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Get all the available view levels
        $query->select($db->quoteName('id'))
            ->select($db->quoteName('title'))
            ->from($db->quoteName('#__viewlevels'))
            ->order($db->quoteName('id'));

        $db->setQuery($query);

        try {
            $result = $db->loadObjectList();
        } catch (RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        return $result;
    }

    /**
     * A protected method to get the where clause for the reorder.
     * This ensures that the row will be moved relative to a row with the same menutype.
     *
     * @param JTableMenu $table JTableMenu instance.
     *
     * @return string An array of conditions to add to add to ordering queries.
     *
     * @since 1.6
     */
    protected function getReorderConditions($table)
    {
        return 'menutype = ' . $this->_db->quote($table->menutype);
    }

    /**
     * Returns a Table object, always creating it
     *
     * @param string $type   The table type to instantiate.
     * @param string $prefix A prefix for the table class name. Optional.
     * @param array  $config Configuration array for model. Optional.
     *
     * @return JTable A database object.
     *
     * @since 1.6
     */
    public function getTable($type = 'Menu', $prefix = 'MenusTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return void
     * @throws Exception When application can not start
     * @since  1.6
     */
    protected function populateState()
    {
        $app = JFactory::getApplication('administrator');

        // Load the User state.
        $pk = $app->input->getInt('id');
        $this->setState('item.id', $pk);
        $parentId = $app->getUserState('com_menus.edit.item.parent_id');
        if (!$parentId) {
            $parentId = $app->input->getInt('parent_id');
        }

        $this->setState('item.parent_id', $parentId);

        $menuType = $app->getUserState('com_menus.edit.item.menutype');

        if ($app->input->getString('menutype', false)) {
            $menuType = $app->input->getString('menutype', 'mainmenu');
        }

        $this->setState('item.menutype', $menuType);
        $type = $app->getUserState('com_menus.edit.item.type');
        if (!$type) {
            $type = $app->input->get('type');

            /**
             * Note: a new menu item will have no field type.
             * The field is required so the user has to change it.
             */
        }

        $this->setState('item.type', $type);
        $link = $app->getUserState('com_menus.edit.item.link');
        if ($link) {
            $this->setState('item.link', $link);
        }

        // Load the parameters.
        $params = JComponentHelper::getParams('com_menus');
        $this->setState('params', $params);
    }

    /**
     * Method to preprocess the form.
     *
     * @param JForm  $form  A JForm object.
     * @param mixed  $data  The data expected for the form.
     * @param string $group The name of the plugin group to import.
     *
     * @return void
     *
     * @since  1.6
     * @throws Exception If there is an error in the form event.
     */
    protected function preprocessForm(JForm $form, $data, $group = 'content')
    {
        $link = $this->getState('item.link');
        $type = $this->getState('item.type');
        $formFile = false;
        $xml = '';

        // Initialise form with component view params if available.
        if ($type === 'component') {
            $link = htmlspecialchars_decode($link);

            // Parse the link arguments.
            $args = array();
            parse_str(parse_url(htmlspecialchars_decode($link), PHP_URL_QUERY), $args);

            // Confirm that the option is defined.
            $option = '';
            $base = '';

            if (isset($args['option'])) {
                // The option determines the base path to work with.
                $option = $args['option'];
                $base = JPATH_SITE . '/components/' . $option;
            }

            if (isset($args['view'])) {
                $view = $args['view'];

                // Determine the layout to search for.
                if (isset($args['layout'])) {
                    $layout = $args['layout'];
                } else {
                    $layout = 'default';
                }

                // Check for the layout XML file. Use standard xml file if it exists.
                $tplFolders = array(
                    $base . '/views/' . $view . '/tmpl',
                    $base . '/view/' . $view . '/tmpl'
                );
                $path = JPath::find($tplFolders, $layout . '.xml');

                if (is_file($path)) {
                    $formFile = $path;
                }

                // If custom layout, get the xml file from the template folder
                // template folder is first part of file name -- template:folder
                if (!$formFile && (strpos($layout, ':') > 0)) {
                    $temp = explode(':', $layout);
                    $path_template = JPATH_SITE . '/templates/' . $temp[0] . '/html/' . $option;
                    $path_template .= '/' . $view . '/' . $temp[1] . '.xml';
                    $templatePath = JPath::clean($path_template);
                    if (is_file($templatePath)) {
                        $formFile = $templatePath;
                    }
                }
            }

            // Now check for a view manifest file
            if (!$formFile) {
                if (isset($view)) {
                    $metadataFolders = array(
                        $base . '/view/' . $view,
                        $base . '/views/' . $view
                    );
                    $metaPath = JPath::find($metadataFolders, 'metadata.xml');
                    $path = JPath::clean($metaPath);
                    if (is_file($path)) {
                        $formFile = $path;
                    }
                } else {
                    // Now check for a component manifest file
                    $path = JPath::clean($base . '/metadata.xml');

                    if (is_file($path)) {
                        $formFile = $path;
                    }
                }
            }
        }

        if ($formFile) {
            // If an XML file was found in the component, load it first.
            // We need to qualify the full path to avoid collisions with component file names.

            if ($form->loadFile($formFile, true, '/metadata') === false) {
                throw new Exception(JText::_('JERROR_LOADFILE_FAILED'));
            }

            // Attempt to load the xml file.
            $xml = simplexml_load_file($formFile);
            if (!$xml) {
                throw new Exception(JText::_('JERROR_LOADFILE_FAILED'));
            }

            // Get the help data from the XML file if present.
            $help = $xml->xpath('/metadata/layout/help');
        } else {
            // We don't have a component. Load the form XML to get the help path
            $xmlFile = JPath::find(
                JPATH_ROOT . '/administrator/components/com_menus/models/forms',
                'item_' . $type . '.xml'
            );

            // Attempt to load the xml file.
            $xml = simplexml_load_file($xmlFile);
            if ($xmlFile && !$xml) {
                throw new Exception(JText::_('JERROR_LOADFILE_FAILED'));
            }

            // Get the help data from the XML file if present.
            $help = $xml->xpath('/form/help');
        }

        if (!empty($help)) {
            $helpKey = trim((string)$help[0]['key']);
            $helpURL = trim((string)$help[0]['url']);
            $helpLoc = trim((string)$help[0]['local']);

            $this->helpKey = $helpKey ? $helpKey : $this->helpKey;
            $this->helpURL = $helpURL ? $helpURL : $this->helpURL;
            $this->helpLocal = (((string) $helpLoc === 'true') || ((string) $helpLoc === '1') || ((string) $helpLoc === 'local')) ? true : false;
        }

        // Load the specific type file
        if (!$form->loadFile('item_' . $type, false, false)) {
            throw new Exception(JText::_('JERROR_LOADFILE_FAILED'));
        }

        // Association menu items
        $assoc = JLanguageAssociations::isEnabled();

        if ($assoc) {
            $languages = JLanguageHelper::getLanguages('lang_code');

            $addform = new SimpleXMLElement('<form />');
            $fields  = $addform->addChild('fields');
            $fields->addAttribute('name', 'associations');
            $fieldset = $fields->addChild('fieldset');
            $fieldset->addAttribute('name', 'item_associations');
            $fieldset->addAttribute('description', 'COM_MENUS_ITEM_ASSOCIATIONS_FIELDSET_DESC');
            $add = false;

            foreach ($languages as $tag => $language) {
                if ($tag !== $data['language']) {
                    $add = true;
                    $field = $fieldset->addChild('field');
                    $field->addAttribute('name', $tag);
                    $field->addAttribute('type', 'menuitem');
                    $field->addAttribute('language', $tag);
                    $field->addAttribute('label', $language->title);
                    $field->addAttribute('translate_label', 'false');
                    $option = $field->addChild('option', 'COM_MENUS_ITEM_FIELD_ASSOCIATION_NO_VALUE');
                    $option->addAttribute('value', '');
                }
            }

            if ($add) {
                $form->load($addform, false);
            }
        }

        // Trigger the default form events.
        parent::preprocessForm($form, $data, $group);
    }

    /**
     * Method rebuild the entire nested set tree.
     *
     * @return boolean  False on failure or error, true otherwise.
     *
     * @since 1.6
     */
    public function rebuild()
    {
        // Initialiase variables.
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $table = $this->getTable();

        try {
            $rebuildResult = $table->rebuild();
        } catch (Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        if (!$rebuildResult) {
            $this->setError($table->getError());

            return false;
        }

        $query->select('id, params')
            ->from('#__menu')
            ->where('params NOT LIKE ' . $db->quote('{%'))
            ->where('params <> ' . $db->quote(''));
        $db->setQuery($query);

        try {
            $items = $db->loadObjectList();
        } catch (RuntimeException $e) {
            return JError::raiseWarning(500, $e->getMessage());
        }

        foreach ($items as &$item) {
            $registry = new Registry;
            $registry->loadString($item->params);
            $params = (string)$registry;

            $query->clear();
            $query->update('#__menu')
                ->set('params = ' . $db->quote($params))
                ->where('id = ' . $item->id);

            try {
                $db->setQuery($query)->execute();
            } catch (RuntimeException $e) {
                return JError::raiseWarning(500, $e->getMessage());
            }

            unset($registry);
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to save the form data.
     *
     * @param array $data The form data.
     *
     * @return boolean True on success.
     *
     * @since 1.6
     */
    public function save($data)
    {

        $pk         = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('item.id');
        $isNew      = true;
        $table      = $this->getTable();
        $context    = $this->option . '.' . $this->name;

        // Include the plugins for the on save events.
        JPluginHelper::importPlugin($this->events_map['save']);

        // Load the row if saving an existing item.
        if ($pk > 0) {
            $table->load($pk);
            $isNew = false;
        }

        if (!$isNew) {
            if ($table->parent_id === $data['parent_id']) {
                // If first is chosen make the item the first child of the selected parent.
                if ($data['menuordering'] === -1) {
                    $table->setLocation($data['parent_id'], 'first-child');
                } elseif ($data['menuordering'] === -2) {
                    // If last is chosen make it the last child of the selected parent.
                    $table->setLocation($data['parent_id'], 'last-child');
                } elseif ($data['menuordering'] && $table->id !== $data['menuordering'] || empty($data['id'])) {
                    // Don't try to put an item after itself. All other ones put after the selected item.
                    // $data['id'] is empty means it's a save as copy
                    $table->setLocation($data['menuordering'], 'after');
                } elseif ($data['menuordering'] && $table->id === $data['menuordering']) {
                    // Just leave it where it is if no change is made.
                    unset($data['menuordering']);
                }
            } else { // Set the new parent id if parent id not matched and put in last position
                $table->setLocation($data['parent_id'], 'last-child');
            }
        } else { // We have a new item, so it is not a change.
            $table->setLocation($data['parent_id'], 'last-child');
        }

        // Bind the data.
        if (!$table->bind($data)) {
            $this->setError($table->getError());

            return false;
        }

        // Alter the title & alias for save as copy.  Also, unset the home record.
        if (!$isNew && (int) $data['id'] === 0) {
            list($title, $alias) = $this->generateNewTitle($table->parent_id, $table->alias, $table->title);
            $table->title = $title;
            $table->alias = $alias;
            $table->published = 0;
            $table->home = 0;
        }

        // Check the data.
        if (!$table->check()) {
            $this->setError($table->getError());

            return false;
        }

        // Trigger the before save event.
        $app = JFactory::getApplication();
        $result = $app->triggerEvent($this->event_before_save, array($context, &$table, $isNew));

        // Store the data.
        if (in_array(false, $result, true) || !$table->store()) {
            $this->setError($table->getError());

            return false;
        }

        // Trigger the after save event.
        $app->triggerEvent($this->event_after_save, array($context, &$table, $isNew));

        // Rebuild the tree path.
        if (!$table->rebuildPath($table->id)) {
            $this->setError($table->getError());

            return false;
        }

        $this->setState('item.id', $table->id);
        $this->setState('item.menutype', $table->menutype);

        // Load associated menu items
        $assoc = JLanguageAssociations::isEnabled();

        if ($assoc) {
            // Adding self to the association
            $associations = $data['associations'];

            // Unset any invalid associations
            $associations = Joomla\Utilities\ArrayHelper::toInteger($associations);

            foreach ($associations as $tag => $id) {
                if (!$id) {
                    unset($associations[$tag]);
                }
            }

            // Detecting all item menus
            $all_language = $table->language === '*';

            if ($all_language && !empty($associations)) {
                JError::raiseNotice(403, JText::_('COM_MENUS_ERROR_ALL_LANGUAGE_ASSOCIATED'));
            }

            $associations[$table->language] = $table->id;

            // Deleting old association for these items
            $db = $this->getDbo();
            $query = $db->getQuery(true)
                ->delete('#__associations')
                ->where('context=' . $db->quote($this->associationsContext))
                ->where('id IN (' . implode(',', $associations) . ')');
            $db->setQuery($query);

            try {
                $db->execute();
            } catch (RuntimeException $e) {
                $this->setError($e->getMessage());

                return false;
            }

            if (!$all_language && count($associations) > 1) {
                // Adding new association for these items
                $key = md5(json_encode($associations));
                $query->clear()
                    ->insert('#__associations');

                foreach ($associations as $id) {
                    $query->values(((int)$id) . ',' . $db->quote($this->associationsContext) . ',' . $db->quote($key));
                }

                $db->setQuery($query);

                try {
                    $db->execute();
                } catch (RuntimeException $e) {
                    $this->setError($e->getMessage());

                    return false;
                }
            }
        }

        // Clean the cache
        $this->cleanCache();

        if (isset($data['link'])) {
            $base = JUri::base();
            $juri = JUri::getInstance($base . $data['link']);
            $option = $juri->getVar('option');

            // Clean the cache
            parent::cleanCache($option);
        }

        return true;
    }

    /**
     * Method to save the reordered nested set tree.
     * First we save the new order values in the lft values of the changed ids.
     * Then we invoke the table rebuild to implement the new ordering.
     *
     * @param array $idArray   Rows identifiers to be reordered
     * @param array $lft_array Lft values of rows to be reordered
     *
     * @return boolean false on failuer or error, true otherwise.
     *
     * @since 1.6
     */
    public function saveorder($idArray = null, $lft_array = null)
    {
        // Get an instance of the table object.
        $table = $this->getTable();

        if (!$table->saveorder($idArray, $lft_array)) {
            $this->setError($table->getError());

            return false;
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to change the home state of one or more items.
     *
     * @param array   $pks   A list of the primary keys to change.
     * @param integer $value The value of the home state.
     *
     * @return boolean  True on success.
     *
     * @since 1.6
     */
    public function setHome(&$pks, $value = 1)
    {
        $table = $this->getTable();
        $pks = (array)$pks;

        $languages = array();
        $onehome = false;

        // Remember that we can set a home page for different languages,
        // so we need to loop through the primary key array.
        foreach ($pks as $i => $pk) {
            if ($table->load($pk)) {
                if (!array_key_exists($table->language, $languages)) {
                    $languages[$table->language] = true;

                    if ($table->home === $value) {
                        unset($pks[$i]);
                        JError::raiseNotice(403, JText::_('COM_MENUS_ERROR_ALREADY_HOME'));
                    } else {
                        $table->home = $value;

                        if ($table->language === '*') {
                            $table->published = 1;
                        }

                        if (!$this->canSave()) {
                            // Prune items that you can't change.
                            unset($pks[$i]);
                            JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
                        } elseif (!$table->check()) {
                            // Prune the items that failed pre-save checks.
                            unset($pks[$i]);
                            JError::raiseWarning(403, $table->getError());
                        } elseif (!$table->store()) {
                            // Prune the items that could not be stored.
                            unset($pks[$i]);
                            JError::raiseWarning(403, $table->getError());
                        }
                    }
                } else {
                    unset($pks[$i]);

                    if (!$onehome) {
                        $onehome = true;
                        JError::raiseNotice(403, JText::sprintf('COM_MENUS_ERROR_ONE_HOME'));
                    }
                }
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to change the published state of one or more records.
     *
     * @param array   $pks   A list of the primary keys to change.
     * @param integer $value The value of the published state.
     *
     * @return boolean  True on success.
     *
     * @since 1.6
     */
    public function publish(&$pks, $value = 1)
    {
        $table = $this->getTable();
        $pks = (array)$pks;

        // Default menu item existence checks.
        if ($value !== 1) {
            foreach ($pks as $i => $pk) {
                if ($table->load($pk) && $table->home && $table->language === '*') {
                    // Prune items that you can't change.
                    JError::raiseWarning(403, JText::_('JLIB_DATABASE_ERROR_MENU_UNPUBLISH_DEFAULT_HOME'));
                    unset($pks[$i]);
                    break;
                }
            }
        }

        // Clean the cache
        $this->cleanCache();

        // Ensure that previous checks doesn't empty the array
        if (empty($pks)) {
            return true;
        }

        return parent::publish($pks, $value);
    }

    /**
     * Method to change the title & alias.
     *
     * @param integer $parent_id The id of the parent.
     * @param string  $alias     The alias.
     * @param string  $title     The title.
     *
     * @return array  Contains the modified title and alias.
     *
     * @since 1.6
     */
    protected function generateNewTitle($parent_id, $alias, $title)
    {
        // Alter the title & alias
        $table = $this->getTable();

        while ($table->load(array('alias' => $alias, 'parent_id' => $parent_id))) {
            if ($title === $table->title) {
                $title = JString::increment($title);
            }

            $alias = JString::increment($alias, 'dash');
        }

        return array($title, $alias);
    }

    /**
     * Custom clean the cache
     *
     * @param string  $group     Cache group name.
     * @param integer $client_id Application client id.
     *
     * @return void
     * @since  1.6
     */
    protected function cleanCache($group = null, $client_id = 0)
    {
        parent::cleanCache('com_modules');
        parent::cleanCache('mod_menu');
    }
}
