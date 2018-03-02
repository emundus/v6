<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

$params = JComponentHelper::getParams( 'com_falang' );
?>
<script type="text/javascript">
    <!--
    Joomla.submitbutton = function(task) {
        if (task == 'export.cancel' || document.formvalidator.isValid(document.id('upload-form'))) {
            Joomla.submitform(task, document.getElementById('upload-form'));
        } else {
            alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
        }
    };
    // -->
</script>

<div id="j-main-container">
    <form action="<?php JRoute::_('index.php?option=com_falang'); ?>" method="post" name="adminForm" id="upload-form" class="form-validate"  enctype="multipart/form-data">
        <ul class="unstyled">

            <li><?php echo JText::_('COM_FALANG_EXPORT_SRC_LANGUAGE_LBL').' : '.$this->sourceLanguages;?></li>

            <?php
            $fields = $this->form->getFieldset();
            foreach($fields as $field) {
                echo '<li>'.$field->label.$field->input.'</li>';
            }

            ?>
        </ul>
        <button type="submit" class="btn btn-small btn-success" onclick="Joomla.submitbutton('export.process')"><?php echo JText::_('COM_FALANG_EXPORT_BTN_PROCESS');?></button>
        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>


