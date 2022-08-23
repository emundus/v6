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
?>
<div class="login<?php echo $this->pageclass_sfx; ?>">
    <?php if ($this->params->get('show_page_heading')) : ?>
        <div class="page-header">
            <h1 class="em-titre-connectez-vous">
                <?php echo JText::_('JLOGIN'); ?>
            </h1>
            <p><?php echo JText::_('JLOGIN_DESC'); ?></p>
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
    <form action="<?php echo (!empty($this->redirect)) ? 'index.php?option=com_users&task=user.login&redirect='.$this->redirect : 'index.php?option=com_users&task=user.login'; ?>" method="post" class="form-validate form-horizontal well">
        <fieldset>
            <?php foreach ($this->form->getFieldset('credentials') as $field) : ?>
                <?php if (!$field->hidden) : ?>
                    <div class="control-group em-mb-32">
                        <div class="control-label">
                            <?php echo $field->label; ?>
                        </div>
                        <div class="controls">
                            <?php echo $field->input; ?>
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
                <div class="control-group em-float-right">
                    <div class="control-label">
                        <a class="em-text-underline" href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
                            <?php echo JText::_('COM_USERS_LOGIN_RESET'); ?>
                        </a>
                    </div>
                </div>
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
    <?php if ($usersConfig->get('allowUserRegistration')) : ?>
        <div>
            <?php echo JText::_('COM_USERS_LOGIN_NO_ACCOUNT'); ?>
            <?php if(!empty($this->campaign) && !empty($this->course)) :?>
                <a class="em-text-underline" href="<?php echo JRoute::_('index.php?option=com_users&view=registration&course=' . $this->course . '&cid=' . $this->campaign); ?>">
                    <?php echo JText::_('COM_USERS_LOGIN_REGISTER'); ?>
                </a>
            <?php else: ?>
                <a class="em-text-underline" href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>">
                    <?php echo JText::_('COM_USERS_LOGIN_REGISTER'); ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
