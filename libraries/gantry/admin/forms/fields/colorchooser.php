<?php
/**
 * @version   $Id: colorchooser.php 6564 2013-01-16 17:13:36Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

defined('GANTRY_VERSION') or die();
/**
 * @package     gantry
 * @subpackage  admin.elements
 */
gantry_import('core.config.gantryformfield');

class GantryFormFieldColorChooser extends GantryFormField
{

	protected $type = 'colorchooser';
	protected $basetype = 'text';

	static $assets_loaded = false;

	public function getInput()
	{
		/** @global Gantry $gantry */
		global $gantry;
		$output = '';

		$expl_path = explode('/', $gantry->templatePath);
		$this->template = end($expl_path);
		$transparent    = 1;

		if ($this->element->attributes('transparent') == 'false') $transparent = 0;
		if (!defined('GANTRY_CSS')) {
			$gantry->addStyle($gantry->gantryUrl . '/admin/widgets/gantry.css');
			define('GANTRY_CSS', 1);
		}

		if (!self::$assets_loaded){
			$gantry->addStyle($gantry->gantryUrl . '/admin/widgets/colorchooser/css/mooRainbow-2.0.css');
			$gantry->addScript($gantry->gantryUrl . '/admin/widgets/colorchooser/js/mooRainbow-2.0.js');

			self::$assets_loaded = true;
		}

		$output = array();

		$output[] = '<div class="wrapper">';
		$output[] = '	<input class="picker-input text-color" data-moorainbow data-moorainbow-transparent="' . $transparent . '" id="' . $this->id . '" name="' . $this->name . '" type="text" value="' . $this->value . '" />';
		$output[] = '	<div class="picker" data-moorainbow-trigger="' . $this->id . '">';
		$output[] = '		<div class="overlay' . (($this->value == 'transparent') ? ' overlay-transparent' : '') . '" style="background-color: ' . $this->value . '">';
		$output[] = '			<div></div>';
		$output[] = '		</div>';
		$output[] = ' 	</div>';
		$output[] = '</div>';

		return implode("\n", $output);
	}
}
