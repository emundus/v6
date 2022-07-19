<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_menu
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
$document = JFactory::getDocument();
$document->addStyleSheet("modules/mod_emundus_user_dropdown/style/mod_emundus_user_dropdown.css" );
// Note. It is important to remove spaces between elements.

if($user != null) {
?>

<style>
    .dropdown-header {
        display: block;
        font-size: unset;
        line-height: 1.42857143;
        color: black;
        white-space: nowrap;
        padding: unset;
    }

    .dropdown-menu-right {
        right: 0;
        left: auto;
    }

    #userDropdownIcon {
        background-color: #<?= $primary_color; ?>;
        border: solid 1px white;
        color: #<?= $secondary_color; ?>;
    }

    #userDropdownIcon:hover,
    #userDropdownIcon.active {
        border: 1px solid;
        box-shadow: inset 0 0 20px rgba(255, 255, 255, .5), 0 0 20px rgba(255, 255, 255, .2);
        outline-color: rgba(255, 255, 255, 0);
        outline-offset: 15px;
        background-color: #<?= $secondary_color; ?>;
        color: #fff;
    }

    #userDropdownMenu .divider {
        height: 1px;
        margin: 9px 1px;
        overflow: hidden;
        background-color: #e5e5e5;
        border-bottom: 1px solid #fff;
    }

    #userDropdownMenu li>a:hover,
    #userDropdownMenu .active>a {
        background: #<?= $secondary_color; ?>;
    }

    .select{
        text-align: left;
    }
    .select .profile-select{
        height: 48px;
        padding: 0 32px 0 12px !important;
        border: 1px solid #e5e5e5;
        background-position-x: 95%;
        background-position-y: 54%;
        -webkit-appearance: none;
        background-image: url('../../../../images/emundus/arrow.svg');
        background-size: 8px;
        background-repeat: no-repeat;
        -moz-appearance: none;
        -webkit-appearance: none;
        width: 200px;
        color: #353544;
    }
    .select .profile-select:hover{
        background-color: white !important;
    }
    .select .profile-select:focus{
        background-color: white !important;
    }
    .dropdown-menu > li > a{
        padding: unset;
    }

        .userDropdown-tip{
            position: fixed;
            width: 100vw !important;
            height: 100vw;
            left: 0;
            top: 0;
            background-color: rgba(60, 60, 60, 0.65);
        }
        .userDropdownLabel-tip{
            position: fixed;
            right: 0;
            top: 18px;
            z-index: 999999;
            background: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
        }
        #g-navigation .g-container #header-c .userDropdownIcon-tip{
            margin-bottom: 0 !important;
        }
        .em-user-dropdown-tip{
            background: white;
            position: fixed;
            right: 280px;
            padding: 10px;
            border-radius: 2px;
            top: 15px;
            transition: opacity 0.2s ease-in-out;
        }
    .em-user-dropdown-tip-link{
        float: right;
        color: #20835F;
        cursor: pointer;
    }
</style>

<?= $intro; ?>

<!-- Button which opens up the dropdown menu. -->
<div class='dropdown <?php if($first_logged) : ?>userDropdown-tip<?php endif; ?>' id="userDropdown" style="float: right;">
    <?php if(!empty($profile_picture)): ?>
    <div class="em-profile-picture em-pointer em-user-dropdown-button" id="userDropdownLabel"
         style="background-image:url('<?php echo $profile_picture ?>');right: 15px">
    </div>
    <?php else : ?>
    <div class="em-user-dropdown-button <?php if($first_logged) : ?>userDropdownLabel-tip<?php endif; ?>" id="userDropdownLabel" aria-haspopup="true" aria-expanded="false">
        <?php if($first_logged) : ?>
            <div class="em-user-dropdown-tip" id="userDropdownTip">
                <p><?php echo JText::_('COM_EMUNDUS_USERDROPDOWN_SWITCH_PROFILE_TIP_TEXT') ?></p><br/>
                <p class="em-user-dropdown-tip-link" onclick="closeTip()"><?php echo JText::_('COM_EMUNDUS_USERDROPDOWN_SWITCH_PROFILE_TIP_CLOSE') ?></p>
            </div>
        <?php endif ;?>
        <img src="<?php echo JURI::base()?>images/emundus/menus/user.svg" id="userDropdownIcon" class="<?php if($first_logged) : ?>userDropdownIcon-tip<?php endif; ?>" alt="<?php echo JText::_('PROFILE_ICON_ALT')?>">
    </div>
    <?php endif; ?>
    <input type="hidden" value="<?= $switch_profile_redirect; ?>" id="switch_profile_redirect">
    <ul class="dropdown-menu dropdown-menu-right" id="userDropdownMenu" aria-labelledby="userDropdownLabel">
        <?php
            $ids_array = array();
            if (isset($user->fnums) && $user->fnums) {
                foreach ($user->fnums as $fnum) {
                    $ids_array[$fnum->profile_id] = $fnum->fnum;
                }
            }

            if (!empty($user->emProfiles) && sizeof($user->emProfiles) > 1 && (!$only_applicant)) {
                echo '<h5 style="margin-bottom: 20px">'.JText::_('SELECT_PROFILE').'</h5>';
                echo '<br/><div class="select">';
                echo '<select class="profile-select" id="profile" name="profiles" onchange="postCProfile()"> ';
                foreach ($user->emProfiles as $profile) {
                    if ($profile->published && !$applicant_option) {
                        echo '<option  value="'.$profile->id.".".$ids_array[$profile->id].'"' .(in_array($user->profile, $app_prof)?'selected="selected"':"").'>'.JText::_('APPLICANT').'</option>';
                        $applicant_option = true;
                    } elseif (!$profile->published) {
                        echo '<option  value="'.$profile->id.".".'"' .(($user->profile == $profile->id)?'selected="selected"':"").'>'.trim($profile->label).'</option>';
                    }
                }
                echo '</select></div><br/><br/>';
            }
        ?>
        <hr style="width: 100%">
        <li class="dropdown-header"><?= $user->name; ?></li>
        <li class="dropdown-header"><?= $user->email; ?></li>
        <?php if ($show_logout == '1') :?>
            <?= '<li><a class="logout-button-user" href="index.php?option=com_users&task=user.logout&'.JSession::getFormToken().'=1">'.JText::_('LOGOUT').'</a></li>'; ?>
        <?php endif; ?>
        <?php if ($show_update == '1') :?>
            <hr style="width: 100%">
            <?php
            echo '<li><a class="edit-button-user" href="/index.php?option=com_emundus&view=users&layout=edit" style="margin-bottom: 20px;margin-top: 0">'.JText::_('COM_USERS_PROFILE_DEFAULT_LABEL').'</a></li>';
            ?>
        <?php endif; ?>
    </ul>
