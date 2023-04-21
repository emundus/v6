<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('UsersHelperRoute', JPATH_SITE . '/components/com_users/helpers/route.php');

JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.tooltip');
?>
<?php
$profile = JFactory::getSession()->get('emundusUser')->profile;
if($applicant = !EmundusHelperAccess::asPartnerAccessLevel($user->id)) {
?>

    <div class="em-container-footer-cand">
        <?php if (!empty($mod_emundus_footer_texte_col_1) || !empty($mod_emundus_footer_texte_col_2)) : ?>
       <div class="row">

        <?php if ($mod_emundus_footer_merge_two_columns == 1) : ?>
           <div class="em-big-col-footer">
               <p><?= $mod_emundus_footer_texte_col_1; ?></p>
           </div>

        <?php else : ?>

            <div class="em-col-1-footer">
                <p><?= $mod_emundus_footer_texte_col_1; ?></p>
            </div>

            <div class="em-col-2-footer">
                <div class="em-col-2-footer-texte">
                    <p><?= $mod_emundus_footer_texte_col_2; ?></p>
                </div>
            </div>

        <?php endif; ?>

           <div class="em-col-3-footer">
               <?php if ($mod_emundus_footer_display_tchooz_logo == 1) : ?>
                    <a target="_blank" href="https://www.tchooz.io/" data-bcup-haslogintext="no">
                        <img class="logo" src="/images/emundus/tchooz_black.png" alt="Logo">
                    </a>
                <?php endif; ?>

               <?php if ($mod_emundus_footer_display_powered_by == 1) : ?>
                   <p><?= JText::_('MOD_EM_FOOTER_COPYRIGHT') ?><a href="<?= JText::_('MOD_EM_FOOTER_LINK') ?>" target="_blank">eMundus</a></p>
               <?php endif; ?>


            </div>
      </div>
    <?php endif; ?>

      <div class="em-flex-row">
        <?php if((empty($mod_emundus_footer_texte_col_1) && empty($mod_emundus_footer_texte_col_2)) && !empty($mod_emundus_footer_client_link)) : ?>
            <a href="<?php echo $mod_emundus_footer_client_link ?>" target="_blank"><img style="width: 250px" src="<?php echo $logo ?>"/></a>
        <?php endif; ?>
        <div class="footer-rgpd">
        <?php if ($mod_emundus_footer_legal_info == '0' && $mod_emundus_footer_data_privacy == '0' && $mod_emundus_footer_rights == '0' && $mod_emundus_footer_cookies == '0' ) :?>

        <?php elseif(!empty($mod_emundus_footer_texte_col_1) || !empty($mod_emundus_footer_texte_col_2)) : ?>
            <hr class="footer-separation"/>
        <?php endif; ?>

        <div class="footer-rgpd-links">
        <?php if ($mod_emundus_footer_legal_info == '1') :?>
            <p><a href="<?php echo $actualLanguage ?>/mentions-legales"><?= JText::_('MOD_EM_FOOTER_LEGAL_INFO_LINK'); ?></a></p>
        <?php endif; ?>

         <?php if ($mod_emundus_footer_data_privacy == '1') :?>
            <p><a href="<?php echo $actualLanguage ?>/politique-de-confidentialite-des-donnees"><?= JText::_('MOD_EM_FOOTER_DATA_PRIVACY_LINK'); ?></a></p>
        <?php endif; ?>

        <?php if ($mod_emundus_footer_rights == '1') :?>
            <p><a href="<?php echo $actualLanguage ?>/gestion-des-droits"><?= JText::_('MOD_EM_FOOTER_RIGHTS_LINK'); ?></a></p>
        <?php endif; ?>

        <?php if ($mod_emundus_footer_cookies == '1') :?>
            <p><a href="<?php echo $actualLanguage ?>/gestion-des-cookies"><?= JText::_('MOD_EM_FOOTER_COOKIES_LINK'); ?></a></p>
        <?php endif; ?>
        </div>
        </div>
          <?php if(empty($mod_emundus_footer_texte_col_1) && empty($mod_emundus_footer_texte_col_2) && $mod_emundus_footer_display_powered_by == 1) : ?>
          <p style="width: auto;white-space: nowrap;"><?= JText::_('MOD_EM_FOOTER_COPYRIGHT') ?><a href="<?= JText::_('MOD_EM_FOOTER_LINK') ?>" target="_blank">eMundus</a></p>
          <?php endif; ?>
      </div>

    </div>



<?php
}
else
{
?>

    <div class="em-container-footer-gest">
        <div class="em-block-footer">
            <p><?= JText::_('MOD_EM_FOOTER_COPYRIGHT') ?><a href="<?= JText::_('MOD_EM_FOOTER_LINK') ?>" target="_blank">eMundus<?php if (!empty($file_version)): ?> - <?= $file_version ?> <?php endif ?></a></p>
        </div>
    </div>

<?php
}
?>
