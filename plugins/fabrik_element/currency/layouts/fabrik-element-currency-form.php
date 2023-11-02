<?php

defined('JPATH_BASE') or die;

$doc = JFactory::getDocument();
$doc->addScript(JURI::root() . "plugins/fabrik_element/currency/assets/js/emundus_imask-min.js");

?>

<div id="<?php echo $displayData->attributes['id']; ?>" class="em-flex-row fabrikSubElementContainer marginNone currencyElement <?php echo $displayData->bootstrap_class?>" >

    <input id="currency_inputValue" name="<?php echo $displayData->attributes['name']; ?>" class="fabrikinput input-medium"
           autocomplete="off" type="text" value="<?php echo $displayData->attributes['inputValue']; ?>">

    <select id="currency_selectValue" name="<?php echo $displayData->attributes['name'].'[selectedIso3Front]'; ?>" class="fabrikinput">

        <?php foreach ($displayData->attributes['valuesForSelect'] as $key => $value) :?>

            <option value="<?php echo $key ?>"

                <?php if ($displayData->attributes['iso3SelectedCurrency'] === $key) : ?>
                    selected="selected"
                <?php endif ?>

            ><?php echo $value ?></option>

        <?php endforeach; ?>
    </select>

    <input id="currency_rowInputValue" name="<?php echo $displayData->attributes['name'].'[rowInputValueFront]'; ?>" type="hidden" class="fabrikinput com_emundus_hidden">
    <input id="currency_displayiso3" name="<?php echo $displayData->attributes['name'].'[currency_displayiso3]'; ?>" type="hidden" value="<?php echo $displayData->displayiso3; ?>" class="fabrikinput com_emundus_hidden">
</div>
