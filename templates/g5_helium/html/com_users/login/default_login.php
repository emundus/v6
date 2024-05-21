<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


require_once (JPATH_SITE.'/components/com_emundus/helpers/cache.php');
$hash = EmundusHelperCache::getCurrentGitHash();

defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');
$document = JFactory::getDocument();
$document->addStyleSheet("templates/g5_helium/html/com_users/login/style/com_users_login.css?".$hash);
$eMConfig = JComponentHelper::getParams('com_emundus');

if(!empty($this->campaign)){
    JFactory::getSession()->set('login_campaign_id',$this->campaign);
} else {
	JFactory::getSession()->clear('login_campaign_id');
}
?>
<iframe id="background-shapes2" class="background-shaped-top" src="/modules/mod_emundus_campaign/assets/fond-clair.svg" alt="<?= JText::_('MOD_EM_FORM_IFRAME') ?>"></iframe>
<iframe id="background-shapes2" class="background-shaped-bottom" src="/modules/mod_emundus_campaign/assets/fond-clair.svg" alt="<?= JText::_('MOD_EM_FORM_IFRAME') ?>"></iframe>

<div class="login<?php echo $this->pageclass_sfx; ?>">
    <?php if ($this->params->get('show_page_heading')) : ?>
        <div class="page-header">
            <?php if (file_exists($this->favicon)) : ?>
                <a href="index.php" alt="Logo" class="em-profile-picture mb-8" style="width: 50px;height: 50px;background-image: url(<?php echo $this->favicon ?>)">
                </a>
            <?php endif; ?>
            <h1 class="em-mb-8">
                <?php echo JText::_('JLOGIN'); ?>
            </h1>
            <p class="em-applicant-text-color em-applicant-default-font"><?php echo JText::_('JLOGIN_DESC'); ?></p>
        </div>
    <?php endif; ?>
    <?php if (($this->params->get('logindescription_show') == 1 && str_replace(' ', '', $this->params->get('login_description')) != '') || $this->params->get('login_image') != '') : ?>
    <div class="login-description">
        <?php endif; ?>
        <?php if ($this->params->get('logindescription_show') == 1) : ?>
            <?php echo $this->params->get('login_description'); ?>
        <?php endif; ?>
        <?php if ($this->params->get('login_image') != '') : ?>
            <img src="<?php echo $this->escape($this->params->get('login_image')); ?>" class="login-image" alt="<?php echo JText::_('COM_USERS_LOGIN_IMAGE_ALT'); ?>" />
        <?php endif; ?>
        <?php if (($this->params->get('logindescription_show') == 1 && str_replace(' ', '', $this->params->get('login_description')) != '') || $this->params->get('login_image') != '') : ?>
    </div>
