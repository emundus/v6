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
		<?php echo EventbookingHelperHtml::getBooleanInput('activate_certificate_feature', $this->item->id ? $this->item->activate_certificate_feature : $this->config->activate_certificate_feature); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('certificate_bg_image', Text::_('EB_CERTIFICATE_BG_IMAGE'), Text::_('EB_CERTIFICATE_BG_IMAGE_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getMediaInput($this->item->certificate_bg_image, 'certificate_bg_image'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('certificate_bg_left', Text::_('EB_BG_POSITION')); ?>
	</div>
	<div class="controls">
		<?php echo Text::_('EB_LEFT') . '    ';?><input type="text" name="certificate_bg_left" class="input-mini form-control d-inline-block" value="<?php echo (int) $this->item->certificate_bg_left; ?>" />
		<?php echo Text::_('EB_TOP') . '    ';?><input type="text" name="certificate_bg_top" class="input-mini form-control d-inline-block" value="<?php echo (int) $this->item->certificate_bg_top; ?>" />
	</div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('certificate_bg_width', Text::_('EB_BG_SIZE')); ?>
    </div>
    <div class="controls">
		<?php echo Text::_('EB_WIDTH') . ' (mm)   ';?><input type="text" name="certificate_bg_width" class="input-mini form-control d-inline-block" value="<?php echo (int) $this->item->certificate_bg_width; ?>" />
		<?php echo Text::_('EB_HEIGHT') . ' (mm)   ';?><input type="text" name="certificate_bg_height" class="input-mini form-control d-inline-block" value="<?php echo (int) $this->item->certificate_bg_height; ?>" />
    </div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('certificate_layout', Text::_('EB_CERTIFICATE_LAYOUT')); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'certificate_layout',  $this->item->certificate_layout , '100%', '250', '90', '10' ) ; ?>
	</div>
</div>