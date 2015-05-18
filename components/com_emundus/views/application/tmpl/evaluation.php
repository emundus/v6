<?php
/**
 * Created by PhpStorm.
 * User: brivalland
 * Date: 17/10/14
 * Time: 11:39
 * @package        Joomla
 * @subpackage    eMundus
 * @link          http://www.emundus.fr
 * @copyright    Copyright (C) 2008 - 2014 Décision Publique. All rights reserved.
 * @license        GNU/GPL
 * @author        Decision Publique - Yoan Durand
 */

// No direct access

defined('_JEXEC') or die('Restricted access');

JFactory::getSession()->set('application_layout', 'evaluation');

$isCoordinator = EmundusHelperAccess::asCoordinatorAccessLevel($this->_user->id)?true:false;

?>
<div class="content">
    <?php if(EmundusHelperAccess::asAccessAction(8, 'c', JFactory::getUser()->id, $this->fnum)):?>
        <div class="actions">
            <a class="  clean" target="_blank" href="<?php echo JURI::Base(); ?>index.php?option=com_emundus&controller=evaluation&task=pdf&user=<?php echo $this->student->id; ?>&fnum=<?php echo $this->fnum; ?>">
                <button class="btn btn-default" data-title="<?php echo JText::_('DOWNLOAD_EVALUATIONS'); ?>"><span class="glyphicon glyphicon-file"></span></button>
            </a>
        </div>
    <?php endif;?>
    <div class="form" id="form"></div>
    <a href="<?php echo $this->url_form; ?>" target="_blank" title="<?php echo JText::_('OPEN_EVALUATION_FORM_IN_NEW_TAB_DESC'); ?>"><span class="glyphicon glyphicon-pencil"></span> <?php echo JText::_('OPEN_EVALUATION_FORM_IN_NEW_TAB'); ?></a>
    <div class="evaluations" id="evaluations"></div>
</div>
<script type="text/javascript">
var url_evaluation = '<?php echo $this->url_evaluation; ?>';
var url_form = '<?php echo $this->url_form; ?>';
if (url_evaluation != '') {
    $.ajax({
            type: "GET",
            url: url_evaluation,
            dataType: 'html',
            success: function(data) {
                $("#evaluations").empty();
                $("#evaluations").append(data);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
}
if (url_form != '') {
    $.ajax({
            type: "GET",
            url: url_form,
            dataType: 'text',
            success: function(data) {
                var form = data;
                form = form.replace('<script src="/media/system/js/calendar.js" type="text/javascript"><\/script>',''); 
                form = form.replace('<script src="/media/system/js/calendar-setup.js" type="text/javascript"><\/script>',''); 
                form = form.replace('<script src="/media/system/js/mootools-core.js" type="text/javascript"><\/script>',''); 
                form = form.replace('<script src="/media/system/js/core.js" type="text/javascript"><\/script>',''); 
                form = form.replace('<script src="/media/system/js/mootools-more.js" type="text/javascript"><\/script>',''); 
                form = form.replace('<script src="/media/com_fabrik/js/lib/art.js" type="text/javascript"><\/script>',''); 
                form = form.replace('<script src="/libraries/gantry/js/browser-engines.js" type="text/javascript"><\/script>',''); 
                $("#form").empty();
                $("#form").append(form);
                $("#form").empty();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(jqXHR.responseText);
            }
        });
}
</script>