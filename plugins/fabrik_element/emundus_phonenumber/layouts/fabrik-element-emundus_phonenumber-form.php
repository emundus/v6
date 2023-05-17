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

<style>

	#renderCountryCode
	{
		height: 46px;
		width: 40px;
		text-align: center;
		padding: 0 6px 0 6px;
		border-radius: unset;
		border-left: 0;
		border-right: 0;
	}

	#inputValue
	{
		height: 46px;
		border-left: 0;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        border-color: rgb(137, 144, 169);
		padding-left: 4px;
	}

	.check
	{
		opacity: 0;
		position: absolute;
	}

	.fabrikEmundusPhoneNumber .chzn-container{
		width: auto !important;
	}

    .fabrikEmundusPhoneNumber .chzn-container .chzn-drop{
	    width: max-content;
        border-radius: 8px;
        margin-top: 6px;
    }

    .fabrikEmundusPhoneNumber .chzn-container .chzn-drop .chzn-search{
        padding: 4px;
    }

    .fabrikEmundusPhoneNumber .chzn-container .chzn-drop .chzn-search input{
        height: 35px;
        background: unset;
    }

    .fabrikEmundusPhoneNumber .chzn-container .chzn-drop .chzn-results {
        margin: 4px;
    }

    .fabrikEmundusPhoneNumber .chzn-container .chzn-drop .chzn-results li{
        font-size: 14px;
	    word-spacing: 4px;
        margin-right: 4px;
    }

    .fabrikEmundusPhoneNumber .chzn-container .chzn-drop .chzn-results li.highlighted{
        background-color: #D1E9FF;
        border-radius: 8px;
    }

    .fabrikEmundusPhoneNumber .chzn-container .chzn-single{
        display: flex;
        justify-content: center;
        align-items: center;
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
        background: #FAFAFB !important;
    }

    .fabrikEmundusPhoneNumber .chzn-container .chzn-single span{
        font-size: 20px;
        margin-right: 16px;
    }
    .fabrikEmundusPhoneNumber .chzn-container .chzn-single span img{
        width: 24px;
    }

    .fabrikEmundusPhoneNumber .chzn-container .chzn-single div{
        height: auto;
        width: 8px;
        position: static;
        margin-right: 4px;
        margin-top: -4px;
    }

    .fabrikEmundusPhoneNumber .chzn-container .chzn-single div b{
        border: solid black;
        border-width: 0 2px 2px 0;
        display: inline-block;
        transform: rotate(45deg);
        -webkit-transform: rotate(45deg);
        background: unset;
        position: static;
        padding: 3px;
    }

</style>

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
