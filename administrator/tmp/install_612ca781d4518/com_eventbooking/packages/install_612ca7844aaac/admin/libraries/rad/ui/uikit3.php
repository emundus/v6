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
class RADUiUikit3 extends RADUiAbstract implements RADUiInterface
{
	/**
	 * UIKit framework classes
	 *
	 * @var array
	 */
	protected $frameworkClasses = [
		'uk-input',
		'uk-select',
		'uk-textarea',
		'uk-radio',
		'uk-checkbox',
		'uk-legend',
		'uk-range',
		'uk-fieldset',
		'uk-legend',
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
				'row-fluid'                                      => 'uk-container uk-grid',
				'span2'                                          => 'uk-width-1-6@s',
				'span3'                                          => 'uk-width-1-4@s',
				'span4'                                          => 'uk-width-1-3@s',
				'span5'                                          => 'uk-width-1-2@s',
				'span6'                                          => 'uk-width-1-2@s',
				'span7'                                          => 'uk-width-1-2@s',
				'span8'                                          => 'uk-width-2-3@s',
				'span9'                                          => 'uk-width-3-4@s',
				'span10'                                         => 'uk-width-5-6@s',
				'span12'                                         => 'uk-width-1-1',
				'pull-left'                                      => 'uk-float-left',
				'pull-right'                                     => 'uk-float-right',
				'clearfix'                                       => 'uk-clearfix',
				'btn'                                            => 'uk-button uk-button-default',
				'btn-primary'                                    => 'uk-button-primary',
				'btn-mini'                                       => 'uk-button uk-button-default uk-button-small',
				'btn-small'                                      => 'uk-button uk-button-default uk-button-small',
				'btn-large'                                      => 'uk-button uk-button-default uk-button-large',
				'btn-inverse'                                    => 'uk-button-primary',
				'hidden-phone'                                   => 'uk-visible@s',
				'form form-horizontal'                           => 'uk-form-horizontal',
				'control-group'                                  => 'control-group',
				'control-label'                                  => 'uk-form-label',
				'controls'                                       => 'uk-form-controls uk-form-controls-text',
				'input-tiny'                                     => 'uk-input uk-form-width-xsmall',
				'input-small'                                    => 'uk-input uk-form-width-small',
				'input-medium'                                   => 'uk-input uk-form-width-medium',
				'input-large'                                    => 'uk-input uk-form-width-large',
				'center'                                         => 'uk-text-center',
				'text-center'                                    => 'uk-text-center',
				'row-fluid clearfix'                             => 'uk-container uk-grid uk-clearfix',
				'btn btn-primary'                                => 'uk-button uk-button-default uk-button-primary',
				'table table-striped table-bordered'             => 'uk-table uk-table-striped uk-table-bordered',
				'table table-bordered table-striped'             => 'uk-table uk-table-bordered uk-table-striped',
				'table table-striped table-bordered table-hover' => 'uk-table uk-table-striped uk-table-bordered uk-table-hover',
				'nav'                                            => 'uk-nav',
				'nav-pills'                                      => 'uk-navbar',
				'badge badge-info'                               => 'uk-badge',
			];
		}

		$this->classMaps = $classMaps;
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
		$html[] = '<div class="uk-inline">';
		$html[] = '<span class="uk-form-icon">' . $addOn . '</span>';
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
		$html[] = '<div class="uk-inline">';
		$html[] = $input;
		$html[] = '<span class="uk-form-icon">' . $addOn . '</span>';
		$html[] = '</div>';

		return implode("\n", $html);
	}
}