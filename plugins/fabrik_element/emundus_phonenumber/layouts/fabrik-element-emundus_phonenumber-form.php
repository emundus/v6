<?php

defined('JPATH_BASE') or die;

// Add span with id so that element fxs work.
$doc = JFactory::getDocument();
$doc->addScript(JURI::root() . "plugins/fabrik_element/emundus_phonenumber/assets/js/emundus_phonenumber_libphone-min.js");
$doc->addScript(JURI::root() . "plugins/fabrik_element/emundus_phonenumber/assets/js/emundus_phonenumber_ValidatorJS.js");

JText::script('PLG_ELEMENT_PHONE_NUMBER_INVALID');
JText::script('PLG_ELEMENT_PHONE_NUMBER_UNSUPPORTED');
?>

<div id="<?php echo $displayData->attributes['id']; ?>" class="em-flex-row fabrikSubElementContainer">

	<select id="countrySelect" name="<?php echo $displayData->attributes['name'].'[country]'; ?>" class="input-small fabrikinput"
			selectedValue="<?echo $displayData->attributes['selectValue']; ?>"
	>

		<?php foreach ($displayData->dataSelect as $key => $value) :?>

		<option value="<?php echo $value->iso2 ?>"><?php echo $value->iso2 ?> <span class="emoji"><?php echo $value->flag ?></span></option>

		<?php endforeach; ?>

	</select>

	<input id="inputValue" name="<?php echo $displayData->attributes['name'].'[num_tel]'; ?>" class="input-medium fabrikinput em-ml-8" maxlength="16"
		   value="<?php echo $displayData->attributes['inputValue']; ?>"
	>
	<input id="validationValue" type="checkbox" style="opacity: 0; position: absolute;" name="<?php echo $displayData->attributes['name'].'[is_valid]'; ?>"

		   <?php if ($displayData->attributes['isValid'] == '1') :?>
			checked
		<?php endif ?>
	>

</div>