</div>

<script>
    <?php if($first_logged) : ?>
        displayUserOptions();
    <?php endif ?>
    document.addEventListener('DOMContentLoaded', function () {
        if(document.getElementById('profile_chzn') != null){
            document.getElementById('profile_chzn').style.display = 'none';
            document.getElementById('profile').style.display = 'block';
        }
    });
    function displayUserOptions(){
        var dropdown = document.getElementById('userDropdown');
        var icon = document.getElementById('userDropdownIcon');

        // get message module elements
        var messageDropdown = document.getElementById('messageDropdown');
        var messageIcon = document.getElementById('messageDropdownIcon');

        if (dropdown.classList.contains('open')) {
            jQuery("#userDropdownMenu").css("transform","translate(250px)")
            setTimeout(() => {
                dropdown.classList.remove('open');
                jQuery("#userDropdownMenu").css("transform","unset")
                if(icon !== null) {
                    icon.classList.remove('active');
                }
            },300);
        } else {
            // remove message classes if message module is on page
            if(messageDropdown||messageIcon) {
                messageDropdown.classList.remove('open');
                messageIcon.classList.remove('active');
                messageIcon.classList.remove('open');
            }
            dropdown.classList.add('open');
            if(icon !== null) {
                icon.classList.add('open');
            }
        }
    }

    // This counters all of the issues linked to using BootstrapJS.
    document.getElementById('userDropdownLabel').addEventListener('click', function (e) {
        e.stopPropagation();
        displayUserOptions();
    });

    /*document.addEventListener('click', function (e) {
        e.stopPropagation();
        var dropdown = document.getElementById('userDropdown');
        var icon = document.getElementById('userDropdownIcon');

        if (dropdown.classList.contains('open')) {
            jQuery("#userDropdownMenu").css("transform","translate(250px)")
            setTimeout(() => {
                dropdown.classList.remove('open');
                jQuery("#userDropdownMenu").css("transform","unset")
                icon.classList.remove('active');
            },300);
        }
    });*/

    function postCProfile() {
        var current_fnum = document.getElementById("profile").value;
        var redirect_url = document.getElementById("switch_profile_redirect").value;

        var url = window.location.origin.toString() + '/' + redirect_url;

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
                alert("Error switching porfiles.");
            }
        });
    }

    function closeTip() {
        jQuery.ajax({
            type: 'POST',
            url: 'index.php?option=com_emundus&controller=users&task=updateemundussession',
            data: ({
                param: 'first_logged',
                value: 0,
            }),
            success: function (result) {
                document.getElementById('userDropdown').classList.remove('userDropdown-tip');
                document.getElementById('userDropdownLabel').classList.remove('userDropdownLabel-tip');
                document.getElementById('userDropdownIcon').classList.remove('userDropdownIcon-tip');
                document.getElementById('userDropdownTip').style.opacity = '0';
                setTimeout(() => {
                    document.getElementById('userDropdownTip').style.display = 'none';
                },300)
            },
            error : function (jqXHR, status, err) {
                alert("Error switching porfiles.");
            }
        });
    }
</script>
<?php } else { ?>
<div class="header-right" style="text-align: right;">
	<a class="btn btn-danger" href="<?= $link_login; ?>" data-toggle="sc-modal"><?= JText::_('CONNEXION_LABEL'); ?></a>
	<?php if ($show_registration) { ?>
		<a class="btn btn-danger btn-creer-compte" href="<?= $link_register; ?>" data-toggle="sc-modal"><?= JText::_('CREATE_ACCOUNT_LABEL'); ?></a>
	<?php } ?>
</div>
    <a class="forgotten_password_header" href="<?= $link_forgotten_password; ?>"><?= JText::_('FORGOTTEN_PASSWORD_LABEL'); ?></a>
<?php } ?>
