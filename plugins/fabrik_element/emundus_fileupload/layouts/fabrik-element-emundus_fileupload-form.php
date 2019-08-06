<?php

defined('JPATH_BASE') or die;

// Add span with id so that element fxs work.
$d = $displayData;

?>
<input type="file" name="<?php echo $d->attributes['name']; ?>"
        <?php foreach ($d->attributes as $key => $value) :

    echo $key . '="' . $value . '" ';

endforeach; ?>
/>

<a class="btn goback-btn em-deleteFile" onload="FbFileUpload.hideButtonDelete()" onclick="FbFileUpload.doDelete()"><i class="far fa-times-circle"></i></a>
<script>console.log(FbFileUpload.hideButtonDelete());</script>