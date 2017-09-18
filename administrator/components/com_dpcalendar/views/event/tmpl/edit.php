<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

DPCalendarHelper::loadLibrary(array('jquery' => true, 'datepicker' => true, 'chosen' => true, 'maps' => true));
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tabstate');
JHtml::_('script', 'system/core.js', false, true);

$input = JFactory::getApplication()->input;

$document = JFactory::getDocument();
$document->addScript(JURI::base() . 'components/com_dpcalendar/views/event/tmpl/edit.js');
$document->addStyleDeclaration('.ui-datepicker { z-index: 1003 !important; }');

JText::script('COM_DPCALENDAR_VIEW_EVENT_SEND_TICKET_HOLDERS_NOFICATION');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'event.cancel' || document.formvalidator.isValid(document.id('event-form'))) {
				<?php echo $this->form->getField('description')->save();
				if (isset($this->item->tickets) && count($this->item->tickets))
				{
				?>
					if (task != 'event.cancel' && confirm(Joomla.JText._('COM_DPCALENDAR_VIEW_EVENT_SEND_TICKET_HOLDERS_NOFICATION'))) {
						jQuery('#jform_notify_changes').val('1');
					}
				<?php
				}?>
				Joomla.submitform(task, document.getElementById('event-form'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<h4>
<font style="color: red">
<?php if ($this->item->original_id > 0)
{
	echo sprintf(JText::_('COM_DPCALENDAR_VIEW_EVENT_GOTO_ORIGINAL'),
		JRoute::_('index.php?option=com_dpcalendar&task=event.edit&id=' . $this->item->original_id));
}?>
<?php if ($this->item->original_id == -1)
{
	echo JText::_('COM_DPCALENDAR_VIEW_EVENT_ORIGINAL_WARNING');
}?>
</font>
</h4>
<form action="<?php echo JRoute::_('index.php?option=com_dpcalendar&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="event-form" class="form-validate">
	<div class="row-fluid">
		<div class="span10 form-horizontal">
			<ul class="nav nav-tabs">
				<li class="active">
					<a href="#general" data-toggle="tab">
						<?php echo empty($this->item->id) ? JText::_('COM_DPCALENDAR_NEW_EVENT') : JText::sprintf('COM_DPCALENDAR_EDIT_EVENT', $this->item->id); ?>
					</a>
				</li>
				<li><a href="#publishing" data-toggle="tab"><?php echo JText::_('JGLOBAL_FIELDSET_PUBLISHING');?></a></li>
				<li><a href="#booking" data-toggle="tab"><?php echo JText::_('COM_DPCALENDAR_VIEW_EVENT_BOOK_OPTIONS');?></a></li>
				<?php
				$fieldSets = $this->form->getFieldsets('params');
				foreach ($fieldSets as $name => $fieldSet)
				{
				?>
				<li><a href="#params-<?php echo $name;?>" data-toggle="tab">
					<?php echo JText::_($fieldSet->label);?></a></li>
				<?php
				} ?>
				<li><a href="#metadata" data-toggle="tab"><?php echo JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS');?></a></li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane active" id="general">
					<div class="row-fluid">
						<div class="span6">
							<?php
							echo $this->form->renderField('title');
							echo $this->form->renderField('catid');
							echo $this->form->renderField('color');
							echo $this->form->renderField('url');
							?>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('location_ids'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('location_ids');
									if (DPCalendarHelper::getActions()->get('core.create'))
									{?>
									<a class="btn btn-micro" href="javascript:void(0);" id="location-activator"><i class="icon-new"></i></a>
									<?php
									}
									if (DPCalendarHelper::getActions()->get('core.delete'))
									{?>
									<a class="btn btn-micro" href="javascript:void(0);" id="location-remove"><i class="icon-delete"></i></a>
									<?php
									}
									if (DPCalendarHelper::getActions()->get('core.create'))
									{?>
									<div id="location-form">
										<div class="btn-toolbar">
											<div class="btn-group">
												<button type="button" class="btn btn-primary" id="location-save-button">
													<i class="icon-ok"></i> <?php echo JText::_('JSAVE') ?>
												</button>
											</div>
											<div class="btn-group">
												<button type="button" class="btn" id="location-cancel-button">
													<i class="icon-cancel"></i> <?php echo JText::_('JCANCEL') ?>
												</button>
											</div>
										</div>
										<?php $locationForm = JForm::getInstance('com_dpcalendar.location', 'location', array('control' => 'location'));
										$locationForm->setFieldAttribute('title', 'required', false);?>
										<input type="hidden" id="location_token" value="<?php echo JSession::getFormToken();?>" />
										<?php
										echo $locationForm->renderField('title');
										echo $locationForm->renderField('country');
										echo $locationForm->renderField('province');
										echo $locationForm->renderField('city');
										echo $locationForm->renderField('zip');
										echo $locationForm->renderField('street');
										echo $locationForm->renderField('number');
										echo $locationForm->renderField('room');
										?>
									</div>
									<?php
									}?>
								</div>
							</div>
						</div>
						<div class="span6 timepair">
							<?php
							echo $this->form->renderField('start_date');
							echo $this->form->renderField('end_date');

							echo $this->loadTemplate('date');
							?>
						</div>
						<div class="clearfix"> </div>
						<?php echo $this->form->getInput('description'); ?>
						<div id="event-location-frame" style="width:100%;height:200px;border-style: none; margin-bottom:20px"></div>

						<?php
						foreach ($this->form->getGroup('images') as $field)
						{
							echo $field->getControlGroup();
						}?>
					</div>
				</div>
				<div class="tab-pane" id="publishing">
					<div class="row-fluid">
						<div class="span6">
							<?php
							echo $this->form->renderField('alias');
							echo $this->form->renderField('id');
							echo $this->form->renderField('created_by');
							echo $this->form->renderField('created_by_alias');
							echo $this->form->renderField('created');
							echo $this->form->renderField('version_note');
							?>
						</div>
						<div class="span6">
							<?php
							echo $this->form->renderField('publish_up');
							echo $this->form->renderField('publish_down');

							if ($this->item->modified_by)
							{
								echo $this->form->renderField('modified_by');
								echo $this->form->renderField('modified');
							}

							if (isset($this->item->version) && $this->item->version)
							{
								echo $this->form->renderField('version');
							}

							if ($this->item->hits)
							{
								echo $this->form->renderField('hits');
							}
							?>
						</div>
					</div>
				</div>
				<div class="tab-pane" id="booking">
					<fieldset>
						<?php
						echo $this->loadTemplate('book');
						?>
					</fieldset>
				</div>
				<div class="tab-pane" id="metadata">
					<fieldset>
						<?php echo $this->loadTemplate('metadata'); ?>
					</fieldset>
				</div>
				<?php echo $this->loadTemplate('params'); ?>
			</div>
			<?php echo $this->form->renderField('notify_changes');?>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="return" value="<?php echo $input->getCmd('return');?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
		<div class="span2">
			<h4><?php echo JText::_('JDETAILS');?></h4>
			<hr />
			<fieldset class="form-vertical">
			<?php
			echo $this->form->renderField('state');
			echo $this->form->renderField('access');
			echo $this->form->renderField('access_content');
			echo $this->form->renderField('featured');
			echo $this->form->renderField('language');
			echo $this->form->renderField('tags');
			?>
			</fieldset>
		</div>
	</div>
</form>

<div align="center" style="clear: both">
	<?php echo sprintf(JText::_('COM_DPCALENDAR_FOOTER'), JRequest::getVar('DPCALENDAR_VERSION'));?>
</div>
