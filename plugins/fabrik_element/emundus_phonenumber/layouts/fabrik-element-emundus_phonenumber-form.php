<?php

defined('JPATH_BASE') or die;

// Add span with id so that element fxs work.
$doc = JFactory::getDocument();
$doc->addScript(JURI::root() . "plugins/fabrik_element/emundus_phonenumber/assets/js/emundus_phonenumber_libphone-min.js");
$doc->addScript(JURI::root() . "plugins/fabrik_element/emundus_phonenumber/assets/js/emundus_phonenumber_ValidatorJS.js");
?>

<div id="<?php echo $displayData->attributes['id']; ?>" class="em-flex-row fabrikSubElementContainer">

	<select class="input-small fabrikinput"
			data-countries="<?php echo base64_encode(json_encode($displayData->dataSelect)); // encode base64?>"
	>

		<?php foreach ($displayData->dataSelect as $key => $value) : // petit boucle pour les montrer et roule ! ?>

		<option value="<?php echo $value->iso2 ?>"><?php echo $value->iso2 ?> <span class="emoji"><?php echo $value->flag ?></span></option>

		<?php endforeach; ?>

	</select>

	<input name="<?php echo $displayData->attributes['name']; ?>" class="input-medium fabrikinput em-ml-8" maxlength="16"
		   value="<?php echo $displayData->attributes['value']; ?>"
	>
</div>
