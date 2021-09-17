<?php
/**
 * @package     MPF
 * @subpackage  UI
 *
 * @copyright   Copyright (C) 2016 - 2018 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

/**
 * Base class for a Joomla Administrator Controller. It handles add, edit, delete, publish, unpublish records....
 *
 * @package       MPF
 * @subpackage    UI
 * @since         2.0
 */
class RADUiBootstrap5 extends RADUiAbstract implements RADUiInterface
{
	/**
	 * UIKit framework classes
	 *
	 * @var array
	 */
	protected $frameworkClasses = [
		'form-control',
		'form-check-input',
		'form-select',
	];

	/**
	 * Constructor
	 *
	 * @param   array  $classMaps
	 */
	public function __construct($classMaps = [])
	{
		if (empty($classMaps))
		{
			$classMaps = [
				'row-fluid'          => 'row',
				'span1'              => 'col-md-1',
				'span2'              => 'col-md-2',
				'span3'              => 'col-md-3',
				'span4'              => 'col-md-4',
				'span5'              => 'col-md-5',
				'span6'              => 'col-md-6',
				'span7'              => 'col-md-7',
				'span8'              => 'col-md-8',
				'span9'              => 'col-md-9',
				'span10'             => 'col-md-10',
				'span11'             => 'col-md-11',
				'span12'             => 'col-md-12',
				'pull-left'          => 'float-start',
				'pull-right'         => 'float-end',
				'btn'                => 'btn btn-secondary',
				'btn-mini'           => 'btn-xs',
				'btn-small'          => 'btn-sm',
				'btn-large'          => 'btn-lg',
				'btn-inverse'        => 'btn-primary',
				'visible-phone'      => 'd-block d-sm-none',
				'visible-tablet'     => 'visible-sm',
				'visible-desktop'    => 'd-block d-md-none',
				'hidden-phone'       => 'd-none d-sm-block d-md-table-cell',
				'hidden-tablet'      => 'd-sm-none',
				'hidden-desktop'     => 'd-md-none hidden-lg',
				'control-group'      => 'row form-group form-row',
				'input-prepend'      => 'input-group-prepend',
				'input-append'       => 'input-group-append',
				'add-on'             => 'input-group-text',
				'img-polaroid'       => 'img-thumbnail',
				'control-label'      => 'col-md-3 form-control-label',
				'controls'           => 'col-md-9',
				'btn btn-primary'    => 'btn btn-primary',
				'row-fluid clearfix' => 'row clearfix',
				'icon-publish'       => 'fa fa-check',
				'icon-unpublish'     => 'fa fa-times',
				'badge badge-info'   => 'badge bg-info',
			];
		}

		$this->classMaps = $classMaps;
	}

	/**
	 * Get the mapping of a given class
	 *
	 * @param   string  $class  The input class
	 *
	 * @return string The mapped class
	 */
	public function getClassMapping($class)
	{
		$mappedClass = parent::getClassMapping($class);

		if ($mappedClass !== null)
		{
			return $mappedClass;
		}

		// Handle icon class
		if (strpos($class, 'icon-') !== false)
		{
			$icon = substr($class, 5);

			return 'fa fa-' . $icon;
		}

		// If no class found, return original class
		return $class;
	}

	/**
	 * Method to render input with prepend add-on
	 *
	 * @param   string  $input
	 * @param   string  $addOn
	 *
	 * @return string
	 */
	public function getPrependAddon($input, $addOn)
	{
		$html   = [];
		$html[] = '<div class="input-group">';
		$html[] = '<span class="input-group-text">' . $addOn . '</span>';
		$html[] = $input;
		$html[] = '</div>';

		return implode("\n", $html);
	}

	/**
	 * Method to render input with append add-on
	 *
	 * @param   string  $input
	 * @param   string  $addOn
	 *
	 * @return string
	 */
	public function getAppendAddon($input, $addOn)
	{
		$html   = [];
		$html[] = '<div class="input-group">';
		$html[] = $input;
		$html[] = '<span class="input-group-text">' . $addOn . '</span>';
		$html[] = '</div>';

		return implode("\n", $html);
	}
}