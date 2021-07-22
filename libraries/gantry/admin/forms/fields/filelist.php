<?php
/**
 * @version        $Id: filelist.php 2325 2012-08-13 17:46:48Z btowles $
 * @package        Joomla.Framework
 * @subpackage     Form
 * @copyright      Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
GantryFormHelper::loadFieldType('list');

/**
 * Supports an HTML select list of file
 *
 * @package        Joomla.Framework
 * @subpackage     Form
 * @since          1.6
 */
class GantryFormFieldFileList extends GantryFormFieldSelectBox
{
	/**
	 * The form field type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	public $type = 'FileList';
	protected $basetype = 'select';

	/**
	 * Method to get the field options.
	 *
	 * @return    array    The field option objects.
	 * @since    1.6
	 */
	public function getOptions()
	{
		// Initialize variables.
		$options = array();

		// Initialize some field attributes.
		$filter      = (string)$this->element['filter'];
		$exclude     = (string)$this->element['exclude'];
		$stripExt    = (string)$this->element['stripext'];
		$hideNone    = (string)$this->element['hide_none'];
		$hideDefault = (string)$this->element['hide_default'];

		// Get the path in which to search for file options.
		$path = (string)$this->element['directory'];
		if (!is_dir($path)) {
			$path = JPATH_ROOT . '/' . $path;
		}

		// Prepend some default options based on field attributes.
		if (!$hideNone) {
			$options[] = GantryHtmlSelect::option('-1', JText::alt('JOPTION_DO_NOT_USE', preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)));
		}
		if (!$hideDefault) {
			$options[] = GantryHtmlSelect::option('', JText::alt('JOPTION_USE_DEFAULT', preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)));
		}

		// Get a list of files in the search path with the given filter.
		$files = JFolder::files($path, $filter);

		// Build the options list from the list of files.
		if (is_array($files)) {
			foreach ($files as $file) {

				// Check to see if the file is in the exclude mask.
				if ($exclude) {
					if (preg_match(chr(1) . $exclude . chr(1), $file)) {
						continue;
					}
				}

				// If the extension is to be stripped, do it.
				if ($stripExt) {
					$file = JFile::stripExt($file);
				}

				$options[] = GantryHtmlSelect::option($file, $file);
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
