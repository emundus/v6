<?php
/**
 * @package         Regular Labs Library
 * @version         22.4.18687
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php'))
{
	return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

JFormHelper::loadFieldClass('range');

class JFormFieldRL_Range extends JFormFieldRange
{
	protected $layout = 'range';

	protected function getInput()
	{
		$this->value = (float) ($this->value ?: $this->default);

		if ( ! empty($this->max))
		{
			$this->value = min($this->value, $this->max);
		}
		if ( ! empty($this->min))
		{
			$this->value = max($this->value, $this->min);
		}

		return parent::getInput();
	}

	protected function getLayoutData()
	{
		$data = parent::getLayoutData();

		// Initialize some field attributes.
		$extraData = [
			'prepend' => (string) $this->element['prepend'] ?? '',
			'append'  => (string) $this->element['append'] ?? '',
		];

		return array_merge($data, $extraData);
	}

	protected function getLayoutPaths()
	{
		$paths   = parent::getLayoutPaths();
		$paths[] = JPATH_LIBRARIES . '/regularlabs/layouts';

		return $paths;
	}
}
