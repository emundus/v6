<?php

defined('JPATH_BASE') or die;


?>

<style>

    #currency_inputValue
    {
        height: 46px;
        border-right: 0;
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    #currency_selectedValue
    {
        width: 5em;
        height: 46px;
        border-left: 0;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        padding-left: 4px;
    }

</style>

<div id="<?php echo $displayData->attributes['id']; ?>" class="em-flex-row fabrikSubElementContainer">

    <input id="currency_inputValue" name="<?php echo $displayData->attributes['name']['inputValue'] ?>" class="form-control fabrikinput input-medium"
    />

    <select id="currency_selectedValue" name="<?php echo $displayData->attributes['name']['selectValue']?>" class="fabrikinput">

    </select>

</div>
