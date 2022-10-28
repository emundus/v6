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
		<?php echo EventbookingHelperHtml::getFieldLabel('activate_certificate_feature', Text::_('EB_ACTIVATE_CERTIFICATE_FEATURE'), Text::_('EB_ACTIVATE_CERTIFICATE_FEATURE_EXPLAIN')); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('activate_certificate_feature', $config->activate_certificate_feature); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('show_certificate_sent_status', Text::_('EB_SHOW_CERTIFICATE_SENT_STATUS'), Text::_('EB_SHOW_CERTIFICATE_SENT_STATUS_EXPLAIN')); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('show_certificate_sent_status', $config->show_certificate_sent_status); ?>
    </div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('download_certificate_if_checked_in', Text::_('EB_DOWNLOAD_CERTIFICATE_IF_CHECKED_IN'), Text::_('EB_DOWNLOAD_CERTIFICATE_IF_CHECKED_IN_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('download_certificate_if_checked_in', $config->download_certificate_if_checked_in); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('certificate_prefix', Text::_('EB_CERTIFICATE_PREFIX'), Text::_('EB_CERTIFICATE_PREFIX_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="certificate_prefix" class="form-control" value="<?php echo $config->get('certificate_prefix', 'CT'); ?>" size="10" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('certificate_number_length', Text::_('EB_CERTIFICATE_NUMBER_LENGTH'), Text::_('EB_CERTIFICATE_NUMBER_LENGTH_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="certificate_number_length" class="form-control" value="<?php echo $config->get('certificate_number_length', 5); ?>" size="10" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('certificate_page_orientation', Text::_('EB_PAGE_ORIENTATION')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['certificate_page_orientation']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('certificate_page_format', Text::_('EB_PAGE_FORMAT')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['certificate_page_format']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('default_certificate_bg_image', Text::_('EB_DEFAULT_CERTIFICATE_BG_IMAGE'), Text::_('EB_DEFAULT_CERTIFICATE_BG_IMAGE_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getMediaInput($config->get('default_certificate_bg_image'), 'default_certificate_bg_image'); ?>
	</div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('default_certificate_bg_left', Text::_('EB_DEFAULT_BG_POSITION')); ?>
    </div>
    <div class="controls">
		<?php echo Text::_('EB_LEFT') . '    ';?><input type="text" name="default_certificate_bg_left" class="form-control input-mini d-inline-block" value="<?php echo (int) $config->default_certificate_bg_left; ?>" />
		<?php echo Text::_('EB_TOP') . '    ';?><input type="text" name="default_certificate_bg_top" class="form-control input-mini d-inline-block" value="<?php echo (int) $config->default_certificate_bg_top; ?>" />
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('default_certificate_bg_width', Text::_('EB_DEFAULT_BG_SIZE')); ?>
    </div>
    <div class="controls">
		<?php echo Text::_('EB_WIDTH') . ' (mm)   ';?><input type="text" name="default_certificate_bg_width" class="form-control input-mini d-inline-block" value="<?php echo (int) $config->get('default_certificate_bg_width', 210); ?>" />
		<?php echo Text::_('EB_HEIGHT') . ' (mm)   ';?><input type="text" name="default_certificate_bg_height" class="form-control input-mini d-inline-block" value="<?php echo (int) $config->get('default_certificate_bg_height', 297); ?>" />
    </div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('certificate_layout', Text::_('EB_DEFAULT_CERTIFICATE_LAYOUT'), Text::_('EB_DEFAULT_CERTIFICATE_LAYOUT_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'certificate_layout',  $config->certificate_layout , '100%', '550', '75', '8' ) ;?>
	</div>
</div>