<?php
/**
 * @package        Joomla.Site
 * @subpackage     mod_menu
 * @copyright      Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
$document = JFactory::getDocument();
$document->addStyleSheet("modules/mod_emundus_user_dropdown/style/mod_emundus_user_dropdown.css");
// Note. It is important to remove spaces between elements.
if ($user != null) {
	?>

    <style>
        .dropdown-header {
            display: block;
            padding: 3px 20px;
            font-size: 12px;
            line-height: 1.42857143;
            color: #777;
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

        #userDropdownMenu li > a:hover,
        #userDropdownMenu .active > a {
            background: #<?= $secondary_color; ?>;
        }
    </style>

	<?= $intro; ?>

    <!-- Button which opens up the dropdown menu. -->
    <div class='dropdown' id="userDropdown" style="float: right;">
        <div class="em-user-dropdown-button" id="userDropdownLabel" aria-haspopup="true" aria-expanded="false">
            <i class="<?= $icon; ?>" id="userDropdownIcon"></i>
        </div>
        <ul class="dropdown-menu dropdown-menu-right" id="userDropdownMenu" aria-labelledby="userDropdownLabel">
            <li class="dropdown-header"><?= $user->name; ?></li>
            <li class="dropdown-header"><?= $user->email; ?></li>
			<?php if (!empty($list)) : ?>
                <li role="separator" class="divider"></li>
				<?php foreach ($list as $i => $item) : ?>
                    <li class="<?= ($item->id == $active_id) ? 'active' : ''; ?>"><a
                                href="<?= $item->flink; ?>" <?= ($item->browserNav == 1) ? 'target="_blank"' : ''; ?>><?= $item->title; ?></a>
                    </li>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php if ($show_logout == '1') : ?>
                <li role="separator" class="divider"></li>
				<?= '<li><a href="index.php?option=com_users&task=user.logout&' . JSession::getFormToken() . '=1">' . JText::_('LOGOUT') . '</a></li>'; ?>
			<?php endif; ?>
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
                dropdown.classList.remove('open');
                icon.classList.remove('active');
            } else {
                // remove message classes if message module is on page
                if (messageDropdown || messageIcon) {
                    messageDropdown.classList.remove('open');
                    messageIcon.classList.remove('active');
                    messageIcon.classList.remove('open');
                }
                dropdown.classList.add('open');
                icon.classList.add('open');
            }
        });

        document.addEventListener('click', function (e) {
            let clickInsideModule = false;

            e.path.forEach((pathElement) => {
                if (pathElement.id == "userDropdownMenu") {
                    clickInsideModule = true;
                }
            });

            if (!clickInsideModule) {
                const dropdown = document.getElementById('userDropdown');
                const icon = document.getElementById('userDropdownIcon');

                jQuery("#userDropdownMenu").css("transform", "translate(250px)")
                setTimeout(() => {
                    dropdown.classList.remove('open');
                    jQuery("#userDropdownMenu").css("transform", "unset")
                    if (icon !== null) {
                        icon.classList.remove('active');
                    }
                }, 300);
            }
        });
    </script>
<?php } else { ?>
    <div class="header-right" style="text-align: right;">
        <a class="btn btn-danger" href="<?= $link_login; ?>"
           data-toggle="sc-modal"><?= JText::_('CONNEXION_LABEL'); ?></a>
		<?php if ($show_registration) { ?>
            <a class="btn btn-danger btn-creer-compte" href="<?= $link_register; ?>"
               data-toggle="sc-modal"><?= JText::_('CREATE_ACCOUNT_LABEL'); ?></a>
		<?php } ?>
    </div>
    <a class="forgotten_password_header"
       href="<?= $link_forgotten_password; ?>"><?= JText::_('FORGOTTEN_PASSWORD_LABEL'); ?></a>
<?php }
?>