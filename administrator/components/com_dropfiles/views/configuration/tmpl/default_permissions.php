<?php
defined('_JEXEC') || die;

$parameters = $this->form->getFieldset('permissions');

if (!$this->form || empty($parameters)) {
    echo JText::sprintf('COM_DROPFILES_CONFIGURATION_NOT_FOUND');
    return;
}
?>
<div class="main-container">
    <div class="settings-title">
        <h1 class="settings-header"><?php echo JText::sprintf('COM_DROPFILES_CONFIGURATION_PERMISSIONS_NAME') ?></h1>
    </div>
    <div class="container-settings">
        <ul class="field">
            <?php foreach ($parameters as $k => $field) : ?>
                <?php if (!in_array($k, array('jform_adminassets'))) :?>
                <li class="ju-settings-option full-width ju-padding-20">
                    <div class="ju-custom-block ju-width-100"><?php echo $field->input; ?></div>
                </li>
                <?php endif;?>
            <?php endforeach; ?>
        </ul>
        <div class="clearfix"></div>
    </div>
</div>