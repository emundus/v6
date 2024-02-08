<?php

use Joomla\CMS\Factory;

defined('JPATH_BASE') or die;

$doc = JFactory::getDocument();
$doc->addScript(JURI::root() . "plugins/fabrik_element/emundus_colorpicker/assets/swatch.js");
$doc->addStylesheet(JURI::root() . "plugins/fabrik_element/emundus_colorpicker/assets/swatch.css");

$d = $displayData;
?>

<fieldset id="<?php echo $d->attributes['name']; ?>" class="fabrikSubElementContainer fabrikEmundusColorpicker color-swatches js-color-swatches">
    <legend class="hidden color-swatches__legend text-sm lg:text-base text-gray-500 mb-2 lg:mb-3" aria-live="polite" aria-atomic="true">Color: <span class="color-swatches__color text-gray-700 js-color-swatches__color">Jet</span></legend>

    <select class="js-color-swatches__select fabrikinput" id="<?php echo $d->attributes['id']; ?>" aria-label="Select a color">
        <option value="Jet" data-style="background-color: #34252F;">Jet</option>
        <option value="Arsenic" data-style="background-color: #3B5249;">Arsenic</option>
        <option value="Wintergreen" data-style="background-color: #519872;">Wintergreen</option>
        <option value="Laurel Green" data-style="background-color: #A4B494;">Laurel Green</option>
    </select>
</fieldset>




