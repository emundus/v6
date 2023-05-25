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
        width: 7em;
        height: 46px;
        border-left: 0;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }

</style>

<div id="<?php echo $displayData->attributes['id']; ?>" class="em-flex-row fabrikSubElementContainer">

    <input id="currency_inputValue" name="<?php echo $displayData->attributes['name'].'[inputValueFront]'; ?>" class="fabrikinput input-medium"
    />

    <select id="currency_selectValue" name="<?php echo $displayData->attributes['name'].'[selectValueFront]'; ?>" class="fabrikinput">

    </select>

</div>
