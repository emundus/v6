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
		<?php echo EventbookingHelperHtml::getBooleanInput('activate_tickets_pdf', $config->activate_tickets_pdf); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('send_tickets_via_email', Text::_('EB_SEND_TICKETS_VIA_EMAIL'), Text::_('EB_SEND_TICKETS_VIA_EMAIL_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('send_tickets_via_email', $config->get('send_tickets_via_email', 1)); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('ticket_number_length', Text::_('EB_TICKET_NUMBER_LENGTH'), Text::_('EB_TICKET_NUMBER_LENGTH_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="ticket_number_length" class="form-control" value="<?php echo $config->get('ticket_number_length', 5); ?>" size="10" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('ticket_page_orientation', Text::_('EB_PAGE_ORIENTATION')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['ticket_page_orientation']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('ticket_page_format', Text::_('EB_PAGE_FORMAT')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['ticket_page_format']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('default_ticket_bg_image', Text::_('EB_DEFAULT_TICKET_BG_IMAGE'), Text::_('EB_DEFAULT_TICKET_BG_IMAGE_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getMediaInput($config->get('default_ticket_bg_image'), 'default_ticket_bg_image'); ?>
	</div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('default_ticket_bg_left', Text::_('EB_DEFAULT_BG_POSITION')); ?>
    </div>
    <div class="controls">
		<?php echo Text::_('EB_LEFT') . '    ';?><input type="text" name="default_ticket_bg_left" class="form-control input-mini d-inline-block" value="<?php echo (int) $config->default_ticket_bg_left; ?>" />
		<?php echo Text::_('EB_TOP') . '    ';?><input type="text" name="default_ticket_bg_top" class="form-control input-mini d-inline-block" value="<?php echo (int) $config->default_ticket_bg_top; ?>" />
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('default_ticket_bg_width', Text::_('EB_DEFAULT_BG_SIZE')); ?>
    </div>
    <div class="controls">
		<?php echo Text::_('EB_WIDTH') . ' (mm)    ';?><input type="text" name="default_ticket_bg_width" class="form-control input-mini d-inline-block" value="<?php echo (int) $config->get('default_ticket_bg_width', 210); ?>" />
		<?php echo Text::_('EB_HEIGHT') . ' (mm)    ';?><input type="text" name="default_ticket_bg_height" class="form-control input-mini d-inline-block" value="<?php echo (int) $config->get('default_ticket_bg_height', 297); ?>" />
    </div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('default_ticket_layout', Text::_('EB_DEFAULT_TICKET_LAYOUT'), Text::_('EB_DEFAULT_TICKET_LAYOUT_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'default_ticket_layout',  $config->default_ticket_layout , '100%', '550', '75', '8' ) ;?>
	</div>
</div>