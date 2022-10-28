<?php
defined('_JEXEC') || die;

$parameters = $this->form->getFieldset('liveupdate');

if (!$this->form || empty($parameters)) {
    echo JText::sprintf('COM_DROPFILES_CONFIGURATION_NOT_FOUND');
    return;
}

JFactory::getDocument()->addScriptDeclaration("var ju_url = {option: 'com_dropfiles', view: 'configuration', component:'com_dropfiles'};");
?>
<div class="main-container">
    <div class="settings-title">
        <h1 class="settings-header"><?php echo JText::sprintf('COM_DROPFILES_CONFIGURATION_NAV_NAME_LIVE_UPDATES') ?></h1>
    </div>
    <div class="container-settings">
        <ul class="field">
            <?php foreach ($parameters as $k => $field) : ?>
                <li class="ju-settings-option full-width ju-padding-20">
                    <div class="ju-custom-block"><?php echo $field->input; ?></div>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="clearfix"></div>
    </div>
</div>