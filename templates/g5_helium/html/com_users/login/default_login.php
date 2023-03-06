<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');
$document = JFactory::getDocument();
$document->addStyleSheet("templates/g5_helium/html/com_users/login/style/com_users_login.css");
$eMConfig = JComponentHelper::getParams('com_emundus');

if(!empty($this->campaign)){
    JFactory::getSession()->set('login_campaign_id',$this->campaign);
} else {
	JFactory::getSession()->clear('login_campaign_id');
}
?>
<div class="login<?php echo $this->pageclass_sfx; ?>">
    <?php if ($this->params->get('show_page_heading')) : ?>
        <div class="page-header">
            <?php if (file_exists('images/custom/favicon.png')) : ?>
                <a href="/" class="em-profile-picture em-mb-32" style="width: 50px;height: 50px;background-image: url('images/custom/favicon.png')">
                </a>
            <?php endif; ?>
            <p class="em-mb-8 em-h3">
                <?php echo JText::_('JLOGIN'); ?>
            </p>
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
                    <div class="control-group em-mb-32">
                        <div class="control-label">
                            <?php echo $field->label; ?>
                        </div>
                        <div class="controls" style="<?= $field->type === "Password" ? 'position:relative; ' : '' ?>">
                            <?php echo $field->input; ?>
                            <?php if ($eMConfig["reveal_password"] && $field->type === "Password"): ?>
                                <span id="toggle-password-visibility" class="material-icons-outlined em-pointer" style="position: absolute;top: 25px;right: 10px;opacity: 0.3;user-select: none;">visibility_off</span>
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
                    <button type="submit" class="btn btn-primary">
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
        document.querySelector('#header-a img').style.display = 'none';

        <?php if ($eMConfig['reveal_password']): ?>
            const spanVisibility = document.querySelector('#toggle-password-visibility');
            const inputPassword = document.querySelector('.controls #password');

            if (spanVisibility && inputPassword) {
                spanVisibility.addEventListener('click', function () {
                    if (spanVisibility && inputPassword) {
                        if (spanVisibility.innerText == "visibility") {
                            spanVisibility.innerText = "visibility_off";
                            inputPassword.type = "password";
                        } else {
                            spanVisibility.innerText = "visibility";
                            inputPassword.type = "text";
                        }
                    }
                });
            }
        <?php endif; ?>


        document.getElementById('login_form').addEventListener('submit', function (event) {
            event.preventDefault();

            let formData = new FormData();
            formData.append('username', document.getElementsByName('username')[0].value);

            fetch('index.php?option=com_emundus&controller=user&task=getusername', {
                body: formData,
                method: 'post',
            }).then((response) => {
                if (response.ok) {
                    return response.json();
                }
            }).then((res) => {
                if(res.username !== '' && res.username !== null){
                    document.getElementsByName('username')[0].value = res.username;
                }

                document.getElementById('login_form').submit();
            })
        });
    });
</script>
