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
Text::script('PLG_ELEMENT_IBAN_LOCALIZATION');

# COUNTRY
Text::script('PLG_ELEMENT_IBAN_FRANCE');
Text::script('PLG_ELEMENT_IBAN_GERMANY');
Text::script('PLG_ELEMENT_IBAN_ITALY');
Text::script('PLG_ELEMENT_IBAN_SPAIN');
Text::script('PLG_ELEMENT_IBAN_UNITED_KINGDOM');
Text::script('PLG_ELEMENT_IBAN_AUSTRIA');
Text::script('PLG_ELEMENT_IBAN_BELGIUM');
Text::script('PLG_ELEMENT_IBAN_BULGARIA');
Text::script('PLG_ELEMENT_IBAN_BENIN');
Text::script('PLG_ELEMENT_IBAN_CROATIA');
Text::script('PLG_ELEMENT_IBAN_CYPRUS');
Text::script('PLG_ELEMENT_IBAN_CZECH_REPUBLIC');
Text::script('PLG_ELEMENT_IBAN_DENMARK');
Text::script('PLG_ELEMENT_IBAN_ESTONIA');
Text::script('PLG_ELEMENT_IBAN_FINLAND');
Text::script('PLG_ELEMENT_IBAN_GREECE');
Text::script('PLG_ELEMENT_IBAN_HUNGARY');
Text::script('PLG_ELEMENT_IBAN_IRELAND');
Text::script('PLG_ELEMENT_IBAN_LATVIA');
Text::script('PLG_ELEMENT_IBAN_LITHUANIA');
Text::script('PLG_ELEMENT_IBAN_LUXEMBOURG');
Text::script('PLG_ELEMENT_IBAN_MALTA');
Text::script('PLG_ELEMENT_IBAN_NETHERLANDS');
Text::script('PLG_ELEMENT_IBAN_POLAND');
Text::script('PLG_ELEMENT_IBAN_PORTUGAL');
Text::script('PLG_ELEMENT_IBAN_ROMANIA');
Text::script('PLG_ELEMENT_IBAN_SLOVAKIA');
Text::script('PLG_ELEMENT_IBAN_SLOVENIA');
Text::script('PLG_ELEMENT_IBAN_SWEDEN');
Text::script('PLG_ELEMENT_IBAN_NORWAY');
Text::script('PLG_ELEMENT_IBAN_SWITZERLAND');
Text::script('PLG_ELEMENT_IBAN_ICELAND');
Text::script('PLG_ELEMENT_IBAN_LIECHTENSTEIN');
Text::script('PLG_ELEMENT_IBAN_MONACO');
Text::script('PLG_ELEMENT_IBAN_SAN_MARINO');
Text::script('PLG_ELEMENT_IBAN_VATICAN_CITY');
Text::script('PLG_ELEMENT_IBAN_ALBANIA');
Text::script('PLG_ELEMENT_IBAN_ANDORRA');
Text::script('PLG_ELEMENT_IBAN_BOSNIE_HERZEGOVINE');
Text::script('PLG_ELEMENT_IBAN_KOSOVO');
Text::script('PLG_ELEMENT_IBAN_FEROE_ISLANDS');
Text::script('PLG_ELEMENT_IBAN_GEORGIA');
Text::script('PLG_ELEMENT_IBAN_UKRAINE');
Text::script('PLG_ELEMENT_IBAN_SAINT_LUCIA');
Text::script('PLG_ELEMENT_IBAN_SEYCHELLES');
Text::script('PLG_ELEMENT_IBAN_EGYPT');
Text::script('PLG_ELEMENT_IBAN_COSTA_RICA');
Text::script('PLG_ELEMENT_IBAN_BELARUS');
Text::script('PLG_ELEMENT_IBAN_TURKEY');
Text::script('PLG_ELEMENT_IBAN_TUNISIA');
Text::script('PLG_ELEMENT_IBAN_SAUDI_ARABIA');
Text::script('PLG_ELEMENT_IBAN_QATAR');
Text::script('PLG_ELEMENT_IBAN_PALESTINE');
Text::script('PLG_ELEMENT_IBAN_PAKISTAN');
Text::script('PLG_ELEMENT_IBAN_MAURITIUS');
Text::script('PLG_ELEMENT_IBAN_MOROCCO');
Text::script('PLG_ELEMENT_IBAN_GIBRALTAR');
Text::script('PLG_ELEMENT_IBAN_SERBIA');
Text::script('PLG_ELEMENT_IBAN_MONTENEGRO');
Text::script('PLG_ELEMENT_IBAN_MACEDONIA');
Text::script('PLG_ELEMENT_IBAN_REPUBLIC_OF_MOLDOVA');
Text::script('PLG_ELEMENT_IBAN_GREENLAND');


$d = $displayData;
?>

<fieldset class="fabrikSubElementContainer fabrikIban">
    <input type="text" class="fabrikinput inputbox" name="<?php echo $d->attributes['name'];?>"
           id="<?php echo $d->attributes['id']?>" value="<?php echo $d->value; ?>" />
    <div class="flex items-center justify-start gap-1 mt-2 localization-block">
        <span class="text-xs">
            <?php echo Text::_('PLG_ELEMENT_IBAN_LOCALIZATION'); ?>
        </span>
        <span class="text-xs localization"></span>
    </div>
</fieldset>




