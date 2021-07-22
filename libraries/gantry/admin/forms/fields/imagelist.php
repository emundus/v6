<?php
/**
 * @version        $Id: imagelist.php 2325 2012-08-13 17:46:48Z btowles $
 * @package        Joomla.Framework
 * @subpackage     Form
 * @copyright      Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
GantryFormHelper::loadFieldType('filelist');

/**
 * Supports an HTML select list of image
 *
 * @package        Joomla.Framework
 * @subpackage     Form
 * @since          1.6
 */
class GantryFormFieldImageList extends GantryFormFieldFileList
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	public $type = 'ImageList';

	/**
	 * Method to get the field options.
	 *
	 * @return    array    The field option objects.
	 * @since    1.6
	 */
	public function getOptions()
	{
		// Define the image file type filter.
		$filter = '\.png$|\.gif$|\.jpg$|\.bmp$|\.ico$|\.jpeg$|\.psd$|\.eps$';

		// Set the form field element attribute for file type filter.
		$this->element->addAttribute('filter', $filter);

		// Get the field options.
		return parent::getOptions();
	}
}
