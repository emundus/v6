<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die;

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$isJoomla4 = EventbookingHelper::isJoomla4();

HTMLHelper::_('behavior.core');
HTMLHelper::_('bootstrap.tooltip');

if ($isJoomla4)
{
	$tabApiPrefix = 'uitab.';
}
else
{
	HTMLHelper::_('formbehavior.chosen', 'select');
	HTMLHelper::_('behavior.tabstate');

	$tabApiPrefix = 'bootstrap.';
}

$document = Factory::getDocument();
$document->addScript(Uri::root(true).'/media/com_eventbooking/js/admin-category-default.min.js');
$document->addStyleDeclaration(".hasTip{display:block !important}");

$editor = Editor::getInstance(Factory::getApplication()->get('editor'));
$translatable = Multilanguage::isEnabled() && count($this->languages);
$hasCustomSettings = file_exists(__DIR__ . '/default_custom_settings.php');

Text::script('EB_ENTER_CATEGORY_TITLE', true);
?>
<form action="index.php?option=com_eventbooking&view=category" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
<?php
	echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'category', array('active' => 'general-page'));
	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'category', 'general-page', Text::_('EB_GENERAL', true));
?>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('EB_NAME'); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="name" id="name" size="40" maxlength="250" value="<?php echo $this->item->name;?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('EB_ALIAS'); ?>
		</div>
		<div class="controls">
			<input class="form-control" type="text" name="alias" id="alias" maxlength="250" value="<?php echo $this->item->alias;?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('EB_PARENT'); ?>
		</div>
		<div class="controls">
			<?php echo EventbookingHelperHtml::getChoicesJsSelect($this->lists['parent'], Text::_('EB_TYPE_OR_SELECT_ONE_CATEGORY')); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label"><?php echo Text::_('EB_IMAGE'); ?></div>
		<div class="controls">
			<?php echo EventbookingHelperHtml::getMediaInput($this->item->image, 'image', null); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('EB_LAYOUT'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['layout']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('text_color', Text::_('EB_TEXT_COLOR'), Text::_('EB_TEXT_COLOR_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<input type="text" name="text_color" class="form-control color {required:false}" value="<?php echo $this->item->text_color; ?>" size="10" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('color_code', Text::_('EB_COLOR'), Text::_('EB_COLOR_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<input type="text" name="color_code" class="form-control color {required:false}" value="<?php echo $this->item->color_code; ?>" size="10" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('EB_ACCESS_LEVEL'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['access']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo  Text::_('EB_SUBMIT_EVENT_ACCESS_LEVEL'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['submit_event_access']; ?>
		</div>
	</div>
	<?php
	if (Multilanguage::isEnabled())
	{
	?>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('EB_LANGUAGE'); ?>
			</div>
			<div class="controls">
				<?php echo $this->lists['language'] ; ?>
			</div>
		</div>
	<?php
	}
	?>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_PUBLISHED'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['published']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_DESCRIPTION'); ?>
		</div>
		<div class="controls">
			<?php echo $editor->display( 'description',  $this->item->description , '100%', '250', '75', '10' ) ; ?>
		</div>
	</div>
<?php
echo HTMLHelper::_($tabApiPrefix . 'endTab');

echo HTMLHelper::_($tabApiPrefix . 'addTab', 'category', 'seo-options-page', Text::_('EB_SEO_OPTIONS', true));
echo $this->loadTemplate('seo_options');
echo HTMLHelper::_($tabApiPrefix . 'endTab');

// Add support for custom settings layout
if ($hasCustomSettings)
{
	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'category', 'custom-settings-page', Text::_('EB_CATEGORY_CUSTOM_SETTINGS', true));
	echo $this->loadTemplate('custom_settings', array('editor' => $editor));
	echo HTMLHelper::_($tabApiPrefix . 'endTab');
}

if ($translatable)
{
	echo $this->loadTemplate('translation', array('editor' => $editor));
}

echo HTMLHelper::_($tabApiPrefix . 'endTabSet');
?>
<div class="clearfix"></div>
<?php echo HTMLHelper::_( 'form.token' ); ?>
<input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>"/>
<input type="hidden" name="task" value="" />
</form>