<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 19/06/14
 * Time: 11:23
 */
JFactory::getSession()->set('application_layout', 'form');
?>

<!--<div class="active title" id="em_application_forms"> <i class="dropdown icon"></i> </div>
-->
<div class="row">
    <div class="panel panel-default widget">
        <div class="panel-heading">
            <h3 class="panel-title">
                <span class="glyphicon glyphicon-list"></span>
                <?php echo JText::_('APPLICATION_FORM').' - '.$this->formsProgress." % ".JText::_("COMPLETED"); ?>
                <?php if(EmundusHelperAccess::asAccessAction(8, 'c', JFactory::getUser()->id, $this->fnum)):?>
                    <a class="  clean" target="_blank" href="<?php echo JURI::base(); ?>index.php?option=com_emundus&task=pdf&user=<?php echo $this->sid; ?>&fnum=<?php echo $this->fnum; ?>">
                        <button class="btn btn-default" data-title="<?php echo JText::_('DOWNLOAD_APPLICATION_FORM'); ?>" data-toggle="tooltip" data-placement="right" title="<?= JText::_('DOWNLOAD_APPLICATION_FORM'); ?>"><span class="glyphicon glyphicon-save"></span></button>
                    </a>
                <?php endif;?>
            </h3>
        </div>
        <div class="panel-body">
            <div class="active content">
                <?php echo $this->forms; ?>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>
