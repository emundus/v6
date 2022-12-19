<?php
/**
 * @package     Joomla.Site
 * @subpackage  eMundus
 * @copyright   Copyright (C) 2018 emundus.fr. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;

if($just_logged && !$only_applicant) {
    $user = JFactory::getSession()->get('emundusUser');
?>
<style>
    .em-switch-profile-img{
        text-align: center;
    }
    .em-switch-profile-img img{
        width: 10vw;
    }
    .em-switch-profile-swal-container.swal2-shown{
        background-color: rgba(60, 60, 60, 0.98);
    }
    .em-switch-profile-swal-container .swal2-actions{
        justify-content: center !important;
    }
    .em-switch-profile-swal-container .em-switch-profile-swal-content{
        text-align: left;
    }
    .em-switch-profile-swal-container li{
        margin-bottom: 8px;
        margin-top: 8px;
    }
    .em-switch-profile-swal-container h2{
        margin-bottom: 0 !important;
    }
    .em-switch-profile-swal-container .swal2-modal {
        width: 40vw;
    }

    .swal2-content div > p.em-text-align-center {
        color: var(--neutral-900);
    }

    .swal2-content p.em-text-align-center.em-font-size-24 {
        color: var(--main-500);
    }

    @media (max-width: 1368px) {
        .em-switch-profile-swal-container .swal2-modal {
            width: 50vw;
        }
    }
    .em-switch-profile-card{
        height: 45px;
        text-align: center;
        border: solid 1px var(--main-500);
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: var(--em-border-radius);
        flex-direction: column;
        cursor: pointer;
        color: var(--main-500);
        transition: all 0.3s ease-in-out;
        width: 45%;
        margin: 10px;
    }
    .em-switch-profile-card:hover {
        background: var(--main-500);
        color: var(--neutral-50);
    }
    .em-switch-profile-card .material-icons-outlined{
        font-size: 64px;
    }
    .em-switch-profile-card:last-child:nth-child(3n - 1) {
        grid-column-end: -2;
    }

    .em-switch-profile-card:nth-last-child(2):nth-child(3n + 1) {
        grid-column-end: 4;
    }

    /* Dealing with single orphan */

    .em-switch-profile-card:last-child:nth-child(3n - 2) {
        grid-column-end: 5;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
<script>

    const current_profile = "<?= $user->profile . '.'; ?>";
    jQuery(document).ready(function () {
        showModal();
    });

    function showModal(){
        Swal.fire({
            position: 'center',
            iconHtml: '',
            title: "<span class='em-main-500-color'><?php echo JText::_('MOD_EMUNDUS_SWITCH_PROFILE_WELCOME') . ' ' . $user->name ?></span>",
            html: "<?php echo $text ?>",
            showConfirmButton: false,
            reverseButtons: true,
            allowOutsideClick: false,
            confirmButtonText: "<?php echo JText::_('MOD_EMUNDUS_SWITCH_PROFILE_OK') ?>",
            customClass: {
                title: 'em-swal-title',
                confirmButton: 'em-swal-confirm-button',
                content: 'em-switch-profile-swal-content',
                container: 'em-switch-profile-swal-container'
            }
        })
    }

    function hideModal() {
        Swal.close();
    }

    function postCProfileAtLogin(current_fnum) {
        if (current_fnum == current_profile) {
            hideModal();
            return;
        }

        const url = window.location.origin.toString() + '/index.php';

        jQuery.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus&task=switchprofile',
            data: ({
                profnum: current_fnum
            }),
            success: function (result) {
                window.location.href = url;
                //location.reload(true);
            },
            error : function (jqXHR, status, err) {
                alert("Error switching profiles.");
            }
        });
    }
</script>
<?php } ?>
