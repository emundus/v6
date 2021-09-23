<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

/* @var EventbookingViewConfigurationHtml $this */
?>

<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('pdf_font', Text::_('EB_PDF_FONT'), Text::_('EB_PDF_FONT_EXPLAIN')); ?>
        <p class="text-warning">
			<?php echo Text::_('EB_PDF_FONT_WARNING'); ?>
        </p>
    </div>
    <div class="controls">
		<?php echo $this->lists['pdf_font']; ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('pdf_margin_left', Text::_('EB_MARGIN_LEFT')); ?>
    </div>
    <div class="controls">
		<input type="number" class="form-control" name="pdf_margin_left" step="1" value="<?php echo $this->config->get('pdf_margin_left', 15); ?>">
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('pdf_margin_right', Text::_('EB_MARGIN_RIGHT')); ?>
    </div>
    <div class="controls">
        <input type="number" class="form-control" name="pdf_margin_right" step="1" value="<?php echo $this->config->get('pdf_margin_right', 15); ?>">
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('pdf_margin_top', Text::_('EB_MARGIN_TOP')); ?>
    </div>
    <div class="controls">
        <input type="number" class="form-control" name="pdf_margin_top" step="1" value="<?php echo $this->config->get('pdf_margin_top', 0); ?>">
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('pdf_margin_bottom', Text::_('EB_MARGIN_BOTTOM')); ?>
    </div>
    <div class="controls">
        <input type="number" class="form-control" name="pdf_margin_bottom" step="1" value="<?php echo $this->config->get('pdf_margin_bottom', 25); ?>">
    </div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registrants_page_orientation', Text::_('EB_REGISTRANTS_EXPORT_PAGE_ORIENTATION')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['registrants_page_orientation']; ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registrants_page_orientation', Text::_('EB_REGISTRANTS_EXPORT_PAGE_FORMAT')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['registrants_page_format']; ?>
	</div>
</div>
<?php
if (PluginHelper::isEnabled('editors', 'codemirror'))
{
	$editorPlugin = 'codemirror';
}
elseif (PluginHelper::isEnabled('editor', 'none'))
{
	$editorPlugin = 'none';
}
else
{
	$editorPlugin = null;
}

if (\Joomla\CMS\Plugin\PluginHelper::isEnabled('eventbooking', 'mpdf'))
{
?>
    <div class="control-group">
        <div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('pdf_css', Text::_('EB_PDF_CSS'), Text::_('EB_PDF_CSS_EXPLAIN')); ?>
        </div>
        <div class="controls">
			<?php
			if ($editorPlugin)
			{
				echo Editor::getInstance('codemirror')->display('pdf_css', $this->config->pdf_css, '100%', '550', '75', '8', false);
			}
			else
			{
			?>
                <textarea name="pdf_css" class="input-xxlarge" rows="10"><?php echo $this->config->pdf_css; ?></textarea>
			<?php
			}
			?>
        </div>
    </div>
<?php
}