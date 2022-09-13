<?php
/**
* @version		$Id: group.php 14401 2013-02-26 14:10:00Z rivalland $
* @package		Joomla.Framework
* @subpackage	Parameter
* @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');
jimport('joomla.application.component.controller');

/**
 * Renders a list of elements
 *
 * @package     Joomla.Framework
 * @subpackage	Parameter
 * @since       2.5
 */

class JFormFieldGroup extends JFormField
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $type = 'Group';

	public function getLabel() {
	// code that returns HTML that will be shown as the label
		 return '<span style="text-decoration: underline;">' . parent::getLabel() . '</span>';
	}

	public function getInput() {
	// code that returns HTML that will be shown as the form field
     	$class = '';
		$options = '';
		$db	= JFactory::getDBO();

		$query = 'SELECT esg.id, esg.label  
		FROM #__emundus_setup_groups esg
		WHERE esg.published=1 
		ORDER BY esg.label';
		$db->setQuery($query);
		$groups = $db->loadObjectList();

		foreach($groups as $group) {
			$options .= '<label><input type="checkbox" name="'.$this->name.'" value="'.$group->id.'" ';
			/*if($edit==1) {
				foreach($this->users_groups as $users_groups) {
					if($users_groups->user_id==$this->users[0]->id && $users_groups->group_id==$groups->id)
						echo ' checked="checked"';
				}
			}*/
			$options .= ' />'.$group->label.'</label><br />';
		}

		return $options;
	}

}
?>
