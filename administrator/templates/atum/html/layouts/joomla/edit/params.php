<?php

/* In this file we will load in the standard joomla layout, modify it so it works for us, and then return the output
	Only modify in Fabrik menues or modules
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Fabrik\Helpers\Php;

/* The following is used by the install/upgrade script to validate whether an installed override is ours or not */
$validationtag = 'FABRIK_JOOMLA_EDIT_LAYOUT_OVERRIDE';

$originalLayout = JPATH_ROOT."/layouts/joomla/edit/params.php";

$formData = $displayData->getForm()->getData();
$fabrikMenu = $displayData->getName() == 'item' && stristr($formData->get('link',''),'com_fabrik');
$fabrikModule = $displayData->getName() == 'module' && stristr($formData->get('module',''),'mod_fabrik');

if (!$fabrikMenu && !$fabrikModule) 
{
	require_once $originalLayout;
	return;
}

$targets = ["\$displayData->get('ignore_fieldsets') ?: array();", 
			"\$displayData->get('ignore_fieldsets') ?: [];",
			"//fieldset[not(ancestor::field/form/*)]');"];
$replacement = ["array_merge(\$displayData->get('ignore_fieldsets') ?: array(),  ['list_elements_modal', 'prefilters_modal', 'ordering_modal']);",
				"array_merge(\$displayData->get('ignore_fieldsets') ?: [],  ['list_elements_modal', 'prefilters_modal', 'ordering_modal']);",
				"//fieldset[not(ancestor::field/form/*)]//fieldset[not(ancestor::field/fields/*)]');"];

$buffer = file_get_contents($originalLayout);

foreach ($targets as $key=>$target) {
	$pos = strpos($buffer, $target); 
	if ($pos === false) {
		/* Enque a message */
	} else {
		$buffer = substr_replace($buffer, $replacement[$key], $pos, strlen($target));
	}
}

echo Php::Eval(['code' => '?>'.$buffer.PHP_EOL, 'vars' => ['displayData'=>$displayData]]);
