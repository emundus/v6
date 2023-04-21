<?php
/**
 * @package		Joomla
  * @subpackage  Search.emundus
 * @copyright	Copyright (C) 2016 emundus.fr. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Content Search plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	Search.emundus
 * @since		3
 */
class plgSearchEmundus extends JPlugin
{

	/**
	 * @return array An array of search areas
	 */
	function onContentSearchAreas()
	{
		// load plugin params info
		$section = $this->params->get('search_section_heading');
		$areas = array(
			'emundus' => $section
		);
		return $areas;
	}

	/**
	 * Content Search method
	 * The sql must return the following fields that are used in a common display
	 * routine: href, title, section, created, text, browsernav
	 * @param string Target search string
	 * @param string mathcing option, exact|any|all
	 * @param string ordering option, newest|oldest|popular|alpha|category
	 * @param mixed An array if the search it to be restricted to areas, null if search all
	 */
	function onContentSearch( $text, $phrase='', $ordering='', $areas=null )
	{
		global $mainframe;

		$db		= JFactory::getDBO();
		$user	= JFactory::getUser();

		require_once(JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
		require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_search'.DS.'helpers'.DS.'search.php');

		$searchText = $text;
		if (is_array( $areas )) {
			if (!array_intersect( $areas, array_keys( plgSearchEmundusAreas() ) )) {
				return array();
			}
		}

		// load plugin params info
	 	$plugin			= JPluginHelper::getPlugin('search', 'emundus');
		$limit 			= $this->params->set( 'search_limit', 50 );

		$text = trim( $text );
		if ($text == '') {
			return array();
		}

		$wheres = array();
		switch ($phrase) {
			case 'exact':
				$text		= $db->Quote( '%'.$db->getEscaped( $text, true ).'%', false );
				$wheres2 	= array();
				$wheres2[] 	= 'eu.user_id LIKE '.$text;
				$wheres2[] 	= 'eu.lastname LIKE '.$text;
				$wheres2[] 	= 'eu.firstname LIKE '.$text;
				$wheres2[] 	= 'eu.schoolyear LIKE '.$text;
				$wheres2[] 	= 'u.email LIKE '.$text;
				$where 		= '(' . implode( ') OR (', $wheres2 ) . ')';
				break;

			case 'all':
			case 'any':
			default:
				$words = explode( ' ', $text );
				$wheres = array();
				foreach ($words as $word) {
					$word		= $db->Quote( '%'.$db->getEscaped( $word, true ).'%', false );
					$wheres2 	= array();
					$wheres2[] 	= 'eu.user_id LIKE '.$word;
					$wheres2[] 	= '(eu.lastname LIKE '.$word.' OR eu.firstname LIKE '.$word.')';
					$wheres2[] 	= 'eu.schoolyear LIKE '.$word;
					$wheres2[] 	= 'u.email LIKE '.$word;
					$wheres[] 	= implode( ' OR ', $wheres2 );
				}
				$where = '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheres ) . ')';
				break;
		}

		switch ($ordering) {
			case 'oldest':
				$order = 'eu.schoolyear ASC';
				break;

			case 'alpha':
				$order = 'eu.lastname ASC';
				break;

			case 'category':
				$order = 'eu.profile ASC';
				break;

			case 'newest':
				default:
				$order = 'eu.schoolyear DESC';
				break;
		}

		$rows = array();
		
		//Search users
		if ($limit >0){
		$query = 'SELECT eu.university_id, eu.registerDate AS created, esp.label AS text, eu.user_id AS user_id,'
			. ' CONCAT_WS(" ",eu.firstname, eu.lastname) AS name,'
			. ' CONCAT_WS(" - ", eu.user_id, name, u.email) AS title,'
			. ' CONCAT_WS(" : ", "Promotion", eu.schoolyear) AS section,'
			. ' "cand" AS browsernav'
			. ' FROM #__emundus_users AS eu'
			. ' LEFT JOIN #__emundus_setup_profiles AS esp ON esp.id = eu.profile'
			. ' LEFT JOIN #__users AS u ON u.id = eu.user_id'
			. ' LEFT JOIN #__emundus_uploads AS eup ON eup.user_id = eu.user_id AND eup.attachment_id = 10'
			. ' WHERE ('.$where.')'
			. ' AND eu.profile > 6'
			. ' ORDER BY '. $order
			;
		}
		$db->setQuery( $query, 0, $limit );
		$list = $db->loadObjectList();
		$limit -= count($list);
		if(isset($list)){
			foreach($list as $key => $item){
				$list[$key]->href = 'index.php?option=com_emundus&view=application_form&sid='.$item->user_id;
			}
		}
		$rows[] = $list;

		$results = array();
		if(count($rows))
		{
			foreach($rows as $row)
			{
				$new_row = array();
				foreach($row AS $key => $student) {
						$new_row[] = $student;
				}
				$results = array_merge($results, (array) $new_row);
			}
		}
		return $results;
	}
}