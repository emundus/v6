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
 *
 * Source code from Joomla's content search plugin
 */

//-- No direct access
defined('_JEXEC') || die('=;)');

require_once JPATH_SITE . '/components/com_content/router.php';

/**
 * Class PlgSearchDropfiles
 */
class PlgSearchDropfiles extends JPlugin
{
    /**
     * PlgSearchDropfiles constructor.
     *
     * @param object $subject Subject
     * @param array  $config  Configurations
     *
     * @return PlgSearchDropfiles
     * @since  version
     */
    public function __construct(&$subject, $config = array())
    {
        JLoader::register('DropfilesBase', JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesBase.php');
        DropfilesBase::loadLanguage();
        return parent::__construct($subject, $config);
    }

    /**
     * Determine areas searchable by this plugin.
     *
     * @return array  An array of search areas.
     *
     * @since 1.6
     */
    public function onContentSearchAreas()
    {
        static $areas = array(
            'files' => 'COM_DROPFILES_PLUGIN_SEARCH_FILES'
        );
        return $areas;
    }

    /**
     * Search content (articles).
     * The SQL must return the following fields that are used in a common display
     * routine: href, title, section, created, text, browsernav.
     *
     * @param string $text     Target search string.
     * @param string $phrase   Matching option (possible values: exact|any|all).  Default is "any".
     * @param string $ordering Ordering option (possible values: newest|oldest|popular|alpha|category).
     * Default is "newest".
     * @param mixed  $areas    An array if the search it to be restricted to areas or null to search all areas.
     *
     * @return array  Search results.
     *
     * @since 1.6
     */
    public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
    {
        $db = JFactory::getDbo();
        $app = JFactory::getApplication();
        $user = JFactory::getUser();
        $groups = implode(',', $user->getAuthorisedViewLevels());
        $tag = JFactory::getLanguage()->getTag();

        require_once JPATH_SITE . '/components/com_content/helpers/route.php';
        require_once JPATH_ADMINISTRATOR . '/components/com_search/helpers/search.php';

        $searchText = $text;
        if (is_array($areas)) {
            if (!array_intersect($areas, array_keys($this->onContentSearchAreas()))) {
                return array();
            }
        }

        $plugin = JPluginHelper::getPlugin('search', 'content');
        $params = new JRegistry;
        $params->loadString($plugin->params);

        $sContent = $params->get('search_content', 1);
        $sArchived = $params->get('search_archived', 1);
        $limit = $params->def('search_limit', 50);

        $nullDate = $db->getNullDate();
        $date = JFactory::getDate();
        $now = $date->toSql();

        $text = trim($text);
        if ($text === '') {
            return array();
        }

        //First search on Dropfiles
        switch ($phrase) {
            case 'exact':
                $text = $db->quote('%' . $db->escape($text, true) . '%', false);
                $wheres2 = array();
                $wheres2[] = 'f.title LIKE ' . $text;
                $wheres2[] = 'f.description LIKE ' . $text;
                $where = '(' . implode(') OR (', $wheres2) . ')';
                break;

            case 'all':
            case 'any':
            default:
                $words = explode(' ', $text);
                $wheres = array();
                foreach ($words as $word) {
                    $word = $db->quote('%' . $db->escape($word, true) . '%', false);
                    $wheres2 = array();
                    $wheres2[] = 'f.title LIKE ' . $word;
                    $wheres2[] = 'f.description LIKE ' . $word;
                    $wheres[] = implode(' OR ', $wheres2);
                }
                $where = '(' . implode(($phrase === 'all' ? ') AND (' : ') OR ('), $wheres) . ')';
                break;
        }

        $queryF = $db->getQuery(true);
        $queryF->select('f.id, f.catid')
            ->from('#__dropfiles_files AS f')
            ->join('INNER', '#__categories AS c ON c.id=f.catid')
            ->where(
                '(' . $where . ') AND c.published = 1 AND c.access IN (' . $groups . ') '
            );
        $db->setQuery($queryF);
        $listF = $db->loadObjectList();

        if (empty($listF)) {
            //nothing to search in articles
            return array();
        }

        $wheres = array();

        foreach ($listF as $file) {
            $wheres[] = 'a.introtext LIKE \'%data-dropfilescategory="' . $file->catid . '"%\'';
            $wheres[] = 'a.fulltext LIKE \'%data-dropfilesfile="' . $file->id . '"%\'';
            $wheres[] = 'a.introtext LIKE \'%data-dropfilescategory="' . $file->catid . '"%\'';
            $wheres[] = 'a.fulltext LIKE \'%data-dropfilesfile="' . $file->id . '"%\'';
        }
        $where = '(' . implode(') OR (', $wheres) . ')';

        //Search into content
        switch ($ordering) {
            case 'oldest':
                $order = 'a.created ASC';
                break;

            case 'popular':
                $order = 'a.hits DESC';
                break;

            case 'alpha':
                $order = 'a.title ASC';
                break;

            case 'category':
                $order = 'c.title ASC, a.title ASC';
                break;

            case 'newest':
            default:
                $order = 'a.created DESC';
                break;
        }

        $rows = array();
        $query = $db->getQuery(true);

        // Search articles.
        if ($sContent && $limit > 0) {
            $query->clear();

            // SQLSRV changes.
            $case_when = ' CASE WHEN ';
            $case_when .= $query->charLength('a.alias', '!=', '0');
            $case_when .= ' THEN ';
            $a_id = $query->castAsChar('a.id');
            $case_when .= $query->concatenate(array($a_id, 'a.alias'), ':');
            $case_when .= ' ELSE ';
            $case_when .= $a_id . ' END as slug';
            $case_when1 = ' CASE WHEN ';
            $case_when1 .= $query->charLength('c.alias', '!=', '0');
            $case_when1 .= ' THEN ';
            $c_id = $query->castAsChar('c.id');
            $case_when1 .= $query->concatenate(array($c_id, 'c.alias'), ':');
            $case_when1 .= ' ELSE ';
            $case_when1 .= $c_id . ' END as catslug';

            $query->select('a.title AS title, a.metadesc, a.metakey, a.created AS created')
                ->select($query->concatenate(array('a.introtext', 'a.fulltext')) . ' AS text')
                ->select('c.title AS section, ' . $case_when . ',' . $case_when1 . ', \'2\' AS browsernav')
                ->from('#__content AS a')
                ->join('INNER', '#__categories AS c ON c.id=a.catid')
                ->where(
                    '(' . $where . ') AND a.state=1 AND c.published = 1 AND a.access IN (' . $groups . ') '
                    . 'AND c.access IN (' . $groups . ') '
                    . 'AND (a.publish_up = ' . $db->quote($nullDate) . ' OR a.publish_up <= ' . $db->quote($now) . ') '
                    . 'AND (a.publish_down = ' . $db->quote($nullDate) . ' OR a.publish_down >= '
                    . $db->quote($now) . ')'
                )
                ->group('a.id, a.title, a.metadesc, a.metakey, a.created, a.introtext,
                 a.fulltext, c.title, a.alias, c.alias, c.id')
                ->order($order);
            // Filter by language.
            if ($app->isClient('site') && JLanguageMultilang::isEnabled()) {
                $query->where('a.language in (' . $db->quote($tag) . ',' . $db->quote('*') . ')')
                    ->where('c.language in (' . $db->quote($tag) . ',' . $db->quote('*') . ')');
            }
            $db->setQuery($query, 0, $limit);
            $list = $db->loadObjectList();
            $limit -= count($list);
            if (isset($list)) {
                foreach ($list as $key => $item) {
                    $list[$key]->href = ContentHelperRoute::getArticleRoute($item->slug, $item->catslug);
                }
            }
            $rows[] = $list;
        }

        // Search archived content.
        if ($sArchived && $limit > 0) {
            $query->clear();

            // SQLSRV changes.
            $case_when = ' CASE WHEN ';
            $case_when .= $query->charLength('a.alias', '!=', '0');
            $case_when .= ' THEN ';
            $a_id = $query->castAsChar('a.id');
            $case_when .= $query->concatenate(array($a_id, 'a.alias'), ':');
            $case_when .= ' ELSE ';
            $case_when .= $a_id . ' END as slug';

            $case_when1 = ' CASE WHEN ';
            $case_when1 .= $query->charLength('c.alias', '!=', '0');
            $case_when1 .= ' THEN ';
            $c_id = $query->castAsChar('c.id');
            $case_when1 .= $query->concatenate(array($c_id, 'c.alias'), ':');
            $case_when1 .= ' ELSE ';
            $case_when1 .= $c_id . ' END as catslug';

            $query->select(
                'a.title AS title, a.metadesc, a.metakey, a.created AS created, '
                . $query->concatenate(array('a.introtext', 'a.fulltext')) . ' AS text,'
                . $case_when . ',' . $case_when1 . ', '
                . 'c.title AS section, \'2\' AS browsernav'
            );
            // .'CONCAT_WS("/", c.title) AS section, \'2\' AS browsernav' );
            $query->from('#__content AS a')
                ->join('INNER', '#__categories AS c ON c.id=a.catid AND c.access IN (' . $groups . ')')
                ->where(
                    '(' . $where . ') AND a.state = 2 AND c.published = 1 AND a.access IN (' . $groups
                    . ') AND c.access IN (' . $groups . ') '
                    . 'AND (a.publish_up = ' . $db->quote($nullDate) . ' OR a.publish_up <= ' . $db->quote($now) . ') '
                    . 'AND (a.publish_down = ' . $db->quote($nullDate) . ' OR a.publish_down >= '
                    . $db->quote($now) . ')'
                )
                ->order($order);
            // Filter by language.
            if ($app->isClient('site') && JLanguageMultilang::isEnabled()) {
                $query->where('a.language in (' . $db->quote($tag) . ',' . $db->quote('*') . ')')
                    ->where('c.language in (' . $db->quote($tag) . ',' . $db->quote('*') . ')');
            }
            $db->setQuery($query, 0, $limit);
            $list3 = $db->loadObjectList();

            // Find an itemid for archived to use if there isn't another one.
            $item = $app->getMenu()->getItems('link', 'index.php?option=com_content&view=archive', true);
            $itemid = isset($item->id) ? '&Itemid=' . $item->id : '';

            if (isset($list3)) {
                foreach ($list3 as $key => $item) {
                    $date = JFactory::getDate($item->created);

                    $created_month     = $date->format('n');
                    $created_year      = $date->format('Y');
                    $url_archive       = 'index.php?option=com_content&view=archive&year=';
                    $list3[$key]->href = JRoute::_($url_archive . $created_year . '&month=' . $created_month . $itemid);
                }
            }
            $rows[] = $list3;
        }
        $results = array();
        if (count($rows)) {
            foreach ($rows as $row) {
                $new_row = array();
                foreach ($row as $article) {
                    // Get the dispatcher and load the users plugins
                    JPluginHelper::importPlugin('content');
                    // Trigger the data preparation event.
                    $app = JFactory::getApplication();
                    $app->triggerEvent('onDropfilesContentPrepare', array('com_content.finder', &$article));
                    if (SearchHelper::checkNoHTML(
                        $article,
                        $searchText,
                        array('text', 'title', 'metadesc', 'metakey')
                    )) {
                        $new_row[] = $article;
                    }
                }
                $results = array_merge($results, (array)$new_row);
            }
        }
        return $results;
    }
}
