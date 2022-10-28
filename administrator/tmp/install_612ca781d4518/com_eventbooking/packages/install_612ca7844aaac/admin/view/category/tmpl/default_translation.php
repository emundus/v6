<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

if (EventbookingHelper::isJoomla4())
{
	$tabApiPrefix = 'uitab.';
}
else
{
	$tabApiPrefix = 'bootstrap.';
}

$rootUri = Uri::root(true);

echo HTMLHelper::_($tabApiPrefix . 'addTab', 'category', 'translation-page', Text::_('EB_TRANSLATION', true));
echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'category-translation', array('active' => 'translation-page-'.$this->languages[0]->sef));

foreach ($this->languages as $language)
{
	$sef = $language->sef;
	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'category-translation', 'translation-page-' . $sef, $language->title . ' <img src="' . $rootUri . '/media/mod_languages/images/' . $language->image . '.gif" />');
	?>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_NAME'); ?>
		</div>
		<div class="controls">
			<input class="input-xlarge form-control" type="text" name="name_<?php echo $sef; ?>" id="name_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'name_' . $sef}; ?>"/>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_ALIAS'); ?>
		</div>
		<div class="controls">
			<input class="input-xlarge form-control" type="text" name="alias_<?php echo $sef; ?>" id="alias_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'alias_' . $sef}; ?>"/>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_PAGE_TITLE'); ?>
		</div>
		<div class="controls">
			<input class="input-xlarge form-control" type="text" name="page_title_<?php echo $sef; ?>" id="page_title_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'page_title_' . $sef}; ?>"/>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_PAGE_HEADING'); ?>
		</div>
		<div class="controls">
			<input class="input-xlarge form-control" type="text" name="page_heading_<?php echo $sef; ?>" id="page_heading_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'page_heading_' . $sef}; ?>"/>
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
			<?php echo  Text::_('EB_META_KEYWORDS'); ?>
        </div>
        <div class="controls">
            <textarea rows="5" cols="30" class="input-xlarge form-control" name="meta_keywords_<?php echo $sef; ?>"><?php echo $this->item->{'meta_keywords_'.$sef}; ?></textarea>
        </div>
    </div>
    <div class="control-group">
        <div class="control-label">
			<?php echo  Text::_('EB_META_DESCRIPTION'); ?>
        </div>
        <div class="controls">
            <textarea rows="5" cols="30" class="input-xlarge form-control" name="meta_description_<?php echo $sef; ?>"><?php echo $this->item->{'meta_description_'.$sef}; ?></textarea>
        </div>
    </div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('EB_DESCRIPTION'); ?>
		</div>
		<div class="controls">
			<?php echo $editor->display('description_' . $sef, $this->item->{'description_' . $sef}, '100%', '250', '75', '10'); ?>
		</div>
	</div>
	<?php
	echo HTMLHelper::_($tabApiPrefix . 'endTab');
}
echo HTMLHelper::_($tabApiPrefix . 'endTabSet');
echo HTMLHelper::_($tabApiPrefix . 'endTab');

