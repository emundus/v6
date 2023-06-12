<?php

defined('JPATH_BASE') or die;

$doc = JFactory::getDocument();
$doc->addScript(JURI::root() . "plugins/fabrik_element/currency/assets/js/emundus_imask-min.js");

?>

<style>

    #currency_inputValue
    {
        height: 46px;
        border-right: 0;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    #currency_selectValue
    {
        width: auto;
        font-size: 16px;
        padding: 0 12px 0 12px !important;
        height: 46px;
        border-left: 0;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .marginNone
    {
        margin: 0 !important;
    }

    .currencyElement .chzn-container{
        width: auto !important;
    }

    .currencyElement .chzn-container .chzn-drop{
        width: max-content;
        margin-top: 6px;
    }

    .currencyElement .chzn-container .chzn-drop .chzn-search{
        padding: 4px;
    }

    .currencyElement .chzn-container .chzn-drop .chzn-search input{
        height: 35px;
        background: unset;
    }

    .currencyElement .chzn-container .chzn-drop .chzn-results {
        margin: 4px;
    }

    .currencyElement .chzn-container .chzn-drop .chzn-results li{
        font-size: 16px;
        word-spacing: 4px;
        margin-right: 4px;
    }

    .currencyElement .chzn-container .chzn-drop .chzn-results li.highlighted{
        background-color: #D1E9FF;
    }

    .currencyElement .chzn-container .chzn-single{
        border-left: 0 !important;
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
    }

    .currencyElement .chzn-container .chzn-single span{
        font-size: 16px;
        margin-right: 16px;
    }

    .currencyElement .chzn-container .chzn-single div{
        height: auto;
        width: 8px;
        position: static;
        margin-right: 4px;
    }

    .currencyElement .chzn-container .chzn-single div b{
        border: solid black;
        border-width: 0 2px 2px 0;
        display: inline-block;
        transform: rotate(45deg);
        -webkit-transform: rotate(45deg);
        background: unset;
        position: static;
        padding: 3px;
    }

    .currencyElement:hover
    {
        border-color: var(--neutral-600);
    }

    .currencyElement:focus
    {
        border-color: var(--blue-500);
    }

</style>

<div id="<?php echo $displayData->attributes['id']; ?>" class="em-flex-row fabrikSubElementContainer marginNone currencyElement" >

    <input id="currency_inputValue" name="<?php echo $displayData->attributes['name']; ?>" class="fabrikinput input-medium"
           autocomplete="off" value="<?php echo $displayData->attributes['inputValue']; ?>">

    <select id="currency_selectValue" name="<?php echo $displayData->attributes['name'].'[selectedIso3Front]'; ?>" class="fabrikinput">

        <?php foreach ($displayData->attributes['valuesForSelect'] as $key => $value) :?>

            <option value="<?php echo $key ?>"

                <?php if ($displayData->attributes['iso3SelectedCurrency'] === $key) : ?>
                    selected="selected"
                <?php endif ?>

            ><?php echo $value ?></option>

        <?php endforeach; ?>
    </select>

    <input id="currency_rowInputValue" name="<?php echo $displayData->attributes['name'].'[rowInputValueFront]'; ?>" hidden="hidden" class="fabrikinput">

</div>
