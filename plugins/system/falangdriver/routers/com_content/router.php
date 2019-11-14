<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

defined('_JEXEC') or die;

/**
 * Routing class of com_content
 *
 * @since  3.3
 */
require_once JPATH_ROOT . '/components/com_content/router.php';

class FalangContentRouter extends ContentRouter
{

	/**
	 * Content Component router constructor
	 *
	 * @param   JApplicationCms $app  The application object
	 * @param   JMenu           $menu The menu object to work with
	 */
	public function __construct($app = null, $menu = null)
	{
		parent::__construct($app, $menu);
		//need to override router name
		$this->name = 'content';

	}



	/**
	 * Method to get the segment(s) for an article
	 *
	 * @param   string $id    ID of the article to retrieve the segments for
	 * @param   array  $query The request that is built right now
	 *
	 * @return  array|string  The segments of this item
	 */

	public function getArticleSegment($id, $query)
	{
		if (!strpos($id, ':'))
		{
			$db      = JFactory::getDbo();
			$dbquery = $db->getQuery(true);
			//add id in the query to use falang override query
			$dbquery->select($dbquery->qn(array('alias', 'id')))
				->from($dbquery->qn('#__content'))
				->where('id = ' . $dbquery->q($id));
			$db->setQuery($dbquery);
			$id .= ':' . $db->loadResult();
		}

		if ($this->noIDs)
		{
			list($void, $segment) = explode(':', $id, 2);

			return array($void => $segment);
		}

		return array((int) $id => $id);
	}


	public function getArticleId($segment, $query)
	{
		if ($this->noIDs)
		{
			$lang         = JFactory::getLanguage()->getTag();
			$default_lang = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');

			//look in Falang Table
			if ($default_lang != $lang)
			{
				$fManager = FalangManager::getInstance();
				$id_lang  = $fManager->getLanguageID($lang);

				$db      = JFactory::getDbo();
				$dbQuery = $db->getQuery(true);
				$dbQuery->select('fc.reference_id')
					->from('#__falang_content fc')
					->where('fc.value = ' . $dbQuery->q($segment))
					->where('fc.language_id = ' . $dbQuery->q($id_lang))
					->where('fc.reference_field = ' . $dbQuery->q('alias'))
					->where('fc.published = 1')
					->where('fc.reference_table = ' . $dbQuery->q('content'));

				$db->setQuery($dbQuery);
				$db->execute();
				$num_rows = $db->getNumRows();
				//case no alias translated by falang need to find the content alias
				//TODO filter by catid too in case
				if (empty($num_rows)){
					//get category
					if (isset($query['view']) && $query['view'] == 'category'){
						$cat_id = $query['id'];
					}

					$dbQuery = $db->getQuery(true);
					$dbQuery->select('c.id');
					$dbQuery->from('#__content c');
					$dbQuery->where('c.alias = ' . $dbQuery->q($segment));

					if (isset($cat_id)){
						$dbQuery->where('c.catid = ' . $dbQuery->q($cat_id));
					}

					$db->setQuery($dbQuery);
					$db->execute();
					$article_id = $db->loadResult();
					return (int) $article_id;
				}
				//most case only 1 alias
				if (isset($num_rows) && $num_rows == 1){
					$article_id = $db->loadResult();
					if (isset($article_id))
					{
						return (int) $article_id;
					}
				} else {
					$article_ids = $db->loadObjectList();
					//2 alias with the same name look the right one
					foreach ($article_ids as $id){
						$dbquery = $db->getQuery(true);
						$dbquery->select($dbquery->qn('id'))
							->from($dbquery->qn('#__content'))
							->where('id = ' . $dbquery->q($id->reference_id))
							->where('catid = ' . $dbquery->q($query['id']));
						$db->setQuery($dbquery);
						$db->execute();
						$num_rows = $db->getNumRows();
						if (isset($num_rows)) {
							return (int) $db->loadResult();
						}
					}
				}
			} else	{
				$db      = JFactory::getDbo();
				$dbquery = $db->getQuery(true);
				$dbquery->select($dbquery->qn('id'))
					->from($dbquery->qn('#__content'))
					->where('alias = ' . $dbquery->q($segment))
					->where('catid = ' . $dbquery->q($query['id']));
				$db->setQuery($dbquery);

				return (int) $db->loadResult();
			}
		}

		return (int) $segment;
	}

}