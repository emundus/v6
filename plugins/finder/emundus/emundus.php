<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Finder.Content
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JLoader::register('FinderIndexerAdapter', JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php');

/**
 * Smart Search adapter for com_content.
 *
 * @since  2.5
 */
class PlgFinderEmundus extends FinderIndexerAdapter
{
    /**
     * The plugin identifier.
     *
     * @var    string
     * @since  2.5
     */
    protected $context = 'Emundus';

    /**
     * The extension name.
     *
     * @var    string
     * @since  2.5
     */
    protected $extension = 'com_emundus';

    /**
     * The sublayout to use when rendering the results.
     *
     * @var    string
     * @since  2.5
     */
    protected $layout = 'application';

    /**
     * The type of content that the adapter indexes.
     *
     * @var    string
     * @since  2.5
     */
    protected $type_title = 'Emundus';

    /**
     * The table name.
     *
     * @var    string
     * @since  2.5
     */
    protected $table = '#__emundus_campaign_candidature';

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * The field the published state is stored in.
     *
     * @var    string
     * @since  2.5
     */
    protected $state_field = 'published';

    /**
     * Method to update the item link information when the item category is
     * changed. This is fired when the item category is published or unpublished
     * from the list view.
     *
     * @param   string   $extension  The extension whose category has been updated.
     * @param   array    $pks        A list of primary key ids of the content that has changed state.
     * @param   integer  $value      The value of the state that the content has been changed to.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onFinderCategoryChangeState($extension, $pks, $value)
    {
        // Make sure we're handling com_content categories.
        if ($extension === 'com_emundus')
        {
            $this->categoryStateChange($pks, $value);
        }
    }

    /**
     * Method to remove the link information for items that have been deleted.
     *
     * @param   string  $context  The context of the action being performed.
     * @param   JTable  $table    A JTable object containing the record to be deleted
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     * @throws  Exception on database error.
     */
    public function onFinderAfterDelete($context, $table)
    {
        if ($context === 'com_emundus.application')
        {
            $id = $table->id;
        }
        elseif ($context === 'com_finder.index')
        {
            $id = $table->link_id;
        }
        else
        {
            return true;
        }

        // Remove item from the index.
        return $this->remove($id);
    }

    /**
     * Smart Search after save content method.
     * Reindexes the link information for an article that has been saved.
     * It also makes adjustments if the access level of an item or the
     * category to which it belongs has changed.
     *
     * @param   string   $context  The context of the content passed to the plugin.
     * @param   JTable   $row      A JTable object.
     * @param   boolean  $isNew    True if the content has just been created.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     * @throws  Exception on database error.
     */
    public function onFinderAfterSave($context, $row, $isNew)
    {
        // We only want to handle articles here.
        if ($context === 'com_emundus.application')
        {
            // Check if the access levels are different.
            if (!$isNew && $this->old_access != $row->access)
            {
                // Process the change.
                $this->itemAccessChange($row);
            }

            // Reindex the item.
            $this->reindex($row->id);
        }

        // Check for access changes in the category.
        if ($context === 'com_categories.category')
        {
            // Check if the access levels are different.
            if (!$isNew && $this->old_cataccess != $row->access)
            {
                $this->categoryAccessChange($row);
            }
        }

        return true;
    }

    /**
     * Smart Search before content save method.
     * This event is fired before the data is actually saved.
     *
     * @param   string   $context  The context of the content passed to the plugin.
     * @param   JTable   $row      A JTable object.
     * @param   boolean  $isNew    If the content is just about to be created.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     * @throws  Exception on database error.
     */
    public function onFinderBeforeSave($context, $row, $isNew)
    {
        // We only want to handle articles here.
        if ($context === 'com_emundus.application')
        {
            // Query the database for the old access level if the item isn't new.
            if (!$isNew)
            {
                $this->checkItemAccess($row);
            }
        }

        // Check for access levels from the category.
        if ($context === 'com_categories.category')
        {
            // Query the database for the old access level if the item isn't new.
            if (!$isNew)
            {
                $this->checkCategoryAccess($row);
            }
        }

        return true;
    }

    /**
     * Method to update the link information for items that have been changed
     * from outside the edit screen. This is fired when the item is published,
     * unpublished, archived, or unarchived from the list view.
     *
     * @param   string   $context  The context for the content passed to the plugin.
     * @param   array    $pks      An array of primary key ids of the content that has changed state.
     * @param   integer  $value    The value of the state that the content has been changed to.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onFinderChangeState($context, $pks, $value)
    {
        // We only want to handle articles here.
        if ($context === 'com_emundus.application')
        {
            $this->itemStateChange($pks, $value);
        }

        // Handle when the plugin is disabled.
        if ($context === 'com_plugins.plugin' && $value === 0)
        {
            $this->pluginDisable($pks);
        }
    }

    /**
     * Method to index an item. The item must be a FinderIndexerResult object.
     *
     * @param   FinderIndexerResult  $item    The item to index as a FinderIndexerResult object.
     * @param   string               $format  The item format.  Not used.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  Exception on database error.
     */
    protected function index(FinderIndexerResult $item, $format = 'html')
    {
        $item->setLanguage();

        // Check if the extension is enabled.
        if (JComponentHelper::isEnabled($this->extension) === false)
        {
            return;
        }

        $item->context = 'com_emundus.application';

        // Initialise the item parameters.
        $registry = new Registry($item->params);
        $item->params = clone JComponentHelper::getParams('com_emundus', true);
        $item->params->merge($registry);

        $item->metadata = new Registry($item->metadata);

        // Trigger the onContentPrepare event.
        $item->summary = FinderIndexerHelper::prepareContent($item->summary, $item->params, $item);

        // Build the necessary route and path information.
        $item->url = 'dossiers#'.$item->summary.'|open';
        $item->route = 'dossiers#'.$item->summary.'|open';
        $item->path = 'dossiers#'.$item->summary.'|open';
        $item->state = 1;
        $item->access = 1;

        // Get the menu title if it exists.
        $title = $item->name;

        // Adjust the title if necessary.
        if (!empty($title))
        {
            $item->title = $title;
        }

        // Add the meta author.
        $item->metaauthor = $item->metadata->get('author');

        // Add the metadata processing instructions.
        $item->addInstruction(FinderIndexer::META_CONTEXT, 'author');

        // Translate the state. Articles should only be published if the category is published.
        $item->state = $this->translateState($item->state, $item->cat_state);

        // Add the type taxonomy data.
        $item->addTaxonomy('Type', 'Emundus');

        // Add the author taxonomy data.
        if (!empty($item->author) || !empty($item->created_by_alias))
        {
            $item->addTaxonomy('Author', $item->author);
        }

        $item->addTaxonomy('Status', $item->status);

        // Get content extras.
        FinderIndexerHelper::getContentExtras($item);

        // Index the item.
        $this->indexer->index($item);
    }

    /**
     * Method to setup the indexer to be run.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     */
    protected function setup()
    {
        // Load dependent classes.
        JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');

        return true;
    }

    /**
     * Method to get the SQL query used to retrieve the list of content items.
     *
     * @param   mixed  $query  A JDatabaseQuery object or null.
     *
     * @return  JDatabaseQuery  A database object.
     *
     * @since   2.5
     */
    protected function getListQuery($query = null)
    {
        $db = JFactory::getDbo();

        // Check if we can use the supplied SQL query.
        $query = $query instanceof JDatabaseQuery ? $query : $db->getQuery(true)
            ->select('cc.id, u.name, cc.fnum AS summary, ss.value as status')
            ->select('u.name AS author')
            ->from('#__emundus_campaign_candidature AS cc')
            ->join('LEFT', '#__users AS u ON u.id = cc.applicant_id')
            ->join('LEFT', '#__emundus_setup_status AS ss ON ss.step = cc.status');

        return $query;
    }
}
