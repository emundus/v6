<?php
/**
 * @package         SCLogin
 * @copyright (c)   2009-2019 by SourceCoast - All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version         Release v8.0.5
 * @build-date      2019/01/14
 */

defined('_JEXEC') or die('Restricted access');

if ($params->get('displayType') == 'modal')
{
    $loginClass = "";
    $registerClass = "";
    $spacer = JText::_('MOD_SCLOGIN_LOGINREG_SEPARATOR');
    if ($params->get('modalButtonStyle') == 'button')
    {
        $loginClass = 'class="btn btn-primary"';
        $registerClass = 'class="btn"';
        $spacer = "";
    }
    if ($helper->isJFBConnectInstalled)
        $modalName = JFBCFactory::config()->getSetting('jquery_load') ? 'sc-modal' : 'modal';
    else
        $modalName = $params->get('loadJQuery') ? 'sc-modal' : 'modal';

    echo '<div class="sourcecoast sclogin-modal-links sclogin"><a ' . $loginClass . ' href="#login-modal" role="button" data-toggle="' . $modalName . '">' . JText::_('MOD_SCLOGIN_LOGIN') . '</a>';
    if ($showRegisterLinkInModal)
        echo $spacer . '<a ' . $registerClass . ' href="' . $helper->registerLink . '">' . JText::_('MOD_SCLOGIN_REGISTER_FOR_THIS_SITE') . '</a>';
    echo '</div>';

    ob_start();
}
?>

    <div class="sclogin sourcecoast" id="sclogin-<?php echo $module->id; ?>">
        <?php if ($params->get('user_intro')): ?>
            <div class="sclogin-desc pretext">
                <?php echo $params->get('user_intro'); ?>
            </div>
        <?php endif; ?>

        <div class="row-fluid">
            <?php
            if($params->get('socialButtonsOrientation') == 'top')
            {
                require(JModuleHelper::getLayoutPath('mod_sclogin', "socialLogin"));
                require(JModuleHelper::getLayoutPath("mod_sclogin", "joomlaLogin_" . $layout));
            }
            else
            {
                require(JModuleHelper::getLayoutPath("mod_sclogin", "joomlaLogin_" . $layout));
                require(JModuleHelper::getLayoutPath('mod_sclogin', "socialLogin"));
            }
            ?>
        </div>

        <?php echo $helper->getPoweredByLink(); ?>
        <div class="clearfix"></div>
    </div>

<?php

if ($params->get('displayType') == 'modal')
{
    $modalContents = ob_get_clean();
    $doc = JFactory::getDocument();
    if ($doc->getType() == 'html')
    {
        echo '<div id="login-modal" class="sourcecoast modal fade" tabindex="-1" role="dialog" aria-labelledby="login-modalLabel" aria-hidden="true" style="display:none">';
        if ($params->get('modalCloseButton'))
            echo '<div class="modal-header"><button type="button" class="close" data-dismiss="'.$modalName.'" aria-hidden="true">&times;</button><span class="modal-title">'.$module->title.'</span></div>';
        echo '<div class="modal-body">' .
                $modalContents .
                '</div></div>';

        echo '<script type="text/javascript">
                jfbcJQuery(document).ready(function() {
                    jfbcJQuery("#login-modal").appendTo("body");
                });';
        if ($params->get('autoFocusUsername')) {
            echo 'jfbcJQuery("#login-modal").on("shown.bs.modal", function () {
                jfbcJQuery("#sclogin-username").focus();
            });';
        }
        echo 'jfbcJQuery("#login-modal").on("show", function() {
            jfbcJQuery("#login-modal").css({"margin-left": function() {return -(jfbcJQuery("#login-modal").width() / 2)}})
        });
        </script>';

    }
}
?>