<?php

defined('JPATH_BASE') or die;

// Add span with id so that element fxs work.
$doc = JFactory::getDocument();
$doc->addScript('https://unpkg.com/imask');
$doc->addScript(JURI::root() . "plugins/fabrik_element/emundus_phonenumber/assets/js/emundus_phonenumber_libphone-min.js");
$doc->addScript(JURI::root() . "plugins/fabrik_element/emundus_phonenumber/assets/js/emundus_phonenumber_ValidatorJS.js");
$doc->addScript(JURI::root() . "plugins/fabrik_element/emundus_phonenumber/assets/js/emundus_imask-min.js");

$lang = JFactory::getLanguage();
$actualLanguage = !empty($lang->getTag()) ? substr($lang->getTag(), 0 , 2) : 'fr';

// able to use these errors in JS
JText::script('PLG_ELEMENT_PHONE_NUMBER_INVALID');
JText::script('PLG_ELEMENT_PHONE_NUMBER_UNSUPPORTED');
?>


<div id="<?php echo $displayData->attributes['id']; ?>" class="em-flex-row fabrikSubElementContainer fabrikEmundusPhoneNumber">

	<select id="countrySelect" name="<?php echo $displayData->attributes['name'].'[country]'; ?>" class="em-w-auto fabrikinput"
			selectedValue="<?php echo $displayData->attributes['selectValue']; ?>"
	>

		<?php foreach ($displayData->dataSelect as $key => $value) :?>

		<option value="<?php echo $value->iso2 ?>" data-flag="<?php echo $value->flag_img ?>" data-countrycode=""><?php echo $value->{'label_'.$actualLanguage} ?></option>

		<?php endforeach; ?>

	</select>

	<div class="em-flex-row-end em-h-auto em-w-100">

		<input id="renderCountryCode" name="<?php echo $displayData->attributes['name'].'[country_code]'; ?>" tabindex="-1" class="input-medium fabrikinput input-readonly" readonly="readonly">

		<input id="inputValue" autocomplete="tel" name="<?php echo $displayData->attributes['name'].'[num_tel]'; ?>" class="input-medium fabrikinput" maxlength="16"
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
