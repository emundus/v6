<?php

defined('JPATH_BASE') or die;

$d = $displayData;
/*
 * There's no longer a star-empty (fa- or icon-) in joomla-fontawesome (or in fontawesome 5), so use unicode symbol
*/
?>
<style>.starRating.icon-star-empty::before {
    content: "\2730";
	font-size:1.35em;
}</style>
<div id="<?php echo $d->id; ?>_div" class="fabrikSubElementContainer">
	<?php
	$imgOpts = array('icon-class' => 'small', 'style' => $d->css, 'data-rating' => -1);
	$clearImg = FabrikHelperHTML::image('remove.png', 'list', $d->tmpl, $imgOpts);
	$roundedAvg = round($d->avg);

	if ($d->ratingNoneFirst && $d->canRate)
	{
		echo $d->clearImg;
	}

	$imgOpts = array('icon-class' => 'starRating', 'style' => $d->css);

	for ($s = 0; $s < $roundedAvg; $s++)
	{
		$imgOpts['data-rating'] = $s + 1;
		echo FabrikHelperHTML::image("star", 'list', $d->tmpl, $imgOpts);
	}

	for ($s = $roundedAvg; $s < 5; $s++)
	{
		$imgOpts['data-rating'] = $s + 1;
		echo FabrikHelperHTML::image("star-empty", 'list', $d->tmpl, $imgOpts);
	}

	if (!$d->ratingNoneFirst && $d->canRate)
	{
		echo  $d->clearImg;
	}
	?>
		<span class="ratingScore badge bg-info"><?php echo $d->avg; ?></span>
		<div class="ratingMessage">
            &nbsp;
		</div>
		<input class="fabrikinput input" type="hidden" name="<?php echo $d->name;?>" id="<?php echo $d->id; ?>" value="<?php echo $d->value; ?>" />
	</div>
