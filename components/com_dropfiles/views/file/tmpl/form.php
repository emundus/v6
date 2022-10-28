<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access.
defined('_JEXEC') || die;

if ($this->form) {
    $fieldSet = $this->form->getFieldset();
    if (!empty($fieldSet)) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery("#jform_dropfiles_tags").tagit({
                    availableTags: <?php echo $this->allTagsFiles; ?>,
                    afterTagAdded: function (e) {
                        e.preventDefault();
                    },
                    allowSpaces: true
                });
            });
        </script>
        <style>
            ul.tagit {
                background: #fff none;
            }

            .tagit-hidden-field {
                display: none;
            }

            ul.tagit input[type="text"] {
                background-color: #fff;
            }
        </style>
        <form class="dropfilesparams">
            <div class="fieldset-settings-container">
                <button class="btn dropfiles-save-submit" type="submit"><?php echo JText::_('COM_DROPFILES_JS_SAVE_SETTINGS'); ?></button>
                <?php
                echo $this->form->getInput('id');

                foreach ($fieldSet as $name => $field) : ?>
                    <?php if ($field->id === 'jform_state') : ?>
                        <div class="ju-container ju-file-status <?php echo $field->id ?>">
                            <?php echo $field->label; ?>
                            <div class="ju-switch-button">
                                <label class="switch">
                                    <?php $checked = intval($field->value) ? ' checked' : ''; ?>
                                    <input type="checkbox" name="<?php echo $field->id ?>" id="<?php echo $field->id; ?>"<?php echo $checked; ?>>
                                    <span class="dropfiles-slider"></span>
                                </label>
                                <span class="paraminput input-block-level <?php echo $field->id ?>">
                                        <?php echo $field->input; ?>
                                    </span>
                            </div>
                            <!--<span class="help-block"><?php echo $field->description; ?></span>-->
                        </div>
                    <?php else : ?>
                        <div class="ju-settings-option file-field-container <?php echo $field->id ?>">
                            <?php echo $field->label; ?>
                            <span class="paraminput"><?php echo $field->input; ?></span>
                            <!--<span class="help-block"><?php echo $field->description; ?></span>-->
                        </div>
                    <?php endif;?>
                <?php endforeach; ?>
                <span class="paraminput"><?php echo JHtml::_('form.token'); ?></span>
                <button class="btn dropfiles-save-submit" type="submit"><?php echo JText::_('COM_DROPFILES_JS_SAVE_SETTINGS'); ?></button>
            </div>
        </form>
    <?php }
}
?>
