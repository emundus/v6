<?php
$d             = $displayData;

?>

<div id="<?php echo $d->id; ?>" class="fabrikinput fabrikElementReadOnly" style="background-color: <?php echo $d->backgroundColor ?>">
    <span class="material-icons<?php echo $d->iconType ?>" style="color: <?php echo $d->iconColor ?>"><?php echo $d->icon ?></span>
	<div class="fabrikElementContent">
		<?php echo $d->value;?>
    </div>
</div>
