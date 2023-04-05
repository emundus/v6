<?php

defined('JPATH_BASE') or die;

$d = $displayData;
?>

<input type="<?php echo $d->type;?>" class="form-control fabrikinput inputbox" readonly="readonly" name="<?php echo $d->name;?>"
	id="<?php echo $d->id?>" value="<?php echo $d->value; ?>" />