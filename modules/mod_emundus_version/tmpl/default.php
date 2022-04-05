<?php
/**
 * @package     Joomla.Site
 * @subpackage  eMundus
 * @copyright   Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;
?>
<style>
    .em-version-swal-container .swal2-actions{
        justify-content: center !important;
    }
    .em-version-swal-container .em-version-swal-content{
        text-align: left;
    }
    .em-version-swal-container li{
        margin-bottom: 8px;
    }
    .em-version-swal-container h2{
        margin-bottom: 12px;
    }
    .em-version-swal-container .swal2-modal{
        width: 50%;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
<script>
    <?php if($old_version != $current_version) : ?>
        jQuery(document).ready(function () {
            showReleaseNotes();
        });
    <?php endif; ?>

    function showReleaseNotes(){
        Swal.fire({
            position: 'center',
            iconHtml: '',
            title: "<?php echo JText::_('MOD_EMUNDUS_VERSION_RELEASE_NOTE') ?>",
            html: "<?php echo trim($release_note) ?>",
            showConfirmButton: true,
            reverseButtons: true,
            allowOutsideClick: false,
            confirmButtonText: "<?php echo JText::_('MOD_EMUNDUS_VERSION_OK') ?>",
            customClass: {
                title: 'em-swal-title',
                confirmButton: 'em-swal-confirm-button',
                content: 'em-version-swal-content',
                container: 'em-version-swal-container'
            }
        })
    }
</script>
