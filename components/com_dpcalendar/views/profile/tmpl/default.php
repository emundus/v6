<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

DPCalendarHelper::loadLibrary(array('jquery' => true, 'bootstrap' => true, 'chosen' => true, 'dpcalendar' => true));

JHtml::_('behavior.tooltip');

$return = JFactory::getApplication()->input->getInt('Itemid', null);
if (! empty($return))
{
	$return = JRoute::_('index.php?Itemid=' . $return);
}

$document = JFactory::getDocument();
$document->addScript(JURI::base() . 'components/com_dpcalendar/views/profile/tmpl/default.js');

JText::script('COM_DPCALENDAR_VIEW_DAVCALENDAR_NONE_SELECTED_LABEL');

echo JLayoutHelper::render('user.timezone');

if ($this->params->get('show_page_heading'))
{?>
	<h1>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
<?php
}?>

<div class="dp-container">
<?php
if ($this->params->get('profile_show_sharing', '1'))
{ ?>
<hr/><h3><?php echo JText::_('COM_DPCALENDAR_VIEW_PROFILE_SHARING')?></h3>
<form class="dp-profile-form form-horizontal" id="sharingForm">
	<div class="control-group">
		<label id="read-users-lbl" for="read-users" class="hasTooltip control-label"
			title="<?php echo JText::_('COM_DPCALENDAR_VIEW_PROFILE_READ_USERS_DESC')?>">
			<?php echo JText::_('COM_DPCALENDAR_VIEW_PROFILE_READ_USERS_LABEL')?>
		</label>
	    <div class="controls">
		    <select multiple="multiple" id="read-users">
				<?php echo JHtml::_('select.options', $this->users, 'value', 'text', $this->readMembers);?>
			</select>
	    </div>
	</div>
	<div class="control-group">
		<label id="write-users-lbl" for="write-users" class="hasTooltip control-label"
			title="<?php echo JText::_('COM_DPCALENDAR_VIEW_PROFILE_WRITE_USERS_DESC')?>">
			<?php echo JText::_('COM_DPCALENDAR_VIEW_PROFILE_WRITE_USERS_LABEL')?>
		</label>
	    <div class="controls">
		    <select multiple="multiple" id="write-users" class="">
				<?php echo JHtml::_('select.options', $this->users, 'value', 'text', $this->writeMembers);?>
			</select>
	    </div>
	</div>
</form>
<?php
} ?>
<hr/><h3><?php echo JText::_('COM_DPCALENDAR_VIEW_PROFILE_CALENDARS')?></h3>
<form action="<?php echo JRoute::_('index.php?option=com_dpcalendar&view=profile&Itemid=' . JRequest::getInt('Itemid'));?>"
	method="post" name="adminForm" id="adminForm" class="dp-profile-form form-inline">
<div class="filters btn-toolbar clearfix">
	<div class="btn-group pull-left">
		<input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('filter.search'));?>"
			class="input-medium" onchange="this.form.submit();" placeholder="<?php echo JText::_('JGLOBAL_FILTER_LABEL');?>"/>
	</div>
	<div class="btn-group pull-right">
		<select id="limit" name="limit" class="inputbox input-mini" size="1" onchange="this.form.submit()">
			<?php echo JHtml::_('select.options', array(5 => 5, 10 => 10, 15 => 15, 20 => 20, 25 => 25, 30 => 30, 50 => 50, 100 => 100, 0 => JText::_('JALL')),
						'value', 'text', $this->state->get('list.limit'))?>
		</select>
	</div>
