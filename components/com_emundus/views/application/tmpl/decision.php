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

use Joomla\CMS\Factory;

if (version_compare(JVERSION, '4.0', '>')) {
	Factory::getApplication()->getSession()->set('application_layout', 'comment');
}
else {
	Factory::getSession()->set('application_layout', 'comment');
}

?>
<div class="row">
    <div class="panel panel-default widget em-container-decision">
        <div class="panel-heading em-container-decision-heading">
            <h3 class="panel-title">
                <span class="glyphicon glyphicon-check"></span>
				<?php echo JText::_('COM_EMUNDUS_DECISION'); ?>
				<?php if (EmundusHelperAccess::asAccessAction(8, 'c', $this->_user->id, $this->fnum)): ?>
                    <a class="  clean" target="_blank"
                       href="<?php echo JURI::base(); ?>index.php?option=com_emundus&controller=evaluation&task=pdf_decision&user=<?php echo $this->student->id; ?>&fnum=<?php echo $this->fnum; ?>">
                        <button class="btn btn-default"
                                data-title="<?php echo JText::_('COM_EMUNDUS_EXPORTS_DOWNLOAD_PDF'); ?>"
                                data-toggle="tooltip" data-placement="bottom"
                                title="<?= JText::_('COM_EMUNDUS_EXPORTS_DOWNLOAD_PDF'); ?>"><span
                                    class="material-icons">file_download</span></button>
                    </a>
				<?php endif; ?>
                <div class="em-flex-row">
					<?php if (!empty($this->url_form)): ?>
                        <a href="<?php echo $this->url_form; ?>" target="_blank" class="em-flex-row"
                           title="<?php echo JText::_('COM_EMUNDUS_DECISION_OPEN_DECISION_FORM_IN_NEW_TAB_DESC'); ?>"><span
                                    class="material-icons">open_in_new</span></a>
					<?php endif; ?>
                </div>
            </h3>
            <div class="btn-group pull-right">
                <button id="em-prev-file" class="btn btn-info btn-xxl"><span class="material-icons">arrow_back</span>
                </button>
                <button id="em-next-file" class="btn btn-info btn-xxl"><span class="material-icons">arrow_forward</span>
                </button>
            </div>
        </div>
        <div class="panel-body em-container-decision-body">
            <div class="content em-container-decision-body-content">
                <div class="embed-responsive">
                    <div class="form" id="form">
						<?php if (!empty($this->url_form)): ?>
                            <div class="holds-iframe"><?php echo JText::_('COM_EMUNDUS_LOADING'); ?></div>
                            <iframe id="iframe" class="embed-responsive-item" src="<?php echo $this->url_form; ?>"
                                    align="left" frameborder="0" height="600" width="100%" scrolling="no"
                                    marginheight="0" marginwidth="0" onload="resizeIframe(this)"></iframe>
						<?php else: ?>
                            <div class="em_no-form"><?php echo JText::_('COM_EMUNDUS_DECISION_NO_DECISION_FORM_SET'); ?></div>
						<?php endif; ?>
                    </div>
                </div>
                <div class="evaluations" id="evaluations" style="display: none">-----------</div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('.fabrikMainError').hide();

    $('iframe').load(function () {
        $(".holds-iframe").remove();
    }).show();

    $('#iframe').mouseleave(function () {
        resizeIframe(document.getElementById('iframe'));
    });

    $('#iframe').mouseover(function () {
        resizeIframe(document.getElementById('iframe'));
    });

    function resizeIframe(obj) {
        obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
    }

    window.ScrollToTop = function () {
        $('html,body', window.document).animate({
            scrollTop: '0px'
        }, 'slow');
    };

    var url_evaluation = '<?php echo $this->url_evaluation; ?>';

    if (url_evaluation != '') {
        $.ajax({
            type: "GET",
            url: url_evaluation,
            dataType: 'html',
            success: function (data) {
                $("#evaluations").empty();
                $("#evaluations").append(data);
            },
            error: function (jqXHR) {
                console.log(jqXHR.responseText);
            }
        });
    }

</script>
<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>
