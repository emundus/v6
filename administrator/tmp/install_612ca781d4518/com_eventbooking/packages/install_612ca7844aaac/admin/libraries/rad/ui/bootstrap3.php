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
class RADUiBootstrap3 extends RADUiAbstract implements RADUiInterface
{
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
				'pull-left'          => 'pull-left',
				'pull-right'         => 'pull-right',
				'btn'                => 'btn btn-default',
				'btn-mini'           => 'btn-xs',
				'btn-small'          => 'btn-sm',
				'btn-large'          => 'btn-lg',
				'visible-phone'      => 'visible-xs',
				'visible-tablet'     => 'visible-sm',
				'visible-desktop'    => 'visible-md visible-lg',
				'hidden-phone'       => 'hidden-xs',
				'hidden-tablet'      => 'hidden-sm',
				'hidden-desktop'     => 'hidden-md hidden-lg',
				'control-group'      => 'form-group',
				'input-prepend'      => 'input-group',
				'input-append '      => 'input-group',
				'add-on'             => 'input-group-addon',
				'img-polaroid'       => 'img-thumbnail',
				'control-label'      => 'col-md-3 control-label',
				'controls'           => 'col-md-9',
				'nav'                => 'navbar-nav',
				'nav-stacked'        => 'nav-stacked',
				'nav-tabs'           => 'nav-tabs',
				'btn-inverse'        => 'btn-primary',
				'btn btn-primary'    => 'btn btn-primary',
				'row-fluid clearfix' => 'row clearfix',
				'badge badge-info'   => 'badge badge-info',
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
	 * @return mixed
	 */
	public function getPrependAddon($input, $addOn)
	{
		$html   = [];
		$html[] = '<div class="input-group">';
		$html[] = '<div class="input-group-addon">' . $addOn . '</div>';
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
	 * @return mixed
	 */
	public function getAppendAddon($input, $addOn)
	{
		$html   = [];
		$html[] = '<div class="input-group">';
		$html[] = $input;
		$html[] = '<div class="input-group-addon">' . $addOn . '</div>';
		$html[] = '</div>';

		return implode("\n", $html);
	}
}