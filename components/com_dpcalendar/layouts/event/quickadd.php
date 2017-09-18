<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$params = $displayData['params'];
if (! $params)
{
	$params = new JRegistry();
}

$uniqueId = $params->get('uniqueIdentifier', '');

JFactory::getLanguage()->load('com_dpcalendar', JPATH_ADMINISTRATOR . '/components/com_dpcalendar');
JFactory::getLanguage()->load('com_dpcalendar', JPATH_ROOT . '/components/com_dpcalendar');

$dateVar = JRequest::getVar('date', null);
$local = false;
if (strpos($dateVar, '00-00') != false)
{
	$dateVar = substr($dateVar, 0, 10) . DPCalendarHelper::getDate()->format(' H:i');
	$local = true;
}
$date = DPCalendarHelper::getDate($dateVar);
$date->setTime($date->format('H'), 0);

JLoader::import('joomla.form.form');

JForm::addFormPath(JPATH_ROOT . '/components/com_dpcalendar/models/forms');
JForm::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/models/fields');

$form = JForm::getInstance('com_dpcalendar.event', 'event', array('control' => 'jform'));
$form->setValue('start_date', null, $date->format($params->get('event_form_date_format', 'm.d.Y') . ' ' . $params->get('event_form_time_format', 'g:i a'), $local));
$date->modify('+1 hour');
$form->setValue('end_date', null, $date->format($params->get('event_form_date_format', 'm.d.Y') . ' ' . $params->get('event_form_time_format', 'g:i a'), $local));
$form->setFieldAttribute('title', 'class', 'input-medium');

$form->setFieldAttribute('start_date', 'format', $params->get('event_form_date_format', 'm.d.Y'));
$form->setFieldAttribute('start_date', 'formatTime', $params->get('event_form_time_format', 'g:i a'));
$form->setFieldAttribute('start_date', 'formated', true);
$form->setFieldAttribute('end_date', 'format', $params->get('event_form_date_format', 'm.d.Y'));
$form->setFieldAttribute('end_date', 'formatTime', $params->get('event_form_time_format', 'g:i a'));
$form->setFieldAttribute('end_date', 'formated', true);


$calCode = "// <![CDATA[ \n";
$calCode .= "jQuery(document).ready(function(){\n";

$calCode .= "jQuery('#dpcal-create-" . $uniqueId . "').click(function(){
    jQuery('#editEventForm" . $uniqueId . "').submit();
});
jQuery('.dpcal-cancel-" . $uniqueId . "').click(function(){
    jQuery('#editEventForm" . $uniqueId . "').toggle();
    jQuery('#editEventForm" . $uniqueId . " #jform_title').val('');
    return false;
});
jQuery('#dpcal-edit-" . $uniqueId . "').click(function(){
    jQuery('#editEventForm" . $uniqueId . " #task').val('');
    jQuery('#editEventForm" . $uniqueId . "').submit();
});

jQuery('body').click(function(e) {
    var form = jQuery('#editEventForm" . $uniqueId . "');

    if (form.has(e.target).length === 0 && !jQuery('#ui-datepicker-div').is(':visible') && !jQuery(e.target).hasClass('ui-timepicker-selected')) {
        form.hide();
    }
});

jQuery(window).on('hashchange', function() {
  jQuery('#editEventForm" . $uniqueId . " #urlhash').val(window.location.hash);
});
jQuery('#editEventForm" . $uniqueId . " #urlhash').val(window.location.hash);
";
$calCode .= "});\n";
$calCode .= "// ]]>\n";
JFactory::getDocument()->addScriptDeclaration($calCode);

JFactory::getDocument()->addStyleDeclaration('.dpcalendar-quick-add-form {
	display: none;
	position: absolute;
	background-color: white;
	z-index: 1002;
	border: 1px solid #ccc;
	max-width: 320px;
	padding: 5px;
}

.dpcalendar-quick-add-form .control-group, .dpcalendar-quick-add-form .control-group .controls  {
	margin: 2px;
	padding: 0;
}

.dpcalendar-quick-add-form .control-group .control-label {
	width: 80px;
}

.dpcalendar-quick-add-form .control-group label {
	height: 14px;
	font-size: 10px;
	line-height: 14px;
	margin-top: 5px;
}

.ui-timepicker-list li {
	height: 14px;
	font-size: 10px;
	line-height: 14px;
	margin-top: 5px;
}

.dpcalendar-quick-add-form .control-group input {
	height: 14px;
	font-size: 10px;
	line-height: 14px;
}

.dpcalendar-quick-add-form .control-group select {
	height: 28px;
	font-size: 10px;
	line-height: 14px;
	padding: 0;
}');
?>
<form action="<?php echo JRoute::_(DPCalendarHelperRoute::getFormRoute(0, JUri::getInstance()->toString())); ?>" method="post"
      id="editEventForm<?php echo $uniqueId ?>" class="form-validate dp-container dpcalendar-quick-add-form">
	<button class="close dpcal-cancel-<?php echo $uniqueId ?>">&times;</button>
	<div class="span12 col-md-12 form-horizontal timepair">
		<div class="control-group">
			<div class="control-label">
				<?php echo $form->getLabel('start_date'); ?>
			</div>
			<div class="controls">
				<?php echo $form->getInput('start_date'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $form->getLabel('end_date'); ?>
			</div>
			<div class="controls">
				<?php echo $form->getInput('end_date'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $form->getLabel('title'); ?>
			</div>
			<div class="controls">
				<?php echo $form->getInput('title'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $form->getLabel('catid'); ?>
			</div>
			<div class="controls">
				<?php echo $form->getInput('catid'); ?>
			</div>
		</div>
	</div>

	<input type="hidden" name="urlhash" id="urlhash" value=""/>
	<input type="hidden" name="task" id="task" value="event.save"/>
	<input type="hidden" name="jform[capacity]" value="0"/>
	<input type="hidden" name="jform[all_day]" value="0"/>
	<input type="hidden" name="layout" id="layout" value="edit"/>
	<?php echo JHtml::_('form.token'); ?>
	<button id="dpcal-create-<?php echo $uniqueId ?>" class="btn btn-mini btn-primary btn-default btn-xs" type="button">
		<?php echo JText::_('COM_DPCALENDAR_VIEW_FORM_BUTTON_SUBMIT_EVENT'); ?>
	</button>
	<button id="dpcal-edit-<?php echo $uniqueId ?>" class="btn btn-mini btn-default btn-xs" type="button">
		<?php echo JText::_('COM_DPCALENDAR_VIEW_FORM_BUTTON_EDIT_EVENT'); ?>
	</button>
	<button class="btn btn-mini btn-danger btn-default btn-xs dpcal-cancel-<?php echo $uniqueId ?>" type="button">
		<?php echo JText::_('JCANCEL'); ?>
	</button>
</form>
