<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

defined('_JEXEC') or die;

$this->loadHelper('select');

$js = <<<JS
akeeba.jQuery(document).ready(function(){
    akeeba.jQuery('#exportdataemailtemplates').change(function(){
        if(akeeba.jQuery(this).val() == 1){
            akeeba.jQuery('#emailtemplateWarning').show();
        }
        else{
            akeeba.jQuery('#emailtemplateWarning').hide();
        }
    });
});
JS;

AkeebaStrapper::addJSdef($js);
?>
<div id="emailtemplateWarning" class="alert alert-error" style="display: none">
    <?php echo JText::_('COM_ADMINTOOLS_IMPORTEXPORT_EMAILTEMPLATE_WARN')?>
</div>
<form name="adminForm" id="adminForm" action="index.php" method="post" class="form form-horizontal">
    <input type="hidden" name="option" value="com_admintools"/>
    <input type="hidden" name="view" value="importexport"/>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="<?php echo JFactory::getSession()->getFormToken(); ?>" value="1"/>

    <fieldset>
        <legend><?php echo JText::_('COM_ADMINTOOLS_IMPORTEXPORT_FINE_TUNING')?></legend>

        <div class="control-group">
            <label class="control-label"><?php echo JText::_('COM_ADMINTOOLS_IMPORTEXPORT_WAFCONFIG')?></label>
            <div class="controls">
                <?php echo AdmintoolsHelperSelect::booleanlist('exportdata[wafconfig]', null, 1);?>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label"><?php echo JText::_('COM_ADMINTOOLS_IMPORTEXPORT_IPBLACKLIST')?></label>
            <div class="controls">
                <?php echo AdmintoolsHelperSelect::booleanlist('exportdata[ipblacklist]', null, 1)?>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label"><?php echo JText::_('COM_ADMINTOOLS_IMPORTEXPORT_IPWHITELIST')?></label>
            <div class="controls">
                <?php echo AdmintoolsHelperSelect::booleanlist('exportdata[ipwhitelist]', null, 1)?>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label"><?php echo JText::_('COM_ADMINTOOLS_IMPORTEXPORT_BADWORDS')?></label>
            <div class="controls">
                <?php echo AdmintoolsHelperSelect::booleanlist('exportdata[badwords]', null, 1)?>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label"><?php echo JText::_('COM_ADMINTOOLS_IMPORTEXPORT_EMAILTEMPLATES')?></label>
            <div class="controls">
                <?php echo AdmintoolsHelperSelect::booleanlist('exportdata[emailtemplates]', null, 0)?>
            </div>
        </div>
    </fieldset>
</form>