<?php

defined('JPATH_BASE') or die;

$d = $displayData;
?>
<style>.starRating.icon-star-empty::before {
    content: "\2730";
	font-size:1.35em;
}</style>
<div class="fabrikRating" style="position:relative;">
	<?php
	$imgOpts = array('icon-class' => 'starRating', 'style' => $d->css);
    $roundedAvg = round($d->avg);

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

	?>
    <span class="ratingScore badge bg-info text-light"><?php echo $d->avg; ?></span>
    <div class="ratingMessage">
    </div>
</div>