<?php endif; ?>
    <form id="login_form" action="<?php echo (!empty($this->redirect)) ? 'index.php?option=com_users&task=user.login&redirect='.$this->redirect : 'index.php?option=com_users&task=user.login'; ?>" method="post" class="form-validate form-horizontal well">
        <fieldset>
            <?php foreach ($this->form->getFieldset('credentials') as $field) : ?>
                <?php if (!$field->hidden) : ?>
                    <div class="control-group mb-8">
                        <div class="control-label">
                            <?php echo $field->label; ?>
                        </div>
                        <div class="controls" style="<?= $field->type === "Password" ? 'position:relative; ' : '' ?>">
                            <?php echo $field->input; ?>
                            <?php if ($eMConfig["reveal_password"] && $field->type === "Password"): ?>
                                <button type="button" title="<?php echo JText::_('COM_USERS_LOGIN_SHOW_PASSWORD'); ?>" id="toggle-password-visibility" class="material-icons-outlined em-pointer" aria-pressed="false" style="position: absolute;margin-top: 4px;right: 10px;opacity: 0.3;user-select: none;">visibility_off</button>
                                <div aria-live="polite" aria-atomic="true" style="display: none" id="show_password_text"><p><?php echo JText::_('COM_USERS_LOGIN_SHOW_PASSWORD'); ?></p></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if ($this->tfa) : ?>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getField('secretkey')->label; ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getField('secretkey')->input; ?>
                    </div>
                </div>
            <?php endif; ?>
            <div class="em-w-100 em-flex-row em-flex-end">
                <?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
                    <div class="control-group">
                        <div class="control-label">
                            <label for="remember">
                                <?php echo JText::_('COM_USERS_LOGIN_REMEMBER_ME'); ?>
                            </label>
                        </div>
                        <div class="controls">
                            <input id="remember" type="checkbox" name="remember" class="inputbox" value="yes" />
                        </div>
                    </div>
                <?php endif; ?>
                <?php if($this->displayForgotten) : ?>
                <div class="control-group em-float-right">
                    <div class="control-label">
                        <a class="em-text-underline" href="<?php echo JRoute::_($this->forgottenLink); ?>">
                            <?php echo JText::_('COM_USERS_LOGIN_RESET'); ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="control-group em-w-100">
                <div class="controls">
                    <button type="submit" class="em-applicant-primary-button w-full em-applicant-border-radius">
                        <?php echo JText::_('JLOGIN'); ?>
                    </button>
                </div>
            </div>
            <?php $return = $this->form->getValue('return', '', $this->params->get('login_redirect_url', $this->params->get('login_redirect_menuitem'))); ?>
            <input type="hidden" name="return" value="<?php echo base64_encode($return); ?>" />
            <?php echo JHtml::_('form.token'); ?>
        </fieldset>
    </form>

    <?php $usersConfig = JComponentHelper::getParams('com_users'); ?>
    <?php if ($usersConfig->get('allowUserRegistration') && $this->displayRegistration) : ?>
        <div>
            <?php echo JText::_('COM_USERS_LOGIN_NO_ACCOUNT'); ?>
            <a class="em-text-underline" href="<?php echo JRoute::_($this->registrationLink); ?>">
                <?php echo JText::_('COM_USERS_LOGIN_REGISTER'); ?>
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let username_field = document.querySelector('#username');
        if(username_field) {
            username_field.setAttribute('placeholder', '<?php echo JText::_('COM_USERS_LOGIN_EMAIL_PLACEHOLDER'); ?>');
            username_field.setAttribute('aria-describedby', 'alert-message-text');
            username_field.setAttribute('autocomplete', 'email');
            username_field.focus();
        }
        let password_field = document.querySelector('#password');
        if(password_field) {
            password_field.setAttribute('aria-describedby', 'alert-message-text');
            password_field.setAttribute('autocomplete', 'current-password');
        }

        document.querySelector('#header-a img').style.display = 'none';

        <?php if ($eMConfig['reveal_password']): ?>
            const spanVisibility = document.querySelector('#toggle-password-visibility');
            const inputPassword = document.querySelector('.controls #password');
            const showPasswordText = document.querySelector('#show_password_text');

            if (spanVisibility && inputPassword) {
                spanVisibility.addEventListener('click', function () {
                    if (spanVisibility && inputPassword) {
                        if (spanVisibility.innerText == "visibility") {
                            spanVisibility.setAttribute('aria-pressed', 'false');
                            spanVisibility.innerText = "visibility_off";
                            inputPassword.type = "password";
                            showPasswordText.innerHTML = "<p>Le mot de passe est masqué</p>";
                        } else {
                            spanVisibility.setAttribute('aria-pressed', 'true');
                            spanVisibility.innerText = "visibility";
                            inputPassword.type = "text";
                            showPasswordText.innerHTML = "<p>Le mot de passe est affiché</p>";
                        }
                    }
                });
            }
        <?php endif; ?>
    });

    /* Modification de la couleur du background avec les formes */
    let emProfileColor1 = getComputedStyle(document.documentElement).getPropertyValue('--em-profile-color');
    let iframeElements = document.querySelectorAll("#background-shapes2");

    if(iframeElements !== null) {
        iframeElements.forEach((iframeElement) => {
            iframeElement.addEventListener("load", function () {

                let iframeDocument = iframeElement.contentDocument || iframeElement.contentWindow.document;
                let pathElements = iframeDocument.querySelectorAll("path");

                let styleElement = iframeDocument.querySelector("style");

                if (styleElement) {
                    let styleContent = styleElement.textContent;
                    styleContent = styleContent.replace(/fill:#[0-9A-Fa-f]{6};/, "fill:" + emProfileColor1 + ";");
                    styleElement.textContent = styleContent;
                }

                if (pathElements) {
                    pathElements.forEach((pathElement) => {
                        let pathStyle = pathElement.getAttribute("style");
                        if (pathStyle && pathStyle.includes("fill:grey;")) {
                            pathStyle = pathStyle.replace(/fill:grey;/, "fill:" + emProfileColor1 + ";");
                            pathElement.setAttribute("style", pathStyle);
                        }
                    });
                }
            });
        });
    }

    let displayTchoozy = getComputedStyle(document.documentElement).getPropertyValue('--display-corner-bottom-left-background');
    let displayTchoozy2 = getComputedStyle(document.documentElement).getPropertyValue('--display-corner-top-right-background');
    if (displayTchoozy == 'none' || displayTchoozy2 == 'none') {
        document.querySelector(".background-shaped-top").style.display = 'none';
        document.querySelector(".background-shaped-bottom").style.display = 'none';
    }

</script>
