<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

defined('JPATH_BASE') or die;

if(version_compare(JVERSION, '4', '>=')) {
	$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
    $wa->registerAndUseScript('fabrik-emundus-colorpicker-swatches', Uri::root() . 'plugins/fabrik_element/emundus_colorpicker/assets/swatches.js', ['defer' => true]);
    $wa->registerAndUseStyle('fabrik-emundus-colorpicker-swatches', Uri::root() . 'plugins/fabrik_element/emundus_colorpicker/assets/swatches.css');
} else {
	$doc = Factory::getDocument();
	$doc->addScript(Uri::root() . "plugins/fabrik_element/emundus_colorpicker/assets/swatches.js");
	$doc->addStylesheet(Uri::root() . "plugins/fabrik_element/emundus_colorpicker/assets/swatches.css");
}

$d = $displayData;
?>

<fieldset class="fabrikSubElementContainer fabrikEmundusColorpicker color-swatches js-color-swatches mt-2">
    <p style="display: none" class="color-swatches__legend text-sm lg:text-base text-gray-500 mb-2 lg:mb-3" aria-live="polite"
            aria-atomic="true"><span class="color-swatches__color text-gray-700 js-color-swatches__color"></span>
    </p>

    <select class="js-color-swatches__select fabrikinput" name="<?php echo $d->attributes['name'] ?>"
            value="<?php echo $d->attributes['value']; ?>"
            id="<?php echo $d->attributes['id']; ?>" aria-label="Select a color">
		<?php foreach ($d->colors as $value => $color) : ?>
            <option value="<?php echo $value; ?>"
                    <?php if($value == $d->attributes['value']) : ?>selected<?php endif; ?>
                    data-style="background-color: <?php echo $color; ?>;"><?php echo $value; ?></option>
		<?php endforeach; ?>
    </select>
</fieldset>




