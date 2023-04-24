<?php

defined('JPATH_BASE') or die;

// Add span with id so that element fxs work.
$doc = JFactory::getDocument();
$doc->addScript(JURI::root() . "plugins/fabrik_element/emundus_phonenumber/assets/js/emundus_phonenumber_libphone-min.js");
$doc->addScript(JURI::root() . "plugins/fabrik_element/emundus_phonenumber/assets/css/emundus_phonenumber.css");
?>

<div id="div_<?php echo $displayData->attributes['name']; ?>" class="divBox">

	<select id="div_emundus_select_phone_code" class="input-small fabrikinput inputbox"
			data-countries="<?php echo base64_encode(json_encode($displayData->dataSelect)); // encode base64?>"
	>

		<?php foreach ($displayData->dataSelect as $key => $value) : // petit boucle pour les montrer et roule ! ?>

		<option value="<?php echo $value->iso2 ?>"><?php echo $value->iso2 ?> <span class="emoji"><?php echo $value->flag ?></span></option>

		<?php endforeach; ?>

	</select>

	<input id="div_emundus_phone" class="input-medium fabrikinput inputbox text"
		   name="<?php echo $displayData->attributes['name']; ?>"
	>
</div>
