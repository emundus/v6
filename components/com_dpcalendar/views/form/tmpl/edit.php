<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

DPCalendarHelper::loadLibrary(array('jquery' => true, 'datepicker' => true, 'bootstrap' => true, 'chosen' => true, 'dpcalendar' => true));

$document = JFactory::getDocument();
$document->addStyleDeclaration('.ui-datepicker, .ui-timepicker-list { font:90% Arial,sans-serif; }');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
JHtml::_('script', 'system/core.js', false, true);
JHtml::_('behavior.tabstate');
JHtml::_('behavior.modal', 'a.modal_jform_contenthistory');

$document->addStyleSheet(JURI::base() . 'components/com_dpcalendar/views/form/tmpl/edit.css');
$document->addScript(JURI::base() . 'components/com_dpcalendar/views/form/tmpl/edit.js');

JText::script('COM_DPCALENDAR_VIEW_EVENT_SEND_TICKET_HOLDERS_NOFICATION');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'event.cancel' || document.formvalidator.isValid(document.id('event-form'))) {
			<?php
			$d = $this->form->getField('description');
			if ($d && method_exists($d, 'save')) echo $d->save();
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
<?php if ($this->params->get('show_page_heading', 1))
{ ?>
	<div class="page-header">
		<h1>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
	</div>
<?php
}
if ($this->item->original_id != '0')
{?>
	<h4>
	<span style="color: red">
	<?php if ($this->item->original_id == '-1')
	{
		echo JText::_('COM_DPCALENDAR_VIEW_EVENT_ORIGINAL_WARNING');
	}
	else if (!empty($this->item->original_id))
	{
		echo sprintf(JText::_('COM_DPCALENDAR_VIEW_EVENT_GOTO_ORIGINAL'),
			DPCalendarHelperRoute::getFormRoute($this->item->original_id, base64_decode($this->return_page)));
	}?>
	</span>
	</h4>
<?php
}
if ($this->params->get('event_form_check_overlaping', 0))
{ ?>
	<p id="dp-form-message-box" class="alert"></p>
<?php
}
?>
<form action="<?php echo JRoute::_('index.php?option=com_dpcalendar&layout=edit&e_id=' . $this->item->id); ?>"
	method="post" name="adminForm" id="event-form" class="form-validate form-horizontal dp-container">
	<div class="btn-toolbar">
		<?php
		$calendar = DPCalendarHelper::getCalendar($this->form->getValue('catid'));
		$disableSave = $this->params->get('event_form_check_overlaping', 0) == '2' ? ' save-button' : '';
		if ($calendar->canEdit || ($calendar->canEditOwn && $this->item->created_by == JFactory::getUser()->id))
		{ ?>
		<div class="btn-group">
			<button type="button" class="btn btn-primary<?php echo $disableSave;?>" onclick="Joomla.submitbutton('event.apply')">
				<i class="icon-ok"></i> <?php echo JText::_('JAPPLY') ?>
			</button>
		</div>
		<?php
		} ?>
		<div class="btn-group">
			<button type="button" class="btn<?php echo $disableSave;?>" onclick="Joomla.submitbutton('event.save')">
				<i class="icon-ok"></i> <?php echo JText::_('JSAVE') ?>
			</button>
		</div>
		<div class="btn-group">
			<button type="button" class="btn<?php echo $disableSave;?>" onclick="Joomla.submitbutton('event.save2new')">
				<i class="icon-ok"></i> <?php echo JText::_('JTOOLBAR_SAVE_AND_NEW') ?>
			</button>
		</div>
		<?php if ($this->params->get('save_history', 0))
		{ ?>
			<div class="btn-group">
				<?php echo $this->form->getInput('contenthistory'); ?>
			</div>
		<?php
		} ?>
		<div class="btn-group">
			<button type="button" class="btn" onclick="Joomla.submitbutton('event.cancel')">
				<i class="icon-remove-sign icon-cancel"></i> <?php echo JText::_('JCANCEL') ?>
			</button>
		</div>
	</div>
	<fieldset>
		<ul class="nav nav-tabs navbar-nav">
			<li class="active">
				<a href="#general" data-toggle="tab">
					<?php echo empty($this->item->id) ? JText::_('COM_DPCALENDAR_NEW_EVENT') : JText::sprintf('COM_DPCALENDAR_EDIT_EVENT', $this->item->id); ?>
				</a>
			</li>
			<?php if($this->params->get('event_form_change_location', 1))
			{
				DPCalendarHelper::loadLibrary(array('maps' => true));
			?>
				<li><a href="#location" data-toggle="tab" id="dp-form-location-tab"><?php echo JText::_('COM_DPCALENDAR_FIELD_LOCATION_LABEL') ?></a></li>
			<?php
			}
			if (is_numeric($this->item->catid) || empty($this->item->id))
			{
				$fieldSets = $this->form->getFieldsets('params');
				foreach ($fieldSets as $name => $fieldSet)
				{
					if($name == 'jbasic' && !$this->params->get('event_form_change_options', 1))
					{
						continue;
					}
				?>
					<li><a href="#params-<?php echo $name;?>" data-toggle="tab"><?php echo JText::_($fieldSet->label);?></a></li>
				<?php
				}
				if($this->params->get('event_form_change_book', 1))
				{?>
					<li><a href="#book" data-toggle="tab"><?php echo JText::_('COM_DPCALENDAR_VIEW_EVENT_BOOK_OPTIONS');?></a></li>
				<?php
				}
				if($this->params->get('event_form_change_publishing', 1))
				{?>
					<li><a href="#publishing" data-toggle="tab"><?php echo JText::_('JGLOBAL_FIELDSET_PUBLISHING');?></a></li>
				<?php
				}
				if($this->params->get('event_form_change_language', 1))
				{?>
					<li><a href="#language" data-toggle="tab"><?php echo JText::_('JFIELD_LANGUAGE_LABEL') ?></a></li>
				<?php
				}
				if($this->params->get('event_form_change_metadata', 1))
				{?>
					<li><a href="#metadata" data-toggle="tab"><?php echo JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS');?></a></li>
				<?php
				}
			}?>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active timepair" id="general">
					<?php
					echo $this->form->renderField('title');
					echo $this->form->renderField('catid');
					echo $this->form->renderField('color');
					echo $this->form->renderField('url');
					echo $this->form->renderField('start_date');
					echo $this->form->renderField('end_date');
					echo $this->loadTemplate('date');
				    echo $this->form->getInput('description');

				    foreach ($this->form->getGroup('images') as $field)
				    {
				    	echo $field->getControlGroup();
				    }

					echo $this->form->renderField('captcha');
				    ?>
			</div>

			<?php echo $this->loadTemplate('params'); ?>

			<div class="tab-pane" id="location">
				<?php
				echo $this->form->renderField('location');

				if ($this->form->getField('location_ids'))
				{?>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('location_ids'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('location_ids');
						if (DPCalendarHelper::getActions()->get('core.create'))
						{?>
						<a class="btn btn-micro" href="javascript:void(0);" id="location-activator"><i class="icon-new icon-plus-sign"></i></a>
						<?php
						}
						if (DPCalendarHelper::getActions()->get('core.delete'))
						{?>
						<a class="btn btn-micro" href="javascript:void(0);" id="location-remove"><i class="icon-delete icon-remove-sign"></i></a>
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
										<i class="icon-cancel icon-remove-sign"></i> <?php echo JText::_('JCANCEL') ?>
									</button>
								</div>
							</div>
							<?php
							JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/com_dpcalendar/models/forms');
							$locationForm = JForm::getInstance('com_dpcalendar.location', 'location', array('control' => 'location'));
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
				<div class="control-group">
					<div id="event-location-frame" style="width:100%;height:200px;border-style: none;">&nbsp;</div>
				</div>
				<?php
                    }?>
			</div>
			<div class="tab-pane" id="book">
				<?php echo $this->loadTemplate('booking'); ?>
			</div>
			<div class="tab-pane" id="publishing">
				<div class="row-fluid row">
					<div class="span6 col-md-6">
						<?php
						echo $this->form->renderField('alias');
						?>
						<div class="control-group" <?php echo $this->params->get('event_form_change_tags', '1') != '1' ? 'style="display:none"' : ''?>>
							<div class="control-label">
								<?php echo $this->form->getLabel('tags'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('tags'); ?>
							</div>
						</div>
						<?php if ($this->params->get('save_history', 0))
						{
							echo $this->form->renderField('version_note');
						}

						echo $this->form->renderField('publish_up');
						echo $this->form->renderField('publish_down');
						?>
						<?php if ((!$this->item->id && $this->user->authorise('core.edit.state', 'com_dpcalendar')) || ($this->item->id && $this->user->authorise('core.edit.state', 'com_dpcalendar.category.' . $this->item->catid)))
						{
							echo $this->form->renderField('state');
						}

						echo $this->form->renderField('access');
						echo $this->form->renderField('access_content');
						echo $this->form->renderField('featured');
						echo $this->form->renderField('xreference');
						?>
					</div>
				</div>
			</div>
			<div class="tab-pane" id="metadata">
				<?php echo $this->loadTemplate('metadata'); ?>
			</div>
			<div class="tab-pane" id="language">
				<div class="row-fluid row">
					<div class="span6 col-md-6">
						<?php echo $this->form->renderField('language');?>
					</div>
				</div>
			</div>
		</div>
		<?php echo $this->form->renderField('notify_changes');?>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="tmpl" value="<?php echo JFactory::getApplication()->input->get('tmpl');?>" />
		<input type="hidden" name="urlhash" value="<?php echo JFactory::getApplication()->input->getString('urlhash');?>" />
		<input type="hidden" name="return" value="<?php echo $this->return_page;?>" />
		<?php echo JHtml::_('form.token'); ?>
	</fieldset>
</form>
