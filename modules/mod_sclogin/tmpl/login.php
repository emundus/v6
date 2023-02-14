<?php
/**
 * @package         SCLogin
 * @copyright (c)   2009-2021 by SourceCoast - All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version         Release v9.0.215
 * @build-date      2022/09/06
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

if(JVERSION >= 4.0)
{
    $app = Factory::getApplication();
    $app->getDocument()->getWebAssetManager()
        ->useScript('field.passwordview');
}

ob_start();
?>
<div class="sclogin sourcecoast <?php echo $helper->bsClass;?>" id="sclogin-<?php echo $module->id;?>">
    <?php if ($params->get('user_intro')): ?>
        <div class="sclogin-desc pretext">
            <?php echo $params->get('user_intro'); ?>
        </div>
    <?php endif; ?>

    <div class="<?php echo $helper->rowClass;?>">
        <?php
        if($params->get('socialButtonsOrientation') == 'top')
        {
            require(ModuleHelper::getLayoutPath('mod_sclogin', "socialLogin"));
            require(ModuleHelper::getLayoutPath("mod_sclogin", $helper->getBootstrapVersion() . "joomlaLogin_" . $layout));
        }
        else
        {
            require(ModuleHelper::getLayoutPath("mod_sclogin", $helper->getBootstrapVersion(). "joomlaLogin_" . $layout));
            require(ModuleHelper::getLayoutPath('mod_sclogin', "socialLogin"));
        }
        ?>
    </div>

    <?php echo $helper->getPoweredByLink(); ?>
    <div class="clearfix"></div>
</div>
<?php

$modalContents = ob_get_clean();
$doc = Factory::getDocument();
if ($doc->getType() != 'html')
    $modalContents = '';

if ($params->get('displayType') == 'modal')
{
    $loginClass = "";
    $registerClass = "";
    $spacer = Text::_('MOD_SCLOGIN_LOGINREG_SEPARATOR');
    if ($params->get('modalButtonStyle') == 'button')
    {
        $loginClass = 'class="'.$loginButtonClass.'"';
        $registerClass = 'class="'.$registerButtonClass.'"';
        $spacer = "";
    }

    $modalParams = array(
        'title' => ($module->showtitle) ? $module->title : '',
        'animation' => false,
        'closeButton' => $params->get('modalCloseButton', true)
    );

    $toggle = (JVERSION < 4.0) ? 'data-toggle' : 'data-bs-toggle';
    $target = (JVERSION < 4.0) ? 'data-target' : 'data-bs-target';

    $modalId = "login-modal-".$module->id;
    ?>

    <div class="sourcecoast <?php echo $helper->bsClass;?> sclogin-modal-links sclogin">
        <a <?php echo $loginClass;?> <?php echo $toggle;?>="modal" <?php echo $target?>="#<?php echo $modalId;?>">
        <?php echo Text::_('MOD_SCLOGIN_LOGIN');?>
        </a>
        <?php echo HTMLHelper::_('bootstrap.renderModal', $modalId, $modalParams, $modalContents) ; ?>
        <?php if ($showRegisterLinkInModal) :
            echo $spacer;
            ?>
            <a <?php echo $registerClass;?> href="<?php echo $helper->registerLink;?>">
                <?php echo Text::_('MOD_SCLOGIN_REGISTER_FOR_THIS_SITE');?>
            </a>
        <?php endif; ?>
    </div>

    <script>
        jfbcJQuery(document).ready(function () {
            jfbcJQuery("#<?php echo $modalId;?>").appendTo("body");
        });
        <?php if($params->get('autoFocusUsername')) : ?>
        jfbcJQuery("#<?php echo $modalId;?>").on("shown.bs.modal", function () {
            jfbcJQuery("#sclogin-username-<?php echo $module->id;?>").focus();
        });
        <?php endif;?>

        jfbcJQuery('#<?php echo $modalId;?>').addClass('sourcecoast sclogin-modal');
    </script>

    <?php

}
else
{
    echo $modalContents;
}
?>

