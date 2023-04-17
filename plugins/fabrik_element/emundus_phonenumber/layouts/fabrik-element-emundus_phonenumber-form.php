<?php

defined('JPATH_BASE') or die;

// Add span with id so that element fxs work.
$d = $displayData;
?>


<!--
<style>

	.test2{
		display: flex;
		flex-direction: row;
	}

</style>
-->

<div id="div_<?php echo $d->attributes['name']; ?>" class="test2">


	<select id="div_emundus_select_phone_code" class="input-small fabrikinput inputbox">
		<option value="non">non</option>
		<option value="oui">oui</option>
	</select>


	<input id="div_emundus_phone0"
		<?php foreach ($d->attributes as $key => $value) :
			echo $key . '="' . $value . '" ';
		endforeach;

		?>
	>
</div>





<!--  TEST POUR MOI POUR PLUS TARD

<script>

	const handlerInputChange = (props) =>
	{
		if (props.target.value === "oui")
		{
			addOneToInput();
		}
	};

	const addOneToInput = () =>
	{
		let input = document.getElementById("div_emundus_phone0");
		input.value+="1";
	}

</script>


<script>
	let select = document.getElementById("div_emundus_select_phone_code");
	select.addEventListener("change", handlerInputChange);
</script>

->