</div>
<ul class="list-striped">
<?php foreach ($this->calendars as $url => $calendar)
{?>
<li>
	<?php if (empty($calendar->member_principal_access))
	{?>
	<span class="pull-left width-20">
		<a href="<?php echo JRoute::_('index.php?option=com_dpcalendar&task=davcalendar.delete&return=' . base64_encode(JFactory::getURI()) . '&c_id=' . (int) $calendar->id);?>">
			<i class="hasTooltip icon-remove" title="<?php echo JText::_('COM_DPCALENDAR_VIEW_PROFILE_DELETE_PROFILE_CALENDAR')?>"></i>
		</a>
	</span>
	<span class="pull-left width-20">
		<a href="<?php echo DPCalendarHelperRoute::getFormRoute(0, JFactory::getURI(), 'catid=cd-' . (int) $calendar->id);?>">
			<i class="hasTooltip icon-file" title="<?php echo JText::_('COM_DPCALENDAR_VIEW_PROFILE_CREATE_EVENT_IN_CALENDAR')?>"></i>
		</a>
	</span>
	<strong class="list-title">
		<a href="<?php echo JRoute::_('index.php?option=com_dpcalendar&task=davcalendar.edit&c_id=' . (int) $calendar->id .
				'&Itemid=' . JRequest::getInt('Itemid') . '&return=' . base64_encode(JRoute::_('index.php?&Itemid=' . JRequest::getInt('Itemid'))));?>">
			<?php echo $calendar->displayname;?>
		</a>
	</strong>
	<?php
	}
	else
	{
		$text = JText::sprintf('COM_DPCALENDAR_VIEW_PROFILE_SHARED_CALENDAR', $calendar->member_principal_name,
				strpos($calendar->member_principal_access, '/calendar-proxy-read') !== false ?
				JText::_('COM_DPCALENDAR_VIEW_PROFILE_SHARED_CALENDAR_ACCESS_READ') :
				JText::_('COM_DPCALENDAR_VIEW_PROFILE_SHARED_CALENDAR_ACCESS_WRITE'));?>
		<span class="pull-left width-20">
			<i class="hasTooltip icon-lock" title="<?php echo $text?>"></i>
		</span>
		<span class="list-title">
			<?php echo $calendar->displayname;?>
		</span>
	<?php
	}?>
	<span class="list-date small pull-right" style="background-color: #<?php echo $calendar->calendarcolor;?>;width:20px;height:15px"></span>
	<br />
	<small class="list-author"><?php echo JText::_('COM_DPCALENDAR_VIEW_PROFILE_TABLE_CALDAV_URL_LABEL');?>:
		<a href="<?php echo JUri::base() . 'components/com_dpcalendar/caldav.php/' . $url;?>" target="_blank">
			<?php echo $calendar->uri;?>
		</a>
	</small>
</li>
<?php
}?>
</ul>
<span class="hasTooltip" title="<?php echo JText::_('COM_DPCALENDAR_VIEW_PROFILE_CREATE_PROFILE_CALENDAR');?>">
<?php echo JHtml::_('link', JRoute::_('index.php?option=com_dpcalendar&task=davcalendar.add&return=' . base64_encode(JFactory::getURI()) . '&c_id=0'), JHtml::_('image', 'system/new.png', JText::_('JNEW'), null, true));?>
</span>
<div class="pagination">
		<p class="counter pull-right"><?php echo $this->pagination->getPagesCounter();?></p>
		<?php echo $this->pagination->getPagesLinks();?>
</div>
<input type="hidden" name="filter_order" value="" />
<input type="hidden" name="filter_order_Dir" value="" />
<input type="hidden" name="limitstart" value="" />
<input type="hidden" id="token" value="<?php echo JSession::getFormToken();?>"/>
</form>

<hr/><h3><?php echo JText::_('COM_DPCALENDAR_VIEW_PROFILE_UPCOMING_EVENTS')?></h3>
<ul class="list-striped">
<?php
foreach ($this->events as $event) {
	$calendar = DPCalendarHelper::getCalendar($event->catid);
?>
	<li>
	<span class="badge badge-info pull-right"><?php echo JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_URL') . ': ' . $event->hits?></span>
<?php
	if ($calendar->canEdit || ($calendar->canEditOwn && $event->created_by == JFactory::getUser()->id))
	{
?>
	<span class="pull-left width-20"><a href="<?php echo DPCalendarHelperRoute::getFormRoute($event->id, $return);?>"><i class="hasTooltip icon-edit" title="<?php echo JText::_('JACTION_EDIT');?>"></i></a></span>
<?php
	}
?>
	<strong class="list-title">
		<a href="<?php echo DPCalendarHelperRoute::getEventRoute($event->id, $event->catid)?>" itemprop="url"><span itemprop="name"><?php echo htmlspecialchars($event->title)?></span></a>
	</strong>
	<small class="list-author">
			(<?php echo JText::_('COM_DPCALENDAR_FIELD_CONFIG_EVENT_LABEL_DATE');?>: <?php echo DPCalendarHelper::getDateStringFromEvent($event);?>)
	</small>
	<span class="list-date small pull-right">
	<?php
	if (isset($event->locations))
	{
		foreach ($event->locations as $location)
		{ ?>
			<div class="dp-location" data-latitude="<?php echo $location->latitude;?>" data-longitude="<?php echo $location->longitude?>" data-title="<?php echo $this->escape($location->title);?>">
				<a href="http://maps.google.com/?q=<?php echo $this->escape(DPCalendarHelperLocation::format($location));?>" target="_blank"><?php echo $this->escape($location->title);?></a>
			</div>
			<br/>
		<?php
		}
	}?>
	</span>
</li>
<?php
}
?>
</div>
