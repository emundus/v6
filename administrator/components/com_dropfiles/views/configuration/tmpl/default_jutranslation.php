<?php
defined('_JEXEC') || die;

$parameters = $this->form->getFieldset('jutranslation');

if (!$this->form || empty($parameters)) {
    echo JText::sprintf('COM_DROPFILES_CONFIGURATION_NOT_FOUND');
    return;
}
?>
<div class="main-container">
    <div class="settings-title">
        <h1 class="settings-header"><?php echo JText::sprintf('COM_DROPFILES_CONFIGURATION_NAV_NAME_TRANSLATIONS') ?></h1>
    </div>
    <div class="container-settings">
        <ul class="field">
            <?php foreach ($parameters as $k => $field) : ?>
                <li class="ju-settings-option full-width ju-padding-20">
                    <div id="jutranslation">
                        <div class="ju-custom-block ju-width-100 control-group"><?php echo $field->input; ?></div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="clearfix"></div>
    </div>
</div>