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
                <a class="  clean" target="_blank" href="<?php echo JURI::Base(); ?>index.php?option=com_emundus&controller=evaluation&task=pdf_decision&user=<?php echo $this->student->id; ?>&fnum=<?php echo $this->fnum; ?>">
                    <button class="btn btn-default" data-title="<?php echo JText::_('DOWNLOAD_PDF'); ?>"><span class="glyphicon glyphicon-file"></span></button>
                </a>
                <?php endif;?>
            </h3>
            <?php if(!empty($this->url_form)):?>
                <a href="<?php echo $this->url_form; ?>" target="_blank" title="<?php echo JText::_('OPEN_DECISION_FORM_IN_NEW_TAB_DESC'); ?>"><span class="glyphicon glyphicon-pencil"></span> <?php echo JText::_('OPEN_DECISION_FORM_IN_NEW_TAB'); ?></a>
            <?php endif;?>
        </div>
        <div class="panel-body">
            <div class="content">
                <div class="form" id="form">
                    <?php if(!empty($this->url_form)):?>
                        <div class="holds-iframe"><?php echo JText::_('LOADING'); ?></div>
                        <iframe id="iframe" src="<?php echo $this->url_form; ?>" align="left" frameborder="0" height="600" width="100%" scrolling="no" marginheight="0" marginwidth="0"></iframe>
                    <?php else:?>
                        <div class="em_no-form"><?php echo JText::_('NO_DECISION_FORM_SET'); ?></div>
                    <?php endif;?>

<?php
/*
jimport('joomla.filesystem.file');

// Load front end language file as well
$lang = JFactory::getLanguage();
$lang->load('com_fabrik', JPATH_BASE . '/components/com_fabrik');

if (!defined('COM_FABRIK_FRONTEND'))
{
    JError::raiseError(400, JText::_('COM_FABRIK_SYSTEM_PLUGIN_NOT_ACTIVE'));
}

$app = JFactory::getApplication();
$input = $app->input;

FabrikHelperHTML::framework();
require_once COM_FABRIK_FRONTEND . '/controllers/form.php';

// $$$rob looks like including the view does something to the layout variable
$origLayout = $input->get('layout');
require_once COM_FABRIK_FRONTEND . '/views/form/view.html.php';
require_once COM_FABRIK_FRONTEND . '/views/package/view.html.php';
require_once COM_FABRIK_FRONTEND . '/views/list/view.html.php';

$input->set('layout', $origLayout);

JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fabrik/tables');
JModelLegacy::addIncludePath(COM_FABRIK_FRONTEND . '/models', 'FabrikFEModel');

$formId = 39;
$rowid = $this->fnum;

$layout = 'bootstrap';
$usersConfig = JComponentHelper::getParams('com_fabrik');
$usersConfig->set('rowid', $rowid);

$usekey = 'fnum';

if (!empty($usekey))
{
    $input->set('usekey', $usekey);
}

$moduleclass_sfx = '';
$moduleAjax = true;
$origView = $input->get('view');

$input->set('view', 'form');
$controller = new FabrikControllerForm;

$input->set('layout', $layout);

// Display the view
$controller->isMambot = true;
$origFormid = $input->getInt('formid');
$ajax = $input->get('ajax');
$input->set('formid', $formId);

$input->set('ajax', $moduleAjax);
echo $controller->display();

// Reset the layout and view etc for when the component needs them
$input->set('formid', $origFormid);
$input->set('ajax', $ajax);
$input->set('layout', $origLayout);
$input->set('view', $origView);
*/
?>
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

    /*var url_form = '<?php echo $this->url_form; ?>';

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
     */
</script>
