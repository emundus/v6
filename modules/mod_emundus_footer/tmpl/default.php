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
       <div class="row">
            <div class="em-col-1-footer">
                <p><?= $mod_emundus_footer_texte_col_1; ?></p>
            </div>

            <div class="em-col-2-footer">
                <p><?= $mod_emundus_footer_texte_col_2; ?></p>
            </div>

           <div class="em-col-3-footer">
                    <a target="_blank" href="https://www.tchooz.io/" data-bcup-haslogintext="no">
                        <img class="logo" src="/images/emundus/tchooz_black.png" alt="Logo">
                    </a>

                        <p><?= JText::_('MOD_EM_FOOTER_COPYRIGHT') ?><a href="<?= JText::_('MOD_EM_FOOTER_LINK') ?>" target="_blank">eMundus</a></p>
            </div>
      </div>

      <div class="row">
        <div class="footer-rgpd">
            <hr class="footer-separation"/>
            <p><a href="<?php echo $actualLanguage ?>/mentions-legales"><?= JText::_('MOD_EM_FOOTER_LEGAL_INFO_LINK'); ?></a></p>
            <p><a href="<?php echo $actualLanguage ?>/politique-de-confidentialite-des-donnees"><?= JText::_('MOD_EM_FOOTER_DATA_PRIVACY_LINK'); ?></a></p>
            <p><a href="<?php echo $actualLanguage ?>/gestion-de-vos-droits"><?= JText::_('MOD_EM_FOOTER_RIGHTS_LINK'); ?></a></p>
            <p><a href="<?php echo $actualLanguage ?>/gestion-des-cookies"><?= JText::_('MOD_EM_FOOTER_COOKIES_LINK'); ?></a></p>
        </div>
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
