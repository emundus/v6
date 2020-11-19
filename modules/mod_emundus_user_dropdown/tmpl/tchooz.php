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
        height: 36px;
        padding: 0 5px;
        border: 1px solid #e5e5e5;
        background-color: white !important;
        background-image: url(/images/emundus/arrow-down.png) !important;
        background-size: 12px !important;
        background-repeat: no-repeat !important;
        background-position-x: 98% !important;
        background-position-y: 54% !important;
        -moz-appearance: none;
        -webkit-appearance: none;
        width: 200px;
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
</style>

<?= $intro; ?>

<!-- Button which opens up the dropdown menu. -->
<div class='dropdown' id="userDropdown" style="float: right;">
    <div class="em-user-dropdown-button" id="userDropdownLabel" aria-haspopup="true" aria-expanded="false">
        <i class="<?= $icon; ?>" id="userDropdownIcon"></i>
    </div>
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
                    if (array_key_exists($profile->id, $ids_array)) {
                        echo '<option  value="'.$profile->id.".".$ids_array[$profile->id].'"' .(($user->profile == $profile->id)?'selected="selected"':"").'>'.trim($profile->label).'</option>';
                    } else {
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
        <hr style="width: 100%">
        <?php
        echo '<h5 style="margin-bottom: 20px;margin-top: 0">'.JText::_('COM_USERS_PROFILE_DEFAULT_LABEL').'</h5>';
        ?>
    </ul>
</div>

<script>
    // This counters all of the issues linked to using BootstrapJS.
    document.getElementById('userDropdownLabel').addEventListener('click', function (e) {
        e.stopPropagation();
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
                icon.classList.remove('active');
            },300);
        } else {
            // remove message classes if message module is on page
            if(messageDropdown||messageIcon) {
                messageDropdown.classList.remove('open');
                messageIcon.classList.remove('active');
                messageIcon.classList.remove('open');
            }
            dropdown.classList.add('open');
            icon.classList.add('open');
        }
    });

    document.addEventListener('click', function (e) {
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
    });
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
