<?php
/**
* Form control group
*
* @package     Joomla
* @subpackage  Fabrik
* @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
* @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
* @since       3.4
*/
defined('JPATH_BASE') or die;
$d = $displayData;

if ($d->display_comments) {
  ?>

    <div class="control-group fabrik-element-emundus-container flex !flex-row justify-items-start items-start <?php echo $d->class;?>" <?php echo $d->style;?>>
        <span class="material-icons-outlined cursor-pointer comment-icon mr-5" data-target-type="elements" data-target-id="<?= $d->element->element_fabrik_id ?>">comment</span>
        <div>
            <?php echo $d->row;?>
        </div>
    </div>

    <?php
} else {
    ?>
    <div class="control-group <?php echo $d->class;?> <?php echo $d->span;?>" <?php echo $d->style;?>>
        <?php echo $d->row;?>
    </div>
    <?php
}
?>