<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Finder.Newsfeeds
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;
use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Component\Newsfeeds\Site\Helper\RouteHelper;
use Joomla\Database\DatabaseQuery;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Smart Search adapter for Joomla Newsfeeds.
 *
 * @since  2.5
 */
class PlgFinderNewsfeeds extends Adapter
{
    /**
     * The plugin identifier.
     *
     * @var    string
     * @since  2.5
     */
    protected $context = 'Newsfeeds';

    /**
     * The extension name.
     *
     * @var    string
     * @since  2.5
     */
    protected $extension = 'com_newsfeeds';

    /**
     * The sublayout to use when rendering the results.
     *
     * @var    string
     * @since  2.5
     */
    protected $layout = 'newsfeed';

    /**
     * The type of content that the adapter indexes.
     *
     * @var    string
     * @since  2.5
     */
    protected $type_title = 'News Feed';

    /**
     * The table name.
     *
     * @var    string
     * @since  2.5
     */
    protected $table = '#__newsfeeds';

    /**
     * The field the published state is stored in.
     *
     * @var    string
     * @since  2.5
     */
    protected $state_field = 'published';

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Method to update the item link information when the item category is
     * changed. This is fired when the item category is published or unpublished
     * from the list view.
     *
     * @param   string   $extension  The extension whose category has been updated.
     * @param   array    $pks        An array of primary key ids of the content that has changed state.
     * @param   integer  $value      The value of the state that the content has been changed to.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onFinderCategoryChangeState($extension, $pks, $value)
    {
        // Make sure we're handling com_newsfeeds categories.
        if ($extension === 'com_newsfeeds') {
            $this->categoryStateChange($pks, $value);
        }
    }

    /**
     * Method to remove the link information for items that have been deleted.
     *
     * @param   string  $context  The context of the action being performed.
     * @param   Table   $table    A Table object containing the record to be deleted.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  Exception on database error.
     */
    public function onFinderAfterDelete($context, $table): void
    {
        if ($context === 'com_newsfeeds.newsfeed') {
            $id = $table->id;
        } elseif ($context === 'com_finder.index') {
            $id = $table->link_id;
        } else {
            return;
        }

        // Remove the item from the index.
        $this->remove($id);
    }

    /**
     * Smart Search after save content method.
     * Reindexes the link information for a newsfeed that has been saved.
     * It also makes adjustments if the access level of a newsfeed item or
     * the category to which it belongs has changed.
     *
     * @param   string   $context  The context of the content passed to the plugin.
     * @param   Table    $row      A Table object.
     * @param   boolean  $isNew    True if the content has just been created.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  Exception on database error.
     */
    public function onFinderAfterSave($context, $row, $isNew): void
    {
        // We only want to handle newsfeeds here.
        if ($context === 'com_newsfeeds.newsfeed') {
            // Check if the access levels are different.
            if (!$isNew && $this->old_access != $row->access) {
                // Process the change.
                $this->itemAccessChange($row);
            }

            // Reindex the item.
            $this->reindex($row->id);
        }

        // Check for access changes in the category.
        if ($context === 'com_categories.category') {
            // Check if the access levels are different.
            if (!$isNew && $this->old_cataccess != $row->access) {
                $this->categoryAccessChange($row);
            }
        }
    }

