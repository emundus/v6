<?php
/**
 * @version   $Id: toggle.php 2468 2012-08-17 06:16:57Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('GANTRY_VERSION') or die();


/**
 * Renders a toggle element
 *
 * @package     gantry
 * @subpackage  admin.elements
 */
gantry_import('core.config.gantryformfield');

class GantryFormFieldToggle extends GantryFormField
{

	protected $type = 'toggle';
	protected $basetype = 'checkbox';
	static $jsLoaded = false;

	public function getInput()
	{
		/** @var $gantry Gantry */
		global $gantry;
		$hidden = '<input type="hidden" name="' . $this->name . '" value="_" />';

		$options   = array();
		$options[] = array('value'=> 1, 'text'=> 'On/Off', 'id'=> $this->element->name);

		if (!self::$jsLoaded) {
			$gantry->addScript($gantry->gantryUrl . '/admin/widgets/toggle/js/toggle.js');
			self::$jsLoaded = true;
		}

		// if (!defined('GANTRY_TOGGLE')) {
		// 	$this->template = end(explode('/', $gantry->templatePath));

		//           $gantry->addScript($gantry->gantryUrl.'/admin/widgets/toggle/js/touch.js');
		//           $gantry->addScript($gantry->gantryUrl.'/admin/widgets/toggle/js/toggle.js');
		//           define('GANTRY_TOGGLE',1);
		//       }


		//$gantry->addDomReadyScript($this->toggleInit($this->id));

		$checked = ($this->value == 0) ? '' : 'checked="checked"';
		if ($this->value == 0) $toggleStatus = 'off'; else $toggleStatus = 'on';

		if ($this->detached) $disabledField = ' disabled'; else $disabledField = '';

		return '
		<div class="wrapper">' . "\n" . '
			<div class="toggle toggle-' . $toggleStatus . $disabledField . '">' . "\n" . '
				<input type="hidden" class="toggle-input" name="' . $this->name . '" id="' . $this->id . '" value="' . $this->value . '" />' . "\n" . '
			</div>' . "\n" . '
		</div>' . "\n" . '
		';
	}

	public static function initialize()
	{

	}

	public static function finalize()
	{
		/** @var $gantry Gantry */
		global $gantry;
		//$gantry->addDomReadyScript("window.gantryToggles = new Toggle();");
	}

}
