<?php
/**
 * @version   $Id: diagnostic.php 2381 2012-08-15 04:14:26Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2016 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
defined('GANTRY_VERSION') or die();

gantry_import('core.config.gantryformfield');
/**
 * @package     gantry
 * @subpackage  admin.elements
 */
class GantryFormFieldDiagnostic extends GantryFormField
{

	protected $type = 'diagnostic';
	protected $basetype = 'none';

	public function getInput()
	{

		/** @var $gantry Gantry */
		global $gantry;

		gantry_import('core.gantrydiagnostic');
		$diagnose = new GantryDiagnostic();
		$errors   = $diagnose->runChecks();

		$output = "";

		if (count($errors) > 0) {
			$klass  = "errors";
			$title  = "Something Wrong :(";
			$output = implode("", $errors);
		} else {
			$klass  = "success";
			$title  = "Good!";
			$output = "Congratulations! All the diagnostic test have passed successfully.  There are no show-stopper issues.  You can however still download the ";
		}
		//$output .= '<a href="'.$gantry->baseUrl.'?option=com_admin&amp;tmpl=gantry-ajax-admin&amp;model=diagnostics&amp;template='.$gantry->templateName.'">Diagnostic Status Information archive</a>.';

		return "
		<div id='diagnostic' class='" . $klass . "'>
			<div id='diagnostic-bar' class='h2bar'>Diagnostics: <span>" . $title . "</span></div>
			<div id='diagnostic-desc' class='g-inner'>
			" . $output . "
			</div>
		</div>";

	}

	public function getLabel()
	{
		return "";
	}
}