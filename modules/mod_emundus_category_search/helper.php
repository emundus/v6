<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Site
 * @subpackage	mod_emundusmenu
 * @since		1.5
 */
class modEmundusCategorySearchHelper {

	// Initialize class variables
	var $db = null;

	public function __construct() {

		// Load class variables
		$this->db = JFactory::getDbo();

	}


	public function loadCategories() {

		$query = $this->db->getQuery(true);
		$query->select([$this->db->quoteName('label'), $this->db->quoteName('title'), $this->db->quoteName('color')])
			->from($this->db->quoteName('#__emundus_setup_thematiques'))
			->where($this->db->quoteName('published').' = 1')
			->order($this->db->quoteName('order').' ASC');
		$this->db->setQuery($query);

		try {
			return $this->db->loadObjectList();
		} catch (Exception $e) {
			return null;
		}
	}

}
