<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

DPCalendarHelper::loadLibrary(array('jquery' => true, 'bootstrap' => true, 'dpcalendar' => true));
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

$doc = JFactory::getDocument();
$doc->addStyleSheet(JURI::base() . 'components/com_dpcalendar/views/bookingform/tmpl/edit.css');
$doc->addScript(JURI::base() . 'components/com_dpcalendar/views/bookingform/tmpl/edit.js');
$params = $this->state->get('params');
$booking = $this->booking;
$bookingId = $booking && $booking->id ? $booking->id : 0;
?>
<script type="text/javascript">
var PRICE_URL = '<?php echo JUri::base() . 'index.php?option=com_dpcalendar&task=booking.calculateprice&e_id' . ($this->event ? $this->event->id : 0)?>';

Joomla.submitbutton = function(task) {
	if (task == 'bookingform.cancel' || task == 'bookingform.delete' || document.formvalidator.isValid(document.id('adminForm'))) {
		Joomla.submitform(task);
	}
	else {
		alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
	}
};
</script>
<form action="<?php echo JRoute::_('index.php?option=com_dpcalendar&view=bookingform&b_id=' .
		(int) $bookingId . '&tmpl=' . JRequest::getCmd('tmpl')); ?>"
	method="post" name="adminForm" id="adminForm" class="form-validate dp-container">
	<div class="clearfix"></div>
	<div class="btn-toolbar">
		<div class="btn-group">
			<button type="button" id="dp-booking-submit-button" class="btn btn-primary">
				<i class="icon-ok"></i> <?php echo JText::_(!$bookingId ? 'COM_DPCALENDAR_VIEW_BOOKING_BOOK_BUTTON' : 'JSAVE') ?>
			</button>
		</div>
		<div class="btn-group">
			<button type="button" class="btn" onclick="Joomla.submitbutton('bookingform.cancel')">
				<i class="icon-remove-sign icon-cancel"></i> <?php echo JText::_('JCANCEL') ?>
			</button>
		</div>
		<?php if ($bookingId && !$booking->price)
		{ ?>
		<div class="btn-group">
			<button type="button" class="btn btn-danger" onclick="Joomla.submitbutton('bookingform.delete')">
				<i class="icon-remove-sign icon-trash"></i> <?php echo JText::_('JACTION_DELETE') ?>
			</button>
		</div>
		<?php
		}?>
	</div>
	<div id="dpcalendar-bookingform-loader" style="text-align: center">
		<img src="<?php echo JUri::base()?>media/com_dpcalendar/images/site/ajax-loader.gif"  alt="loader" />
	</div>
	<?php
	echo $this->loadTemplate('payment');
	echo $this->loadTemplate('form');
	?>
</form>
