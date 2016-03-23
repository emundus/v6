<?php
/**
 * @version   $Id: positions.php 30069 2016-03-08 17:45:33Z matias $
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

class GantryFormFieldPositions extends GantryFormField
{
	protected $type = 'positions';
	protected $basetype = 'hidden';

	protected $maxGrid;
	protected $layoutSchemas;
	protected $defaultMainbodySchemas;
	protected $template;
	protected $default;
	protected $mainbodySchemas;
	protected $defaultCount;


	var $schemas = array("1", "2", "3", "4", "5", "6"), $words = array(
		"2",
		"3",
		"4",
		"5",
		"6",
		"7",
		"8",
		"9"
	), $combinations, $customCombinations, $settings, $keyName = "";

	public $position_info;

	public function getInput()
	{
		/** @var $gantry Gantry */
		global $gantry;
		gantry_import('core.gantrypositions');

		$output        = '';
		$lis           = '';
		$currentScheme = '';

		$expl_path = explode('/', $gantry->templatePath);
		$this->template = end($expl_path);

		$this->default      = explode(',', str_replace(' ', '', $this->element['default']));
		$this->defaultCount = count($this->default);

		// [0] => schemas | [1] => words | [2] => maxgrid | [3] => type
		//$opts = $node->children();

		$this->maxGrid = (int)$gantry->get('grid_system');
		if (!$this->maxGrid) $this->maxGrid = 12;

		if ($this->element->grid) {
			$this->maxGrid = (int)$this->element->grid;
		} else {
			$this->maxGrid = (int)$gantry->get('grid_system');
		}


		$this->words   = explode(",", $this->element->words);
		$this->schemas = explode(",", $this->element->schemas);
		$this->type    = ($this->element->type) ? $this->element->type : 'regular';


		$this->layoutSchemas          = $gantry->layoutSchemas[$this->maxGrid];
		$this->defaultMainbodySchemas = $gantry->mainbodySchemas;
		$this->mainbodySchemas        = $gantry->mainbodySchemasCombos[$this->maxGrid];


		$this->keyName = '';
		if ($this->type == 'custom') {
			$tmpName       = str_replace("Position", "Schemas", $this->element['name']);
			$tmpSchema     = $this->{$tmpName};
			$this->keyName = key($tmpSchema[1][0]);
		}

		if (!defined('GANTRY_CSS')) {
			$gantry->addStyle($gantry->gantryUrl . '/admin/widgets/gantry.css');
			define('GANTRY_CSS', 1);
		}

		if (!defined('POSITIONS')) {

			if (!defined('GANTRY_SLIDER')) {
				$gantry->addScript($gantry->gantryUrl . '/admin/widgets/slider/js/slider.js');
				define('GANTRY_SLIDER', 1);
			}
			$gantry->addScript($gantry->gantryUrl . '/admin/widgets/slider/js/unserialize.js');
			$gantry->addScript($gantry->gantryUrl . '/admin/widgets/positions/js/positions-utils.js');

			$this->settings = array("words" => $this->words, "schemas" => $this->schemas, "maxGrid" => $this->maxGrid);

			if ($this->type == 'custom') $this->customCombinations = $this->getCombinations(); else $this->combinations = $this->getCombinations();
			define('POSITIONS', 1);

		}

		$posName   = ($this->element['name'] == "mainbodyPosition") ? "sidebar" : $this->position_info->id;
		$realCount = $gantry->countModules($posName);
		if ($posName == 'sidebar') $realCount += 1;
		if ($realCount > 0) {
			if (!in_array($realCount, $this->schemas)) $realCount = $this->schemas[0];
			$this->default      = $this->oneCharConversion($this->layoutSchemas[$realCount]);
			$this->defaultCount = $realCount;
		}

		// if the same type of combinations are requested, use the cached ones, otherwise get the new set
		if ($this->type != "custom" && ($this->words != $this->settings["words"] || $this->schemas != $this->settings["schemas"] || $this->maxGrid != $this->settings["maxGrid"])) {
			$this->combinations = $this->getCombinations();
		}

		if ($this->type == "custom") $this->customCombinations = $this->getCombinations();

		if (!in_array((string)$this->defaultCount, $this->schemas)) $this->defaultCount = (int)$this->schemas[0];

		$i             = 0;
		$max_positions = isset($this->position_info) ? $this->position_info->max_positions : false;
		if (!$max_positions) $max_positions = 6;
		foreach ($this->schemas as $scheme) {
			$active = "";
			if ($i >= $max_positions) break;
			if ((int)$scheme == $this->defaultCount) {
				$active        = ' class="active"';
				$currentLayout = $scheme;
			}
			$lis .= '<li' . $active . '><a href="#"><span>' . $scheme . '</span></a></li>';
			$i++;
		}

		$scriptinit = $this->sliderInit($this->id);
		$gantry->addDomReadyScript($scriptinit);
		$gantry->addDomReadyScript($this->showmax($this->id));

		$letters = array('a', 'b', 'c', 'd', 'e', 'f');


		$output = '
		<div class="wrapper">
		<div id="' . $this->id . '-grp" class="g-position">
			<div class="navigation">
				<span class="title">Positions:</span>
				<ul class="list">' . $lis . '</ul>
			</div>
			<div class="clr"></div>
			<div id="' . $this->id . '-wrapper" class="col' . $this->maxGrid . ' miniatures">
				<div class="mini-container layout-grid-' . $currentLayout . '">' . "\n";

		for ($i = 0; $i < $max_positions; $i++) {
			$output .= "<div class=\"mini-grid mini-grid-2\">" . $letters[$i] . "</div>\n";
		}

		$output .= '
				</div>
				<div class="clr"></div>
				<div class="position">
					<div class="position2"></div>
					<div class="knob"></div>
				</div>
			</div>
			<input class="layouts-input" type="hidden" id="' . $this->id . '" name="' . $this->name . '" value=\'';
		$output .= $this->value;
		$output .= '\' />
		</div>
		</div>
		';
		return $output;
	}


	function permutations($letters, $num, $filter = 12)
	{
		// hardcoded cases for speed optimization
		$letter0 = base_convert($letters{0}, 24, 10);
		$letter1 = base_convert($this->lastchar($letters), 24, 10);
		if ($letter0 + $letter1 > $filter) return array();
		if ($filter == 12 && $num == 6) return array("222222");
		if ($num == 1) return $this->oneCharConversion(array($filter));

		$last   = str_repeat($letters{0}, $num);
		$result = array();

		while ($last != str_repeat($this->lastchar($letters), $num)) {
			$tmp = 0;
			for ($i = 0; $i < strlen($last); $i++) $tmp += base_convert($last[$i], 24, 10);
			if ($tmp == $filter) $result[] = $last;

			$last = $this->char_add($letters, $last, $num - 1);
		}

		$tmp = 0;
		for ($i = 0; $i < strlen($last); $i++) $tmp += base_convert($last[$i], 24, 10);
		if ($tmp == $filter) $result[] = $last;

		return $result;
	}

	function char_add($digits, $string, $char)
	{
		if ($string{$char} <> $this->lastchar($digits)) {
			$string{$char} = $digits{strpos($digits, $string{$char}) + 1};
			return $string;
		} else {
			$string = $this->changeall($string, $digits{0}, $char);
			return $this->char_add($digits, $string, $char - 1);
		}
	}

	function lastchar($string)
	{
		return $string{strlen($string) - 1};
	}

	function changeall($string, $char, $start = 0, $end = 0)
	{
		if ($end == 0) $end = strlen($string) - 1;
		for ($i = $start; $i <= $end; $i++) {
			$string{$i} = $char;
		}

		return $string;
	}

	function tryCache($implode, $scheme, $words, $grid = 12)
	{
		/** @var $gantry Gantry */
		global $gantry;

		$md5 = md5($grid . implode("", $words) . $scheme);

		$data = $gantry->positions[$grid]->get($md5);

		if (null == $data) {
			$permutation                    = $this->permutations($implode, (int)$scheme, $grid);
			$save                           = array();
			$save[$grid][$implode][$scheme] = $permutation;

			//file_put_contents($file, serialize($save));
			$gantry->positions[$grid]->set($md5, serialize($save));
			return $permutation;
		} else {
			$unserial = unserialize($data);
			return $unserial[$grid][$implode][$scheme];
		}
	}

	function getCombinations()
	{
		/** @var $gantry Gantry */
		global $gantry;

		if ($this->type == 'custom') return $this->getCustomCombinations();

		$grid  = $this->maxGrid;
		$words = $this->words;
		$sets  = $this->schemas;

		$result = "{";

		$words = $this->oneCharConversion($words);

		foreach ($sets as $set) {
			$implode                       = implode("", $words);
			$output[$grid][$implode][$set] = $this->tryCache($implode, (int)$set, $words, $grid);
			$current                       = $output;

			$tmp = $current[$grid][$implode][$set];
			sort($tmp);
			$result .= "'$set': ['" . implode("', '", $tmp) . "'],";
		}
		$result = substr($result, 0, -1) . "}";
		return $result;
	}

	function getCustomCombinations()
	{
		$sets = $this->schemas;
		$name = str_replace("Position", "Schemas", $this->element['name']);

		$results = "{";
		$keysref = "{";

		foreach ($this->{$name} as $key => $set) {
			$results .= "'$key': [";
			$keysref .= "'$key': [";

			foreach ($set as $combination) {
				$combination = $this->oneCharConversion($combination);

				$results .= "'" . implode("", $combination) . "', ";
				$keysref .= "['" . implode("', '", array_keys($combination)) . "'], ";
			}
			$results = substr($results, 0, -2) . "],";
			$keysref = substr($keysref, 0, -2) . "],";
		}
		$results = substr($results, 0, -1) . "}";
		$keysref = substr($keysref, 0, -1) . "}";

		return array($results, $keysref);
	}

	function oneCharConversion($words, $decode = false)
	{
		$dummy = array();

		foreach ($words as $key => $word) {
			if (!$decode) $dummy[$key] = base_convert((int)$word, 10, 24); else $dummy[$key] = base_convert((int)$word, 24, 10);
		}

		return $dummy;
	}

	function outputCombinations($type = 'combinations')
	{
		if (!is_array($this->combinations) && $this->type != 'custom') return $this->combinations;

		return ($type == 'combinations') ? $this->customCombinations[0] : $this->customCombinations[1];
	}

	function getLoadValue()
	{
		$defaultValue = array($this->defaultCount => $this->default);

		if ($this->type == 'custom') {
			$defaultValue = array($this->defaultCount => $this->defaultMainbodySchemas[$this->maxGrid][$this->defaultCount]);
		}

		if (preg_match("/{/", $this->value)) {
            //clean up magic quotes
            if(@ini_get('magic_quotes_gpc'=='1')){
                $this->value = $this->_smartstripslashes($this->value);
            }
			$value = unserialize($this->value);
			if (isset($value[$this->maxGrid]))
                $value = $value[$this->maxGrid];
            else
				$value = $defaultValue;
		} else {
			$value = $defaultValue;
		}

		$merge = $value + $this->layoutSchemas;

		$result = "{";

		$keynames = '';

		if ($this->type == 'custom') {
			foreach ($this->defaultMainbodySchemas[$this->maxGrid] as $key => $defaults) {
				if (!array_key_exists($key, $value)) {
					$value[$key] = $defaults;
				}
			}

			foreach ($value as $key => $array) {
				$array = $this->oneCharConversion($array);
				$result .= $key . ': {';
				$result .= "'values': ['" . implode("", $array) . "'], ";
				$result .= "'keys': [";
				foreach ($array as $mb => $arr) {
					$result .= '"' . $mb . '", ';
				}
				$result = substr($result, 0, -2);
				$result .= "]}, ";
			}

		} else {
			foreach ($merge as $key => $array) {
				$array = $this->oneCharConversion($array);
				$result .= $key . ': [';
				$result .= "'" . implode("", $array) . "'";
				$result .= "], ";
			}
		}

		$result = substr($result, 0, -2);
		$result .= "}";

		return $result;
	}

	function sliderInit($name, $max = 12)
	{
		$name2     = str_replace("-", "_", $name);
		$slider    = "document.id('" . $name . "-grp').getElement('.position')";
		$knob      = "document.id('" . $name . "-grp').getElement('.knob')";
		$hidden    = "document.id('" . $this->id . "')";
		$activeNav = array_search((string)$this->defaultCount, $this->schemas);

		// hidden, name, maxgrid, loadValue, keyName, type, combinations, defaultCount, schemas
		return "GantryPositions.add('" . $this->id . "', '" . $name . "', " . $this->maxGrid . ", " . $this->getLoadValue() . ", '" . $this->keyName . "', '" . $this->type . "', " . $this->outputCombinations('combinations') . ", " . $this->outputCombinations('keys') . ", " . $activeNav . ");";
	}

	function showmax($name)
	{
		$name2 = str_replace("-", "_", $name);
		return "GantryPositionsTools.showMax('" . $name . "', '" . $name2 . "');";
	}

    protected function _smartstripslashes($str)
   	{
   		$cd1 = substr_count($str, "\"");
   		$cd2 = substr_count($str, "\\\"");
   		$cs1 = substr_count($str, "'");
   		$cs2 = substr_count($str, "\\'");
   		$tmp = strtr($str, array("\\\"" => "", "\\'" => ""));
   		$cb1 = substr_count($tmp, "\\");
   		$cb2 = substr_count($tmp, "\\\\");
   		if ($cd1 == $cd2 && $cs1 == $cs2 && $cb1 == 2 * $cb2) {
   			return stripslashes(strtr($str, array("\\\"" => "\"", "\\'" => "'", "\\\\" => "\\")));
   		}
   		return stripslashes($str);
   	}
}

?>
