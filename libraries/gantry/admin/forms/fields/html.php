<?php
/**
 * @version   $Id: html.php 6564 2013-01-16 17:13:36Z btowles $
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

class GantryFormFieldHTML extends GantryFormField
{


	protected $type = 'html';
	protected $basetype = 'none';

	public function getInput()
	{
		/** @global $gantry Gantry */
		global $gantry;

		$html = (string)$this->element->html;

		// version
		$html = str_replace("{template_version}", $gantry->_template->getVersion(), $html);

		// template name
		$html = str_replace("{template_name}", $gantry->get('template_full_name'), $html);

		// preview
		$html = str_replace("{template_preview}", $gantry->templateUrl . '/screenshot.png', $html);

		return "<div class='html'>" . $html . "</div>";
	}

	public function getLabel()
	{
		return "";
	}

}