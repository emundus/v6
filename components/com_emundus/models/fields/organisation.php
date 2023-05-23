<?php
/**
* @version		$Id: organisation.php 14401 2013-02-26 14:10:00Z rivalland $
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

/**
 * Renders a list of elements
 *
 * @package     Joomla.Framework
 * @subpackage	Parameter
 * @since       2.5
 */

class JFormFieldOrganisation extends JFormField
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $type = 'Organisation';

	public function getLabel() {
	// code that returns HTML that will be shown as the label
		 return '<span style="text-decoration: underline;">' . parent::getLabel() . '</span>';
	}

	public function getInput() {
	// code that returns HTML that will be shown as the form field
     	$class = '';
		$options = array ();
		$db	= JFactory::getDBO();

		$query = 'SELECT c.id, c.title 
					FROM #__categories c 
					WHERE published=1 AND extension = "com_contact" AND alias != "bank" 
					ORDER BY c.title';
		$db->setQuery($query);
		$organisations = $db->loadObjectList();

		$options[] = JHTML::_('select.optgroup', JText::_(""));
		foreach($organisations as $organisation){
			$options[] = JHTML::_('select.option', $organisation->id, JText::_($organisation->title), 'value', 'text');
		}

		return JHTML::_('select.genericlist',  $options, $this->name, 'value', 'text');
	}

}
?>
