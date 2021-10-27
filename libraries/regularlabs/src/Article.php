<?php
/**
 * @package         Regular Labs Library
 * @version         21.9.16879
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2021 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\Registry\Registry as JRegistry;
use RegularLabs\Library\CacheNew as Cache;

jimport('joomla.filesystem.file');

/**
 * Class Article
 * @package RegularLabs\Library
 */
class Article
{
	static $articles = [];

	/**
	 * Method to get article data.
	 *
	 * @param integer $id              The id, alias or title of the article.
	 * @param boolean $get_unpublished Whether to also return the article if it is not published
	 * @param array   $selects         Option array of stuff to select. Note: requires correct table alias prefixes
	 *
	 * @return  object|boolean Menu item data object on success, boolean false
	 */
	public static function get($id = null, $get_unpublished = false, $selects = [])
	{
		$id = ($id ?? null) ?: (int) self::getId();

		$cache = new Cache([__METHOD__, $id, $get_unpublished, $selects]);

		if ($cache->exists())
		{
			return $cache->get();
		}

		$db   = JFactory::getDbo();
		$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

		$custom_selects = ! empty($selects);

		$query = $db->getQuery(true)
			->select($custom_selects ? $selects :
				[
					'a.id', 'a.asset_id', 'a.title', 'a.alias', 'a.introtext', 'a.fulltext',
					'a.state', 'a.catid', 'a.created', 'a.created_by', 'a.created_by_alias',
					// Use created if modified is 0
					'CASE WHEN a.modified = ' . $db->quote($db->getNullDate()) . ' THEN a.created ELSE a.modified END as modified',
					'a.modified_by', 'a.checked_out', 'a.checked_out_time', 'a.publish_up', 'a.publish_down',
					'a.images', 'a.urls', 'a.attribs', 'a.version', 'a.ordering',
					'a.metakey', 'a.metadesc', 'a.access', 'a.hits', 'a.metadata', 'a.featured', 'a.language', 'a.xreference',
				]
			)
			->from($db->quoteName('#__content', 'a'));

		if ( ! is_numeric($id))
		{
			$query->where('(' .
				$db->quoteName('a.title') . ' = ' . $db->quote($id)
				. ' OR ' .
				$db->quoteName('a.alias') . ' = ' . $db->quote($id)
				. ')');
		}
		else
		{
			$query->where($db->quoteName('a.id') . ' = ' . (int) $id);
		}

		// Join on category table.
		if ( ! $custom_selects)
		{
			$query->select([
				$db->quoteName('c.title', 'category_title'),
				$db->quoteName('c.alias', 'category_alias'),
				$db->quoteName('c.access', 'category_access'),
				$db->quoteName('c.lft', 'category_lft'),
				$db->quoteName('c.lft', 'category_ordering'),
			]);
		}
		$query->innerJoin($db->quoteName('#__categories', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
			->where($db->quoteName('c.published') . ' > 0');

		// Join on user table.
		if ( ! $custom_selects)
		{
			$query->select($db->quoteName('u.name', 'author'));
		}
		$query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('a.created_by'));

		// Join over the categories to get parent category titles
		if ( ! $custom_selects)
		{
			$query->select([
				$db->quoteName('parent.title', 'parent_title'),
				$db->quoteName('parent.id', 'parent_id'),
				$db->quoteName('parent.path', 'parent_route'),
				$db->quoteName('parent.alias', 'parent_alias'),
			]);
		}
		$query->join('LEFT', $db->quoteName('#__categories', 'parent') . ' ON ' . $db->quoteName('parent.id') . ' = ' . $db->quoteName('c.parent_id'));

		// Join on voting table
		if ( ! $custom_selects)
		{
			$query->select([
				'ROUND(v.rating_sum / v.rating_count, 0) AS rating',
				$db->quoteName('v.rating_count', 'rating_count'),
			]);
		}
		$query->join('LEFT', $db->quoteName('#__content_rating', 'v') . ' ON ' . $db->quoteName('v.content_id') . ' = ' . $db->quoteName('a.id'));

		if ( ! $get_unpublished
			&& ( ! $user->authorise('core.edit.state', 'com_content'))
			&& ( ! $user->authorise('core.edit', 'com_content'))
		)
		{
			// Filter by start and end dates.
			$nullDate = $db->quote($db->getNullDate());
			$date     = JFactory::getDate();

			$nowDate = $db->quote($date->toSql());

			$query->where($db->quoteName('a.state') . ' = 1')
				->where('(' . $db->quoteName('a.publish_up') . ' IS NULL OR ' . $db->quoteName('a.publish_up') . ' = ' . $nullDate . ' OR ' . $db->quoteName('a.publish_up') . ' <= ' . $nowDate . ')')
				->where('(' . $db->quoteName('a.publish_down') . ' IS NULL OR ' . $db->quoteName('a.publish_down') . ' = ' . $nullDate . ' OR ' . $db->quoteName('a.publish_down') . ' >= ' . $nowDate . ')');
		}

		$db->setQuery($query);

		$data = $db->loadObject();

		if (empty($data))
		{
			return false;
		}

		if (isset($data->attribs))
		{
			// Convert parameter field to object.
			$data->params = new JRegistry($data->attribs);
		}

		if (isset($data->metadata))
		{
			// Convert metadata field to object.
			$data->metadata = new JRegistry($data->metadata);
		}

		return $cache->set($data);
	}

	/**
	 * Gets the current article id based on url data
	 */
	public static function getId()
	{
		$input = JFactory::getApplication()->input;

		$id = $input->getInt('id');

		if ( ! $id
			|| ! (
				($input->get('option') == 'com_content' && $input->get('view') == 'article')
				|| ($input->get('option') == 'com_flexicontent' && $input->get('view') == 'item')
			)
		)
		{
			return false;
		}

		return $id;
	}

	public static function getPageNumber(&$all_pages, $search_string)
	{
		if (is_string($all_pages))
		{
			$all_pages = self::getPages($all_pages);
		}

		if (count($all_pages) < 2)
		{
			return 0;
		}

		foreach ($all_pages as $i => $page_text)
		{
			if ($i % 2)
			{
				continue;
			}

			if (strpos($page_text, $search_string) === false)
			{
				continue;
			}

			$all_pages[$i] = StringHelper::replaceOnce($search_string, '---', $page_text);

			return $i / 2;
		}

		return 0;
	}

	public static function getPages($string)
	{
		if (empty($string))
		{
			return [''];
		}

		return preg_split('#(<hr class="system-pagebreak" .*?>)#s', $string, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	}

	/**
	 * Passes the different article parts through the given plugin method
	 *
	 * @param object $article
	 * @param string $context
	 * @param object $class
	 * @param string $method
	 * @param array  $params
	 * @param array  $ignore_types
	 */
	public static function process(&$article, &$context, &$class, $method, $params = [], $ignore_types = [])
	{
		self::processText('title', $article, $class, $method, $params, $ignore_types);
		self::processText('created_by_alias', $article, $class, $method, $params, $ignore_types);
		self::processText('description', $article, $class, $method, $params, $ignore_types);

		// Don't replace in text fields in the category list view, as they won't get used anyway
		if (Document::isCategoryList($context))
		{
			return;
		}

		// prevent fulltext from being messed with, when it is a json encoded string (Yootheme Pro templates do this for some weird f-ing reason)
		if ( ! empty($article->fulltext) && substr($article->fulltext, 0, 6) == '<!-- {')
		{
			self::processText('text', $article, $class, $method, $params, $ignore_types);

			return;
		}

		$has_text                  = isset($article->text);
		$has_article_texts         = isset($article->introtext) && isset($article->fulltext);
		$text_same_as_article_text = false;

		if ($has_text && $has_article_texts)
		{
			$check_text               = RegEx::replace('\s', '', $article->text);
			$check_introtext_fulltext = RegEx::replace('\s', '', $article->introtext . ' ' . $article->fulltext);

			$text_same_as_article_text = $check_text == $check_introtext_fulltext;
		}

		if ($has_article_texts && ! $has_text)
		{
			self::processText('introtext', $article, $class, $method, $params, $ignore_types);
			self::processText('fulltext', $article, $class, $method, $params, $ignore_types);

			return;
		}

		if ($has_article_texts && $text_same_as_article_text)
		{
			$splitter = '͞';
			if (strpos($article->introtext, $splitter) !== false
				|| strpos($article->fulltext, $splitter) !== false)
			{
				$splitter = 'Ͽ';
			}

			$article->text = $article->introtext . $splitter . $article->fulltext;

			self::processText('text', $article, $class, $method, $params, $ignore_types);

			[$article->introtext, $article->fulltext] = explode($splitter, $article->text, 2);

			$article->text = str_replace($splitter, ' ', $article->text);

			return;
		}

		self::processText('text', $article, $class, $method, $params, $ignore_types);
		self::processText('introtext', $article, $class, $method, $params, $ignore_types);

		// Don't handle fulltext on category blog views
		if ($context == 'com_content.category' && JFactory::getApplication()->input->get('view') == 'category')
		{
			return;
		}

		self::processText('fulltext', $article, $class, $method, $params, $ignore_types);
	}

	/**
	 * @param string $type
	 * @param object $article
	 * @param object $class
	 * @param string $method
	 * @param array  $params
	 * @param array  $ignore_types
	 */
	private static function processText($type, &$article, &$class, $method, $params = [], $ignore_types = [])
	{
		if (empty($article->{$type}))
		{
			return;
		}

		if (in_array($type, $ignore_types))
		{
			return;
		}

		call_user_func_array([$class, $method], array_merge([&$article->{$type}], $params));
	}
}
