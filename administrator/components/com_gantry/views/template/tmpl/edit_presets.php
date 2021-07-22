<?php
/**
 * @package   gantry
 * @subpackage core
 * @version   4.1.43 April  1, 2020
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2020 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
/** @var $gantry Gantry */
		global $gantry;
?>
<div id="hack-panel">
	<?php
	$fields = $this->gantryForm->getFullFieldset('toolbar-panel');
	foreach($fields as $name => $field) {
		//$gantry->addDomReadyScript("Gantry.ToolBar.add('".$field->type."');");

		$status = JFactory::getApplication()->input->cookie->getString('gantry-'.$gantry->templateName.'-adminpresets','hide');
		$style = ' style="display: none";';

		if ($status != 'hide'){
			$status = 'hide';
			$style = '';
		}

		echo "<div id=\"contextual-".$field->type."-wrap\" class=\"contextual-custom-wrap\"".$style.">\n";
		echo "		<div class=\"metabox-prefs\">\n";

		echo $field->input;

		echo "		</div>\n";
		echo "</div>\n";
	}
	?>
</div>

