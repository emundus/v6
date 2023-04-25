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
require_once JPATH_ROOT . '/components/com_tags/router.php';

class FalangTagsRouter extends TagsRouter
{
	/**
	 * Content Component router constructor
	 *
	 * @param   JApplicationCms $app  The application object
	 * @param   JMenu           $menu The menu object to work with
	 */
//	public function __construct($app = null, $menu = null)
//	{
//		parent::__construct($app, $menu);
//		//need to override router name
//		$this->name = 'tag';
//
//	}

	protected function fixSegment($segment)
	{
		$db = JFactory::getDbo();
		$lang         = JFactory::getLanguage();
		$default_lang = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');

		// Try to find tag id
		$alias = str_replace(':', '-', $segment);

		if ($lang->getTag() != $default_lang){

			$fManager = FalangManager::getInstance();
			$id_lang = $fManager->getLanguageID($lang->getTag());


			$query = $db->getQuery(true);
			$query->select('reference_id')
				->from('#__falang_content fc')
				->where('fc.value = ' .  $db->quote($alias))
				->where('fc.language_id = ' . $query->q($id_lang))
				->where('fc.reference_field = '.$query->q('alias'))
				->where('fc.published = 1')
				->where('fc.reference_table = '.$query->q('tags'));

			$db->setQuery($query);
			$refid = $db->loadResult();

			//if falang translation exist for this alias tag return the segment
			if ($refid){
				$segment = "$refid:$alias";
				return $segment;
			}


		}

		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__tags'))
			->where($db->quoteName('alias') . " = " . $db->quote($alias));

		$id = $db->setQuery($query)->loadResult();

		if ($id)
		{
			$segment = "$id:$alias";
		}

		return $segment;


	}

}