    /**
     * Smart Search before content save method.
     * This event is fired before the data is actually saved.
     *
     * @param   string   $context  The context of the content passed to the plugin.
     * @param   Table    $row      A Table object.
     * @param   boolean  $isNew    True if the content is just about to be created.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     * @throws  Exception on database error.
     */
    public function onFinderBeforeSave($context, $row, $isNew)
    {
        // We only want to handle newsfeeds here.
        if ($context === 'com_newsfeeds.newsfeed') {
            // Query the database for the old access level if the item isn't new.
            if (!$isNew) {
                $this->checkItemAccess($row);
            }
        }

        // Check for access levels from the category.
        if ($context === 'com_categories.category') {
            // Query the database for the old access level if the item isn't new.
            if (!$isNew) {
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
        // We only want to handle newsfeeds here.
        if ($context === 'com_newsfeeds.newsfeed') {
            $this->itemStateChange($pks, $value);
        }

        // Handle when the plugin is disabled.
        if ($context === 'com_plugins.plugin' && $value === 0) {
            $this->pluginDisable($pks);
        }
    }

    /**
     * Method to index an item. The item must be a Result object.
     *
     * @param   Result  $item  The item to index as a Result object.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  Exception on database error.
     */
    protected function index(Result $item)
    {
        // Check if the extension is enabled.
        if (ComponentHelper::isEnabled($this->extension) === false) {
            return;
        }

        $item->setLanguage();

        // Initialize the item parameters.
        $item->params = new Registry($item->params);

        $item->metadata = new Registry($item->metadata);

        // Create a URL as identifier to recognise items again.
        $item->url = $this->getUrl($item->id, $this->extension, $this->layout);

        // Build the necessary route and path information.
        $item->route = RouteHelper::getNewsfeedRoute($item->slug, $item->catslug, $item->language);

        /*
         * Add the metadata processing instructions based on the newsfeeds
         * configuration parameters.
         */

        // Add the meta author.
        $item->metaauthor = $item->metadata->get('author');

        // Handle the link to the metadata.
        $item->addInstruction(Indexer::META_CONTEXT, 'link');

        $item->addInstruction(Indexer::META_CONTEXT, 'metakey');
        $item->addInstruction(Indexer::META_CONTEXT, 'metadesc');
        $item->addInstruction(Indexer::META_CONTEXT, 'metaauthor');
        $item->addInstruction(Indexer::META_CONTEXT, 'author');
        $item->addInstruction(Indexer::META_CONTEXT, 'created_by_alias');

        // Add the type taxonomy data.
        $item->addTaxonomy('Type', 'News Feed');

        // Add the category taxonomy data.
        $categories = Categories::getInstance('com_newsfeeds', ['published' => false, 'access' => false]);
        $category = $categories->get($item->catid);

        // Category does not exist, stop here
        if (!$category) {
            return;
        }

        $item->addNestedTaxonomy('Category', $category, $this->translateState($category->published), $category->access, $category->language);

        // Add the language taxonomy data.
        $item->addTaxonomy('Language', $item->language);

        // Get content extras.
        Helper::getContentExtras($item);

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
        return true;
    }

    /**
     * Method to get the SQL query used to retrieve the list of content items.
     *
     * @param   mixed  $query  A DatabaseQuery object or null.
     *
     * @return  DatabaseQuery  A database object.
     *
     * @since   2.5
     */
    protected function getListQuery($query = null)
    {
        $db = $this->db;

        // Check if we can use the supplied SQL query.
        $query = $query instanceof DatabaseQuery ? $query : $db->getQuery(true)
            ->select('a.id, a.catid, a.name AS title, a.alias, a.link AS link')
            ->select('a.published AS state, a.ordering, a.created AS start_date, a.params, a.access')
            ->select('a.publish_up AS publish_start_date, a.publish_down AS publish_end_date')
            ->select('a.metakey, a.metadesc, a.metadata, a.language')
            ->select('a.created_by, a.created_by_alias, a.modified, a.modified_by')
            ->select('c.title AS category, c.published AS cat_state, c.access AS cat_access');

        // Handle the alias CASE WHEN portion of the query.
        $case_when_item_alias = ' CASE WHEN ';
        $case_when_item_alias .= $query->charLength('a.alias', '!=', '0');
        $case_when_item_alias .= ' THEN ';
        $a_id = $query->castAsChar('a.id');
        $case_when_item_alias .= $query->concatenate([$a_id, 'a.alias'], ':');
        $case_when_item_alias .= ' ELSE ';
        $case_when_item_alias .= $a_id . ' END as slug';
        $query->select($case_when_item_alias);

        $case_when_category_alias = ' CASE WHEN ';
        $case_when_category_alias .= $query->charLength('c.alias', '!=', '0');
        $case_when_category_alias .= ' THEN ';
        $c_id = $query->castAsChar('c.id');
        $case_when_category_alias .= $query->concatenate([$c_id, 'c.alias'], ':');
        $case_when_category_alias .= ' ELSE ';
        $case_when_category_alias .= $c_id . ' END as catslug';
        $query->select($case_when_category_alias)

            ->from('#__newsfeeds AS a')
            ->join('LEFT', '#__categories AS c ON c.id = a.catid');

        return $query;
    }
}
