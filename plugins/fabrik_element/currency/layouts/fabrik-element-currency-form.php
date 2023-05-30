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
        width: max-content;
        height: 46px;
        border-left: 0;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

    .marginNone
    {
        margin: 0 !important;
    }

</style>

<div id="<?php echo $displayData->attributes['id']; ?>" class="em-flex-row fabrikSubElementContainer marginNone" >

    <input id="currency_inputValue" class="fabrikinput input-medium"
           autocomplete="off" />

    <input id="currency_rowInputValue" name="<?php echo $displayData->attributes['name'].'[rowInputValueFront]' ?>" hidden="hidden">


    <select id="currency_selectValue" name="<?php echo $displayData->attributes['name'].'[selectedIso3Front]'; ?>" class="fabrikinput">

    </select>

</div>
