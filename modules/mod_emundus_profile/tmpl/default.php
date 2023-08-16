<?php
/**
 * @package        Joomla.Site
 * @subpackage    mod_menu
 * @copyright    Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
$document = JFactory::getDocument();
$document->addStyleSheet("modules/mod_emundus_profile/style/mod_emundus_profile.css");
?>
<div>
    <div class="em-container-profile-view-pict em-flex-row em-flex-space-between em-small-flex-column em-small-align-items-start em-mt-32 em-mb-16">
        <div class="em-flex-row em-small-flex-column em-small-align-items-start em-w-100">
            <?php if ($show_profile_picture == 1) : ?>
                <div id="pp_profile_background"
                     <?php if($update_profile_picture == 1) : ?>onclick="openBrowser()" onmouseover="displayEdit('flex')" onmouseleave="displayEdit('none')"<?php endif; ?>
                     class="em-profile-picture-big em-pointer"
                     style="background-image:url('<?php echo $profile_picture ?>')">
                    <span class="em-flex-row" style="display: none" id="pp_edit_icon">
                        <span class="material-icons-outlined em-mr-8">edit</span>
                        <?php echo JText::_('MOD_EMUNDUS_PROFILE_EDIT') ?>
                    </span>
                </div>
            <?php endif; ?>

            <div class="em-flex-row <?php if ($show_name == 1) : ?>em-flex-space-between<?php else : ?>em-flex-row-justify-end<?php endif; ?> em-w-100">
                <?php if ($show_name == 1) : ?>
                    <div class="em-ml-16 em-m-xs-0 em-flex-column em-flex-col-start em-mt-xs-8">
                        <h2><?php echo $user_fullname ?></h2>
                    </div>
                <?php endif; ?>

                <?php if ($show_account_edit_button == 1 && !$external) : ?>
                    <a class="em-w-auto btn manage-account-icon" href="/index.php?option=com_users&view=profile&layout=edit" title="<?php echo JText::_('MOD_EMUNDUS_PROFILE_EDIT_PROFILE_PASSWORD_TITLE') ?>">
                        <span class="material-icons-outlined">manage_accounts</span>
                    </a>
                <?php endif; ?>

            </div>

        </div>
    </div>

    <p class="em-neutral-700-color em-ml-12 em-font-size-16"><?php echo JText::_($intro) ?></p>
</div>

<div class="em-page-loader" style="display: none"></div>

<script>
    <?php if($update_profile_picture == 1) : ?>
    function displayEdit(state) {
        console.log('here')
        document.querySelector('#pp_edit_icon').style.display = state;
    }

    function openBrowser() {
        let input = document.createElement('input');
        let mimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpeg'];
        input.type = 'file';
        input.accept = ".png, .jpg, .jpeg, .gif";
        input.onchange = () => {
            let files = Array.from(input.files);
            let error = false;
            let title = '';
            let text = '';
            if (files[0].size > 2097152) {
                title = "COM_EMUNDUS_USERS_EDIT_PROFILE_PICTURE_ERROR_TITLE";
                text = "COM_EMUNDUS_USERS_EDIT_PROFILE_PICTURE_ERROR_TEXT";
                error = true;
            }
            if (!mimeTypes.includes(files[0].type)) {
                title = "COM_EMUNDUS_USERS_EDIT_PROFILE_PICTURE_ERROR_TITLE";
                text = "COM_EMUNDUS_USERS_EDIT_PROFILE_PICTURE_WRONG_TYPE_TEXT";
                error = true;
            }

            if (error) {
                Swal.fire({
                    title: this.translate(title),
                    text: this.translate(text),
                    type: "error",
                    confirmButtonText: this.translate("COM_EMUNDUS_ONBOARD_OK"),
                    timer: 4000,
                    customClass: {
                        title: 'em-swal-title',
                        confirmButton: 'em-swal-confirm-button',
                        actions: "em-swal-single-action",
                    },
                });
            } else {
                updateProfilePicture(files[0]);
            }
        };
        input.click();
    }

    function updateProfilePicture(file) {
        document.getElementsByClassName('em-page-loader')[0].style.display = 'block';

        const formData = new FormData();
        formData.append("file", file);

        fetch(window.location.origin + '/index.php?option=com_emundus&controller=users&task=updateprofilepicture', {
            body: formData,
            method: 'post'
        }).then((response) => {
            if (response.ok) {
                return response.json();
            } else {
                Swal.fire({
                    title: Joomla.JText._('MOD_EMUNDUS_PROFILE_EDIT_PROFILE_PICTURE_ERROR_TITLE'),
                    text: Joomla.JText._('MOD_EMUNDUS_PROFILE_EDIT_PROFILE_PICTURE_ERROR_UPDATE_TEXT'),
                    type: 'error',
                    showConfirmButton: false,
                    showCancelButton: false,
                    timer: 1500,
                    customClass: {
                        title: 'em-swal-title',
                    },
                });

                reject(response);
            }
        }).then((res) => {
            document.getElementsByClassName('em-page-loader')[0].style.display = 'none';

            if (res.status) {
                const date = new Date();
                const newProfileUrl = window.location.origin + '/' + res.profile_picture + '?' + date.getTime();
                document.querySelector('#pp_profile_background').style.backgroundImage = 'url(' + newProfileUrl + ')';
                document.querySelector('#userDropdownLabel').style.backgroundImage = 'url(' + newProfileUrl + ')';
            }
        });

    }
    <?php endif; ?>
</script>

