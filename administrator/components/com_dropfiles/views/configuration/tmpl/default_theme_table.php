<?php
defined('_JEXEC') || die;

$parameters = $this->form->getFieldset('theme_table');
if (!$this->form || empty($parameters)) {
    echo JText::sprintf('COM_DROPFILES_CONFIGURATION_NOT_FOUND');
    return;
}

?>
<div class="main-container theme-container">
    <div class="container-settings">
        <ul class="field block-list">
            <?php foreach ($parameters as $field) : ?>
                <li class="ju-settings-option block-item">
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
            <?php endforeach; ?>


        </ul>

        <div class="clearfix"></div>
    </div>
</div>