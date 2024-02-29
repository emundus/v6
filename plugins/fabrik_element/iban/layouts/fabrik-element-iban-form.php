<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

defined('JPATH_BASE') or die;

if(version_compare(JVERSION, '4', '>=')) {
	$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
    $wa->registerAndUseStyle('fabrik-iban', Uri::root() . 'plugins/fabrik_element/iban/assets/iban.css');
    $wa->registerAndUseScript('imask', Uri::root() . 'plugins/fabrik_element/iban/assets/js/emundus_imask-min.js');
} else {
	$doc = Factory::getDocument();
	$doc->addStylesheet(Uri::root() . "plugins/fabrik_element/iban/assets/iban.css");
	$doc->addScript(Uri::root() . "plugins/fabrik_element/iban/assets/js/emundus_imask-min.js");
}

Text::script('PLG_ELEMENT_IBAN_INVALID');

$d = $displayData;
?>

<fieldset class="fabrikSubElementContainer fabrikIban">
    <input type="text" class="fabrikinput inputbox" name="<?php echo $d->attributes['name'];?>"
           id="<?php echo $d->attributes['id']?>" value="<?php echo $d->value; ?>" />
</fieldset>




