<?php
/**
 * @version   $Id: modules.php 4585 2012-10-27 01:44:54Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2017 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('JPATH_BASE') or die();

jimport('joomla.html.html');
JFormHelper::loadFieldClass('list');
/**
 * @package     gantry
 * @subpackage  admin.elements
 */
class JFormFieldModules extends JFormFieldList
{

    /**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'modules';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	protected function getOptions()
	{

        $options = array();
        $db	= JFactory::getDbo();
        $query =  $db->getQuery(true);
        $query->select('id, title, module, position');
        $query->from('#__modules AS m');
        $query->where('m.client_id = 0');
        $query->order('position, ordering');

        // Set the query
        $db->setQuery($query);
        if (!($modules = $db->loadObjectList())) {
            JError::raiseWarning(500, JText::sprintf('JLIB_APPLICATION_ERROR_MODULE_LOAD', $db->getErrorMsg()));
            return false;
        }

        foreach($modules as $module){
            $options[] = JHtml::_('select.option', $module->id, $module->title . ' (' . $module->module . ')');
        }

        // Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
        return $options;

	}
}
