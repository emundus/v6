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
	                <?php if($current_lang == 'fr') : ?>
                    "<a href='https://emundus.atlassian.net/wiki/x/BoCjn' target='_blank'><img class='icone-aide-tchoozy' src='../../../media/com_emundus/images/tchoozy/icons/Tchoozy-icone-articles-aide.svg' alt='icone articles aide'><p><?= JText::_('MOD_EMUNDUS_HELP_ARTICLES'); ?></p></a>" +
                    <?php /* "<a href='https://emundus.atlassian.net/wiki/x/BADPn' target='_blank'><span class='material-icons'>smart_display</span><p><?= JText::_('MOD_EMUNDUS_HELP_VIDEOS'); ?></p></a>" + */ ?>
                    <?php else : ?>
                    "<a href='https://emundus.atlassian.net/wiki/x/NQDLn' target='_blank'><img class='icone-aide-tchoozy' src='../../../media/com_emundus/images/tchoozy/icons/Tchoozy-icone-articles-aide.svg' alt='icone articles aide'><p><?= JText::_('MOD_EMUNDUS_HELP_ARTICLES'); ?></p></a>" +
                    <?php /* "<a href='https://emundus.atlassian.net/wiki/x/FoDMn' target='_blank'><span class='material-icons'>smart_display</span><p><?= JText::_('MOD_EMUNDUS_HELP_VIDEOS'); ?></p></a>" + */ ?>
                    <?php endif; ?>
                    "<a href='https://support.client.emundus.fr' target='_blank'><img class='icone-aide-tchoozy' src='../../../media/com_emundus/images/tchoozy/icons/Tchoozy-icone-centre-aide.svg' alt='icone centre aide'><p><?= JText::_('MOD_EMUNDUS_HELP_HELP_CENTER'); ?></p></a>" +
                    "<hr/>" +
                    <?php if($current_lang == 'fr') : ?>
                    "<a href='https://emundus.atlassian.net/wiki/x/EIBskg' target='_blank'><span class='material-icons'>new_releases</span><p><?= JText::_('MOD_EMUNDUS_HELP_LAST_RELEASE'); ?></p></a>" +
                    <?php else : ?>
                    "<a href='https://emundus.atlassian.net/wiki/x/AYBdkw' target='_blank'><span class='material-icons'>new_releases</span><p><?= JText::_('MOD_EMUNDUS_HELP_LAST_RELEASE'); ?></p></a>" +
                    <?php endif; ?>
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
