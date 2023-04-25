<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;

$params = JComponentHelper::getParams( 'com_falang' );
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('form.validate');

?>
<script type="text/javascript">
    <!--
    Joomla.submitbutton = function(task) {
        if (task == 'import.cancel' || document.formvalidator.isValid(document.id('upload-form'))) {
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


            <?php
            $fields = $this->form->getFieldset();
            foreach($fields as $field) {
                echo '<li>'.$field->label.$field->input.'</li>';
            }

            ?>
        </ul>

        <button type="submit" class="btn btn-small btn-success" onclick="Joomla.submitbutton('import.process')"><?php echo JText::_('COM_FALANG_IMPORT_BTN_PROCESS');?></button>
        <input type="hidden" name="task" value="" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>


