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

<div id="mod_emundus_help">
    <p data-toggle="popover" class="mod_emundus_help__popover"><span class="material-icons">help</span></p>
</div>

<script>
    jQuery(function () {
        jQuery('[data-toggle="popover"]').popover(
            {
                html: true,
                placement: 'top',
                template: '<div class="popover" style="margin-top:-65px;"><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><p></p></div></div></div>',
                content: "" +
                    "<a href='https://emundus.atlassian.net/wiki/spaces/HD/overview' target='_blank'><span class='material-icons'>menu_book</span><p><?= JText::_('MOD_EMUNDUS_HELP_ARTICLES'); ?></p></a>" +
                    "<a href='https://emundus.atlassian.net/servicedesk/customer/portals' target='_blank'><span class='material-icons'>textsms</span><p><?= JText::_('MOD_EMUNDUS_HELP_HELP_CENTER'); ?></p></a>" +
                    "<hr/>" +
                    "<a href='https://emundus.atlassian.net/wiki/external/2049966122/Y2Q4ODg1ZWQ0YWJkNGViZDgxMTEwOTFhYzlhNGI0Y2Y?atlOrigin=eyJpIjoiZWQ2ZTEzZWJhM2Y0NDFmNGFiMmZiN2M0NWJhNzg4YTQiLCJwIjoiYyJ9' target='_blank'><span class='material-icons'>new_releases</span><p><?= JText::_('MOD_EMUNDUS_HELP_LAST_RELEASE'); ?></p></a>" +
                    "<hr/>" +
                    "<span>Version <?php echo trim($file_version) ?></span>",
            }
        )

        document.addEventListener("click", function(evt) {
            let popover = document.getElementById('mod_emundus_help'),
                targetEl = evt.target; // clicked element
            do {
                if(targetEl === popover) {
                    return;
                }
                // Go up the DOM
                targetEl = targetEl.parentNode;
            } while (targetEl);
            if(document.querySelector('#mod_emundus_help .popover') != null) {
                jQuery('[data-toggle="popover"]').click();
            }
        });
    })
</script>
