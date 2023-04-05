<?php

defined('JPATH_BASE') or die;

// Add span with id so that element fxs work.
$d = $displayData;
?>

<div class="fabrik_characters_left muted" style="clear:both"><span class="badge bg-secondary"><?php echo $d->charsLeft;?></span>
<?php echo $d->charsLeftLabel;?>
</div>