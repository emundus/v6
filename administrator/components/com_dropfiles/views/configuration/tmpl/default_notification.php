<?php
defined('_JEXEC') || die;

$parameters = $this->form->getFieldset('notification');
if (!$this->form || empty($parameters)) {
    echo JText::sprintf('COM_DROPFILES_CONFIGURATION_NOT_FOUND');
    return;
}

?>
<div class="main-container">
    <div class="settings-title">
        <h1 class="settings-header"><?php echo JText::sprintf('COM_DROPFILES_CONFIG_SENDER_INFORMATION') ?></h1>
    </div>
    <div class="container-settings">
        <ul class="field block-list">
            <?php foreach ($parameters as $k => $field) : ?>
                <?php if (in_array($k, array('jform_sender_name', 'jform_sender_email'))) :?>
                    <li class="ju-settings-option block-item <?php echo $k;?>">
                        <label for="<?php echo $field->id ?>" class="ju-setting-label dropfiles-tooltip" alt="<?php echo JText::sprintf($field->description) ?>"><?php echo strip_tags($field->label) ?></label>
                        <?php
                        switch ($field->type) {
                            case 'Radio':
                                $checked = ($this->params->get($field->fieldname, $field->value)) ? 'checked="checked"' : '';
                                echo '<div class="ju-switch-button">
                            <label class="switch">
                                <input type="checkbox" '.$checked.' name="'.$field->name.'" id="'.$field->id.'" value="1" />
                                <span class="slider"></span>
                            </label>
                        </div>';
                                break;
                            default:
                                $textarea_class = ($field->type === 'Textarea') ? ' ju-custom-area' : '';
                                $text_class = ($field->type === 'Text' || $field->type === 'Number') ? ' ju-custom-right-side' : '';
                                $select_class = ($field->type === 'List') ? ' ju-custom-select ju-custom-right-side' : '';
                                $width_100 = ($field->type !== 'Number' && $field->type !== 'Textarea' && $field->type !== 'Text' && $field->type !== 'List') ? ' ju-width-100' : '';
                                echo '<div class="ju-custom-block '.$textarea_class.$text_class.$select_class.$width_100.'" >';
                                echo $field->input;
                                echo '</div>';
                                break;
                        }
                        ?>
                    </li>
                <?php endif;?>
            <?php endforeach; ?>
        </ul>

        <div class="clearfix"></div>
    </div>

    <div class="settings-title">
        <h1 class="settings-header"><?php echo JText::sprintf('COM_DROPFILES_CONFIG_RECEIVER_AND_EMAIL_CONTENT') ?></h1>
    </div>
    <div class="container-settings">
        <ul class="field block-list">
            <?php foreach ($parameters as $k => $field) : ?>
                <?php if (!in_array($k, array('jform_sender_name', 'jform_sender_email', 'jform___field1', 'jform___field2', 'jform___field4', 'jform___field5'))) :?>
                    <li class="ju-settings-option block-item <?php echo $k;?>">
                        <label for="<?php echo $field->id ?>" class="ju-setting-label dropfiles-tooltip" alt="<?php echo JText::sprintf($field->description) ?>"><?php echo strip_tags($field->label) ?></label>
                        <?php
                        switch ($field->type) {
                            case 'Radio':
                                $checked = ($this->params->get($field->fieldname, $field->value)) ? 'checked="checked"' : '';
                                echo '<div class="ju-switch-button">
                            <label class="switch">
                                <input type="checkbox" '.$checked.' name="'.$field->name.'" id="'.$field->id.'" value="1" />
                                <span class="slider"></span>
                            </label>
                        </div>';
                                break;
                            default:
                                $textarea_class = ($field->type === 'Textarea') ? ' ju-custom-area' : '';
                                $text_class = ($field->type === 'Text' || $field->type === 'Number') ? ' ju-custom-right-side' : '';
                                $select_class = ($field->type === 'List') ? ' ju-custom-select ju-custom-right-side' : '';
                                $width_100 = ($field->type !== 'Number' && $field->type !== 'Textarea' && $field->type !== 'Text' && $field->type !== 'List') ? ' ju-width-100' : '';
                                echo '<div class="ju-custom-block '.$textarea_class.$text_class.$select_class.$width_100.'" >';
                                echo $field->input;
                                echo '</div>';
                                break;
                        }
                        ?>
                    </li>
                <?php endif;?>
            <?php endforeach; ?>
        </ul>

        <div class="clearfix"></div>
    </div>
</div>