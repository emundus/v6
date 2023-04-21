<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 19/06/14
 * Time: 11:24
 */
JFactory::getSession()->set('application_layout', 'assessment');
?>
<div class="title" id="em_application_evaluations"> <i class="dropdown icon"></i> <?php echo JText::_('COM_EMUNDUS_EVALUATION_EVALUATIONS'); ?> </div>
<div class="content">
	<iframe classe="iframe evaluation" id="em_evaluations" src="<?php echo JURI::base(); ?>index.php?option=com_emundus&view=evaluation&layout=evaluation&aid=<?php echo $this->student->id;?>&tmpl=component&iframe=1&Itemid=<?php echo $itemid; ?>" width="100%" height="400px" frameborder="0" marfin="0" padding="0"></iframe>
</div>
