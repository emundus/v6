<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

$this->loadHelper('select');

$js = <<<JS
akeeba.jQuery(document).ready(function($){
    $('#query_type').change(function(){
        if($(this).val() == '')
        {
            $('#fquery').attr('disabled', 'disables').val('');
        }
        else
        {
            $('#fquery').removeAttr('disabled');
        }
    })
    .change();
})
JS;

AkeebaStrapper::addJSdef($js);
?>
<form name="adminForm" id="adminForm" action="index.php" method="post" class="form-horizontal">
	<input type="hidden" name="option" value="com_admintools"/>
	<input type="hidden" name="view" value="wafblacklists"/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="id" value="<?php echo $this->item->id ?>"/>
	<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken(); ?>" value="1"/>

    <div class="control-group">
        <label class="control-label" for="foption"
               title="<?php echo JText::_('ATOOLS_LBL_WAFBLACKLISTS_VERB_TIP') ?>"><?php echo JText::_('ATOOLS_LBL_WAFBLACKLISTS_VERB'); ?></label>

        <div class="controls">
            <?php echo AdmintoolsHelperSelect::httpVerbs('verb', '', $this->item->verb); ?>
        </div>
    </div>

	<div class="control-group">
		<label class="control-label" for="foption"
			   title="<?php echo JText::_('ATOOLS_LBL_WAFEXCEPTIONS_OPTION_TIP') ?>"><?php echo JText::_('ATOOLS_LBL_WAFEXCEPTIONS_OPTION'); ?></label>

		<div class="controls">
			<input class="input-xlarge" type="text" name="foption" id="foption"
				   value="<?php echo $this->item->option ?>">
		</div>
	</div>

	<div class="control-group">
		<label class="control-label" for="fview"
			   title="<?php echo JText::_('ATOOLS_LBL_WAFEXCEPTIONS_VIEW_TIP') ?>"><?php echo JText::_('ATOOLS_LBL_WAFEXCEPTIONS_VIEW'); ?></label>

		<div class="controls">
			<input class="input-xlarge" type="text" name="fview" id="fview" value="<?php echo $this->item->view ?>">
		</div>
	</div>

    <div class="control-group">
        <label class="control-label" for="ftask"
               title="<?php echo JText::_('ATOOLS_LBL_WAFBLACKLISTS_TASK_TIP') ?>"><?php echo JText::_('ATOOLS_LBL_WAFBLACKLISTS_TASK'); ?></label>

        <div class="controls">
            <input class="input-xlarge" type="text" name="ftask" id="ftask" value="<?php echo $this->item->task ?>">
        </div>
    </div>

	<div class="control-group">
		<label class="control-label" for="fquery"
			   title="<?php echo JText::_('ATOOLS_LBL_WAFEXCEPTIONS_QUERY_TIP') ?>"><?php echo JText::_('ATOOLS_LBL_WAFEXCEPTIONS_QUERY'); ?></label>

		<div class="controls">
            <?php echo AdmintoolsHelperSelect::queryParamType('query_type', array('class' => 'input-small'), $this->item->query_type)?>
			<input class="input-xlarge" type="text" name="fquery" id="fquery" value="<?php echo $this->item->query ?>">
		</div>
	</div>

    <div class="control-group">
        <label class="control-label" for="query_content"
               title="<?php echo JText::_('ATOOLS_LBL_WAFBLACKLISTS_QUERY_CONTENT_TIP') ?>"><?php echo JText::_('ATOOLS_LBL_WAFBLACKLISTS_QUERY_CONTENT'); ?></label>

        <div class="controls">
            <input class="input-xlarge" type="text" name="query_content" id="query_content" value="<?php echo $this->item->query_content ?>">
        </div>
    </div>
</form>