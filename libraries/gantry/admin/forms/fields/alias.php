<?php
/**
 * @version   $Id: alias.php 2381 2012-08-15 04:14:26Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('GANTRY_VERSION') or die();
/**
 * @package     gantry
 * @subpackage  admin.elements
 */
gantry_import('core.config.gantryformfield');

require_once(dirname(__FILE__) . '/selectbox.php');

class GantryFormFieldAlias extends GantryFormFieldSelectBox
{

	protected $type = 'alias';
	protected $basetype = 'select';

	public function getInput()
	{
		/** @var $gantry Gantry */
		global $gantry;
		$names     = explode('-', $this->group);
		$names[]   = $this->fieldname;
		$labelname = implode('-', $names);

		$intro = "<div class='alias-label'>" . $labelname . " &rarr; </div>";
		return $intro . parent::getInput();
	}

	public function getLabel()
	{
		return "";
	}

	protected function getOptions()
	{
		/** @var $gantry Gantry */
		global $gantry;
		$options = array();
		$options = parent::getOptions();

		$hide_mobile = false;

		$positions = $gantry->getUniquePositions();

		foreach ($positions as $position) {
			$positionInfo = $gantry->getPositionInfo($position);
			if ($hide_mobile && $positionInfo->mobile) {
				continue;
			}
			if (1 == (int)$positionInfo->max_positions) {
				$split_postions[] = $positionInfo->id;
				continue;
			}
			for ($i = 1; $i <= (int)$positionInfo->max_positions; $i++) {
				$split_postions[] = $positionInfo->id . '-' . chr(96 + $i);
			}
		}

		foreach ($split_postions as $position) {
			// Create a new option object based on the <option /> element.
			$tmp       = GantryHtmlSelect::option($position, $position, 'value', 'text', false);
			$options[] = $tmp;
		}

		return $options;
	}
}
