<?php
/**
 * @version   $Id: tips.php 30069 2016-03-08 17:45:33Z matias $
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
class GantryFormFieldTips extends GantryFormField
{

	protected $type = 'tips';
	protected $basetype = 'none';
	public static $assets_loaded = false;

	public function getInput()
	{

		/** @var $gantry Gantry */
		global $gantry;

		$tabname = $this->element['tab'];
		$output  = "";

		if (!self::$assets_loaded){
			$gantry->addScript($gantry->gantryUrl . '/admin/widgets/tips/js/tips.js');
			$gantry->addInlineScript('var GantryPanelsTips = {};');

			self::$assets_loaded = true;
		}

		$xmlist = $gantry->templatePath . '/admin/tips/' . $tabname . '.xml';
		if (!file_exists($xmlist)) die($xmlist . ' file not found');

		$xml    = simplexml_load_file($xmlist);
		$count  = count($xml);
		$random = 0;

		if ($tabname != "overview") {
			$output = new stdClass;
			$output->{$tabname} = new stdClass;
			for ($i = 0; $i < $count; $i++) {
				$tip_title = ($xml->tip[$i]['label']);
				$tip_id    = (isset($xml->tip[$i]['id'])) ? $xml->tip[$i]['id'] : false;

				if ($tip_id){
					$tip_id = str_replace('-', '_', $tip_id);

					$output->{$tabname}->{$tip_id} = array(
						'title' => (string)$tip_title,
						'content' => strip_tags((string)$xml->tip[$i])
					);
				}
			}

			$gantry->addInlineScript("Object.merge(GantryPanelsTips, " . json_encode($output) . ");");

			return "";

		} else {
			$output = "
			<div class=\"gantrytips\">\n
				<div class=\"gantry-pin\"></div>\n
				<div class=\"gantrytips-count\"><span class=\"current-tip\">" . ($random + 1) . "</span><span> / " . $count . "</span></div>\n
				<div class=\"gantrytips-controller rok-buttons-group\">\n
					<span class=\"rok-button gantrytips-arrow gantrytips-left\">&#9668;</span>\n
					<span class=\"rok-button gantrytips-arrow gantrytips-right\">&#9658;</span>\n
				</div>\n
				<div class=\"gantrytips-desc\">\n
					<div class=\"gantrytips-wrapper\">\n";

			for ($i = 0; $i < $count; $i++) {
				$tip_title = ($xml->tip[$i]['label']);
				$tip_id    = (isset($xml->tip[$i]['id'])) ? $xml->tip[$i]['id'] : false;

				if (!$tip_id) $outputID = ''; else $outputID = 'id="tip-' . str_replace('-', '_', $tip_id) . '"';

				$output .= "<div " . $outputID . " class=\"gantrytips-tip\">\n";
				$output .= "<div class=\"gantrytips-bar h2bar\">\n
						<span>" . $tip_title . "</span>\n
					</div>\n";
				$output .= $xml->tip[$i] . "</div>\n";
			}

			$output .= "
					</div>\n
				</div>\n
			</div>\n";

			return $output;

		}



	}

	public function getLabel()
	{
		return "";
	}
}
