<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Akeeba\AdminTools\Admin\Helper\Select;

/** @var \Akeeba\AdminTools\Admin\View\TempSuperUsers\Html $this */

defined('_JEXEC') or die;

?>
<form action="index.php" method="post" name="adminForm" id="adminForm" class="akeeba-form--horizontal">
    <section class="akeeba-panel--information">
        <header class="akeeba-block-header">
            <h3><?php echo JText::_('COM_ADMINTOOLS_TEMPSUPERUSERS_FIELD_EXPIRATION'); ?></h3>
        </header>

        <div class="akeeba-form-group">
            <label for="expiration">
			    <?php echo JText::_('COM_ADMINTOOLS_TEMPSUPERUSERS_FIELD_EXPIRATION'); ?>
            </label>

		    <?php echo \JHtml::_('calendar', empty($this->item->expiration) ? $this->userInfo['expiration'] : $this->item->expiration, 'expiration', 'expiration', '%Y-%m-%d %H:%M', [
			    'class'    => 'input-small',
			    'showTime' => true,
		    ]); ?>
        </div>
    </section>

    <section class="akeeba-panel--information">
        <header class="akeeba-block-header">
            <h3>
                <?php echo JText::_('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_USERINFO'); ?>
            </h3>
        </header>

        <div class="akeeba-form-group">
            <label for="username">
			    <?php echo JText::_('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_USERNAME'); ?>
            </label>

            <input type="text" name="username" value="<?php echo $this->userInfo['username'] ?>" />
        </div>

        <div class="akeeba-form-group">
            <label for="password">
			    <?php echo JText::_('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_PASSWORD'); ?>
            </label>

            <input type="text" name="password" value="<?php echo $this->userInfo['password'] ?>" />
        </div>

        <div class="akeeba-form-group">
            <label for="password2">
			    <?php echo JText::_('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_PASSWORD2'); ?>
            </label>

            <input type="text" name="password2" value="<?php echo $this->userInfo['password2'] ?>" />
        </div>

        <div class="akeeba-form-group">
            <label for="email">
			    <?php echo JText::_('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_EMAIL'); ?>
            </label>

            <input type="text" name="email" value="<?php echo $this->userInfo['email'] ?>" />
        </div>

        <div class="akeeba-form-group">
            <label for="name">
			    <?php echo JText::_('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_NAME'); ?>
            </label>

            <input type="text" name="name" value="<?php echo $this->userInfo['name'] ?>" />
        </div>

        <div class="akeeba-form-group">
            <label for="groups">
			    <?php echo JText::_('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_GROUPS'); ?>
            </label>

            <?php echo JHtml::_('access.usergroup', 'groups', $this->userInfo['groups'], [
                    'multiple' => true,
                    'size' => 15
            ], false, 'groups'); ?>
        </div>
    </section>


    <div class="akeeba-hidden-fields-container">
        <input type="hidden" name="option" value="com_admintools"/>
        <input type="hidden" name="view" value="TempSuperUsers"/>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="user_id" id="user_id" value="0"/>
        <input type="hidden" name="<?php echo $this->container->platform->getToken(true); ?>" value="1"/>
    </div>
</form>
