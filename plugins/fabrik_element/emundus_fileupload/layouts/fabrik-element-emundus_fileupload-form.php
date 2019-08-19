<?php

defined('JPATH_BASE') or die;

// Add span with id so that element fxs work.
$d = $displayData;

$jinput = JFactory::getApplication()->input;
$formid = $jinput->get->get('formid');


$attachId = $d->attributes['attachmentId'];
$size = $d->attributes['size'];
$encrypt = $d->attributes['encrypted'];
//var_dump($d).die();

?>
<div id="div_<?php echo $d->attributes['name']; ?>">
<input type="file" id="<?php echo $d->attributes['name']; ?>" name="<?php echo $d->attributes['name']; ?>" multiple
        <?php foreach ($d->attributes as $key => $value) :

    echo $key . '="' . $value . '" ';

endforeach; ?>
/>




</div>
<script>
    window.addEventListener('load', () => {FbFileUpload.watchFileAttachment('<?php echo $d->attributes['name']; ?>','<?php echo $attachId; ?>')});

</script>
<script>
    var target = document.getElementById('<?php echo $d->attributes['name']; ?>');
    console.log(target);
    target.addEventListener('change', () => {FbFileUpload.upload('<?php echo $d->attributes['name']; ?>', '<?php echo $attachId; ?>','<?php echo $size; ?>','<?php echo $encrypt; ?>');} );
</script>

