<?php

defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;

$d = $displayData;
?>
<div id="<?php echo $d->id; ?>" class="fabrikSubElementContainer">
<?php
	if ($d->showNone) :
?>
		<button class="btn btn-mini clearslider pull-left" style="margin-right:10px"><?php echo FabrikHelperHTML::icon('icon-remove'); ?></button>
<?php
	endif;
?>

	<div class="slider_cont" style="width:<?php echo $d->width; ?>px;">
		<div class="fabrikslider-line" style="width:<?php echo $d->width; ?>px">
			<div class="knob"></div>
			</div>
		<?php
		if (count($d->labels) > 0 && $d->labels[0] !== '') : ?>
		<ul class="slider-labels" style="width:<?php echo $d->width; ?>px;">
			<?php
			for ($i = 0; $i < count($d->labels); $i++) :
				?>
				<li style="width:<?php echo $d->spanWidth;?>px;text-align:<?php echo $d->align[$i]; ?>"><?php echo $d->labels[$i]; ?></li>
			<?php
			endfor;
			?>
			</ul>
		<?php
		endif;
		?>
		<input type="hidden" class="fabrikinput" name="<?php echo $d->name; ?>" value="<?php echo $d->value; ?>" />
		</div>
		<span class="slider_output badge badge-info"><?php echo $d->value;?></span>
	</div>