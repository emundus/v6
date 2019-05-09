<?php
/**
 * @package      Joomla
 * @subpackage   eMundus
 * @link         http://www.emundus.fr
 * @copyright    Copyright (C) 2008 - 2014 eMundus SAS. All rights reserved.
 * @license      GNU/GPL
 * @author       eMundus SAS - Yoan Durand
 */

// No direct access

defined('_JEXEC') or die('Restricted access');

JFactory::getSession()->set('application_layout', 'decision');

//$isCoordinator = EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)?true:false;

?>
<div class="row">
    <div class="panel panel-default widget">
        <div class="panel-heading">
            <h3 class="panel-title">
            <span class="glyphicon glyphicon-check"></span>
                <?php echo JText::_('COM_EMUNDUS_DECISION'); ?>
                <?php if(EmundusHelperAccess::asAccessAction(8, 'c', JFactory::getUser()->id, $this->fnum)):?>
                <a class="  clean" target="_blank" href="<?php echo JURI::base(); ?>index.php?option=com_emundus&controller=evaluation&task=pdf_decision&user=<?php echo $this->student->id; ?>&fnum=<?php echo $this->fnum; ?>">
                    <button class="btn btn-default" data-title="<?php echo JText::_('DOWNLOAD_PDF'); ?>" data-toggle="tooltip" data-placement="bottom" title="<?= JText::_('DOWNLOAD_PDF'); ?>"><span class="glyphicon glyphicon-save"></span></button>
                </a>
                <?php endif; ?>
            </h3>
            <?php if (!empty($this->url_form)):?>
                <a href="<?php echo $this->url_form; ?>" target="_blank" title="<?php echo JText::_('OPEN_DECISION_FORM_IN_NEW_TAB_DESC'); ?>"><span class="glyphicon glyphicon-pencil"></span> <?php echo JText::_('OPEN_DECISION_FORM_IN_NEW_TAB'); ?></a>
            <?php endif; ?>
        </div>
        <div class="panel-body">
            <div class="content">
              <div class="embed-responsive">
                <div class="form" id="form">
                    <?php if(!empty($this->url_form)):?>
                        <div class="holds-iframe"><?php echo JText::_('LOADING'); ?></div>
                        <iframe id="iframe" class="embed-responsive-item" src="<?php echo $this->url_form; ?>" align="left" frameborder="0" height="600" width="100%" scrolling="no" marginheight="0" marginwidth="0" onload="resizeIframe(this)"></iframe>
                    <?php else: ?>
                        <div class="em_no-form"><?php echo JText::_('NO_DECISION_FORM_SET'); ?></div>
                    <?php endif; ?>
                </div>
              </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('.fabrikMainError').hide();

    $('iframe').load(function(){
        $(".holds-iframe").remove();
    }).show();

    function resizeIframe(obj) {
        obj.style.height = obj.contentWindow.document.body.scrollHeight +400 + 'px';
    }

    window.ScrollToTop = function(){
      $('html,body', window.document).animate({
        scrollTop: '0px'
      }, 'slow');
    };

</script>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>