<?php

defined('JPATH_BASE') or die;

// Add span with id so that element fxs work.
$d = $displayData;

$dataSelect = $d->dataSelect; // on récup les données pour les options du select
?>



<style>

	.test2{
		display: flex;
		flex-direction: row;
	}

	#div_emundus_phone0:valid {
    	background-color: palegreen;
	}

	#div_emundus_phone0:invalid {
		background-color: lightpink;
	}

</style>

<div id="div_<?php echo $d->attributes['name']; ?>" class="test2">


	<select id="div_emundus_select_phone_code" class="input-small fabrikinput inputbox">

		<?php foreach ($dataSelect as $key => $value) : // petit boucle pour les montrer et roule ! ?>

		<option value="<?php echo $value->iso2 ?>"><?php echo $value->iso2 ?> <span class="emoji"><?php echo $value->flag ?></span></option>

		<?php endforeach; ?>

	</select>


	<input id="div_emundus_phone0" class="input-xlarge fabrikinput text">
</div>



<script> // méthode et fonctions

	const changePlaceholder = (texte) =>
	{
		input.placeholder = texte;
	}


	const prepareMaskFormat = () =>
	{
		return "\\"+countrySelected.country_code+countrySelected.area_code+countrySelected.subscriber_number;
	};

	const newCountry = (id) =>
	{
		indiceCountry = id;
		countrySelected = allCountry[indiceCountry];
	}

	const prepareInput = () =>
	{
		input.pattern=prepareMaskFormat();
		input.required = true;
		input.value="";
	}

	const handlerInputChange = (props) =>
	{
		newCountry(props.target.options.selectedIndex);
		prepareInput();
	};

</script>


<script> // lien vers le front pour les évènements

	let indiceCountry;
	let countrySelected; // je récup celui qui est par défaut sous format d'objet
	const allCountry = <?php echo json_encode($dataSelect); ?>;


	const select = document.getElementById("div_emundus_select_phone_code");
	const input = document.getElementById("div_emundus_phone0");
	newCountry(0);


	select.addEventListener("change", handlerInputChange);

</script>





