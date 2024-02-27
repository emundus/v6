<?php
/**
 * User: brivalland
 * Date: 17/06/16
 * Time: 11:39
 * @package       Joomla
 * @subpackage    eMundus
 * @link          http://www.emundus.fr
 * @copyright     Copyright (C) 2016 eMundus. All rights reserved.
 * @license       GNU/GPL
 * @author        eMundus
 */

// No direct access

defined('_JEXEC') or die('Restricted access');

JFactory::getSession()->set('application_layout', 'evaluation');
?>

<div class="row">
    <div class="panel panel-default widget em-container-evaluation">
        <div class="panel-heading em-container-evaluation-heading">
            <h3 class="panel-title">
                Phase de gestion
                <?php if (EmundusHelperAccess::asAccessAction(8, 'c', JFactory::getUser()->id, $this->fnum) && !empty($this->url_form)) :?>
                    <a class="clean" target="_blank" href="<?= JURI::base(); ?>index.php?option=com_emundus&task=pdf_by_form&user=<?= $this->student->id; ?>&fnum=<?= $this->fnum; ?>&form=<?= $this->form_id; ?>">
                        <button class="btn btn-default" data-title="<?= JText::_('COM_EMUNDUS_EXPORTS_DOWNLOAD_PDF'); ?>" data-toggle="tooltip" data-placement="bottom" title="<?= JText::_('COM_EMUNDUS_EXPORTS_DOWNLOAD_PDF'); ?>"><span class="material-icons">file_download</span></button>
                    </a>
                <?php endif;?>
                <div class="em-flex-row">
                    <?php if (!empty($this->url_form)) :?>
                        <a href="<?= $this->url_form; ?>" target="_blank" class="em-flex-row" title="<?= JText::_('COM_EMUNDUS_EVALUATIONS_OPEN_EVALUATION_FORM_IN_NEW_TAB_DESC'); ?>"><span class="material-icons">open_in_new</span></a>
                    <?php endif;?>
                </div>
            </h3>
            <div class="btn-group pull-right">
                <button id="em-prev-file" class="btn btn-info btn-xxl"><span class="material-icons">arrow_back</span></button>
                <button id="em-next-file" class="btn btn-info btn-xxl"><span class="material-icons">arrow_forward</span></button>
            </div>
        </div>
        <div class="panel-body em-container-evaluation-body">
            <div class="content">
                <div class="form" id="form">
                    <?php if (!empty($this->url_form)) :?>
                        <div class="holds-iframe"><?= JText::_('COM_EMUNDUS_LOADING'); ?></div>
                        <iframe id="iframe" src="<?= $this->url_form; ?>" align="left" frameborder="0" height="600" width="100%" scrolling="no" marginheight="0" marginwidth="0" onload="resizeIframe(this)"></iframe>
                    <?php else :?>
                        <div class="em_no-form"><?= JText::_('COM_EMUNDUS_EVALUATIONS_NO_EVALUATION_FORM_SET'); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

    $('iframe').load(function() {
        $(".holds-iframe").remove();
    }).show();

    $('#iframe').mouseleave(function() {
        resizeIframe(document.getElementById('iframe'));
    });

    $('#iframe').mouseover(function() {
        resizeIframe(document.getElementById('iframe'));
    });

    function resizeIframe(obj) {
        obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
    }

    window.ScrollToTop = function() {
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