<?php

defined('JPATH_BASE') or die;

// Add span with id so that element fxs work.
$doc = JFactory::getDocument();
$doc->addScript('https://unpkg.com/imask');
$doc->addScript(JURI::root() . "plugins/fabrik_element/emundus_phonenumber/assets/js/emundus_phonenumber_libphone-min.js");
$doc->addScript(JURI::root() . "plugins/fabrik_element/emundus_phonenumber/assets/js/emundus_phonenumber_ValidatorJS.js");
$doc->addScript(JURI::root() . "plugins/fabrik_element/emundus_phonenumber/assets/js/emundus_imask-min.js");

// able to use these errors in JS
JText::script('PLG_ELEMENT_PHONE_NUMBER_INVALID');
JText::script('PLG_ELEMENT_PHONE_NUMBER_UNSUPPORTED');
?>

<style>

	#renderCountryCode
	{
		height: 46px;
		width: 3em;
		text-align: center;
		padding: 0 6px 0 6px;
		border-bottom-right-radius: 0;
		border-top-right-radius: 0;
		position: absolute;
		background: var(--neutral-100) ;
	}

	#inputValue
	{
		height: 46px;
		margin-left: 4em;
		border-left: 0;
	}

	.check
	{
		opacity: 0;
		position: absolute;
	}

</style>

<div id="<?php echo $displayData->attributes['id']; ?>" class="em-flex-row fabrikSubElementContainer">

	<select id="countrySelect" name="<?php echo $displayData->attributes['name'].'[country]'; ?>" class="input-small fabrikinput"
			selectedValue="<?php echo $displayData->attributes['selectValue']; ?>"
	>

		<?php foreach ($displayData->dataSelect as $key => $value) :?>

		<option value="<?php echo $value->iso2 ?>"><?php echo $value->iso2 ?> <span><?php echo $value->flag ?></span></option>

		<?php endforeach; ?>

	</select>

	<div class="em-flex-row-end em-h-auto em-w-100">

		<input id="renderCountryCode" name="<?php echo $displayData->attributes['name'].'[country_code]'; ?>" tabindex="-1" class="input-medium fabrikinput em-ml-8 input-readonly" readonly="readonly">

		<input id="inputValue" name="<?php echo $displayData->attributes['name'].'[num_tel]'; ?>" class="input-medium fabrikinput" maxlength="16"
			   value="<?php echo $displayData->attributes['inputValue']; ?>" autocomplete="off"
		>

		<input id="hasValidation" type="checkbox" class="check"
			<?php if ($displayData->attributes['mustValidate']) :?>
				checked
			<?php endif ?>
		>

		<input id="validationValue" name="<?php echo $displayData->attributes['name'].'[is_valid]'; ?>" class="fabrikinput check" type="checkbox"
		>
	</div>

</div>
