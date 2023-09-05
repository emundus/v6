<?php

defined('JPATH_BASE') or die;

// Add span with id so that element fxs work.
$d = $displayData;

JText::script('PLG_ELEMENT_INSEE_SIRET_NOT_FOUND');
JText::script('PLG_ELEMENT_INSEE_ERROR');
JText::script('PLG_ELEMENT_INSEE_SIRET_CLOSED');
?>

<div id="<?php echo $d->attributes['id']; ?>" class="em-flex-row fabrikSubElementContainer inseeElement">
    <input id="insee_inputValue" name="<?php echo $d->attributes['name']; ?>" class="fabrikinput input-medium" value="<?php echo $d->attributes['value']; ?>" />
</div>
