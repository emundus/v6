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

$dropfiles_params = JComponentHelper::getParams('com_dropfiles');
if ((int) $dropfiles_params->get('loadthemecategory', 1) === 0) : ?>
    <style type="text/css">
        .hide_params_box {
            display: none;
        }
    </style>
<?php endif; ?>
<?php
if ($this->form) {
    $fieldSet = $this->form->getFieldset();
    if (!empty($fieldSet)) {
        ?>
        <form class="dropfilesparams">
            <fieldset>
                <?php
                echo $this->form->getInput('id');

                foreach ($fieldSet as $name => $field) : ?>
                    <?php if (in_array(
                        $field->id,
                        array(
                            'jform_id',
                            'jform_access',
                            'jform_params_ordering',
                            'jform_params_orderingdir',
                            'jform_params_canview',
                            'jform_created_user_id',
                            'jform_params_usergroup'
                        )
                    )) : ?>
                        <?php echo $field->label; ?>
                        <span class="paraminput input-block-level"><?php echo $field->input; ?></span>
                        <!--<span class="help-block"><?php echo $field->description; ?></span>-->
                    <?php endif; ?>
                <?php endforeach; ?>
                <div class="hide_params_box">
                    <?php foreach ($fieldSet as $name => $field) : ?>
                        <?php if (!in_array(
                            $field->id,
                            array(
                                'jform_id',
                                'jform_access',
                                'jform_params_ordering',
                                'jform_params_orderingdir',
                                'jform_params_canview',
                                'jform_created_user_id',
                                'jform_params_usergroup'
                            )
                        )
                        ) : ?>
                            <?php echo $field->label; ?>
                            <span class="paraminput input-block-level <?php echo $field->id ?>">
                                <?php echo $field->input; ?>
                            </span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <span class="paraminput"><?php echo JHtml::_('form.token'); ?></span>
                <button class="btn" type="submit"><?php echo JText::_('COM_DROPFILES_JS_SAVE'); ?></button>
            </fieldset>
        </form>
    <?php }
}
?>
