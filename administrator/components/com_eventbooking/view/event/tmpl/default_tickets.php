<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die ;

use Joomla\CMS\Language\Text;

?>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('activate_tickets_pdf', Text::_('EB_ACTIVATE_TICKETS_PDF'), Text::_('EB_ACTIVATE_TICKETS_PDF_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('activate_tickets_pdf', $this->item->id ? $this->item->activate_tickets_pdf : $this->config->activate_tickets_pdf); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('ticket_start_number', Text::_('EB_TICKET_START_NUMBER'), Text::_('EB_TICKET_START_NUMBER_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="ticket_start_number" class="form-control" value="<?php echo $this->item->ticket_start_number ? $this->item->ticket_start_number : 1; ?>" size="10" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('ticket_prefix', Text::_('EB_TICKET_PREFIX'), Text::_('EB_TICKET_PREFIX_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="ticket_prefix" class="form-control" value="<?php echo $this->item->ticket_prefix ? $this->item->ticket_prefix : 'TK';; ?>" size="10" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('ticket_bg_image', Text::_('EB_TICKET_BG_IMAGE'), Text::_('EB_TICKET_BG_IMAGE_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getMediaInput($this->item->ticket_bg_image, 'ticket_bg_image'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('ticket_bg_left', Text::_('EB_BG_POSITION')); ?>
	</div>
	<div class="controls">
		<?php echo Text::_('EB_LEFT') . '    ';?><input type="text" name="ticket_bg_left" class="input-mini form-control d-inline-block" value="<?php echo (int) $this->item->ticket_bg_left; ?>" />
		<?php echo Text::_('EB_TOP') . '    ';?><input type="text" name="ticket_bg_top" class="input-mini form-control d-inline-block" value="<?php echo (int) $this->item->ticket_bg_top; ?>" />
	</div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('ticket_bg_width', Text::_('EB_BG_SIZE')); ?>
    </div>
    <div class="controls">
		<?php echo Text::_('EB_WIDTH') . ' (mm)    ';?><input type="text" name="ticket_bg_width" class="input-mini form-control d-inline-block" value="<?php echo (int) $this->item->ticket_bg_width; ?>" />
		<?php echo Text::_('EB_HEIGHT') . ' (mm)    ';?><input type="text" name="ticket_bg_height" class="input-mini form-control d-inline-block" value="<?php echo (int) $this->item->ticket_bg_height; ?>" />
    </div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('ticket_layout', Text::_('EB_TICKET_LAYOUT'), Text::_('EB_TICKET_LAYOUT_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'ticket_layout',  $this->item->ticket_layout, '100%', '550', '75', '8' ); ?>
	</div>
</div>