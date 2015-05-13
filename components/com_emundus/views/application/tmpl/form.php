<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 19/06/14
 * Time: 11:23
 */
JFactory::getSession()->set('application_layout', 'form');
?>

<div class="active title" id="em_application_forms"> <i class="dropdown icon"></i> <?php echo JText::_('APPLICATION_FORM').' - '.$this->formsProgress." % ".JText::_("COMPLETED"); ?> </div>
<div class="active content">
	<div class="actions">
		<?php if(EmundusHelperAccess::asAccessAction(8, 'c', JFactory::getUser()->id, $this->fnum)):?>
			<a class="  clean" target="_blank" href="<?php echo JURI::Base(); ?>/index.php?option=com_emundus&task=pdf&user=<?php echo $this->sid; ?>&fnum=<?php echo $this->fnum; ?>">
				<button class="btn btn-default" data-title="<?php echo JText::_('DOWNLOAD_APPLICATION_FORM'); ?>"><span class="glyphicon glyphicon-file"></span></button>
			</a>
		<?php endif;?>
		<!--
		<button class="btn btn-default" data-title="<?php echo JText::_('EXPORT_TO_ZIP'); ?>" onclick="document.pressed=this.name;" name="export_zip">
			<span class="glyphicon glyphicon-folder-close"></span>
		</button>
		-->
	</div>
	<?php echo $this->forms; ?>
</div>
