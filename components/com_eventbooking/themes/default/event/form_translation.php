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

$bootstrapHelper   = EventbookingHelperBootstrap::getInstance();
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');
$rootUri           = Uri::root();

echo HTMLHelper::_($tabApiPrefix . 'addTab', 'event', 'translation-page', Text::_('EB_TRANSLATION', true));
echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'event-translation', array('active' => 'translation-page-'.$this->languages[0]->sef));

foreach ($this->languages as $language)
{
	$sef = $language->sef;
	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'event-translation', 'translation-page-' . $sef, $language->title . ' <img src="' . $rootUri . '/media/mod_languages/images/' . $language->image . '.gif" />');
	?>
	<div class="<?php echo $controlGroupClass; ?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo EventbookingHelperHtml::getFieldLabel('use_data_from_default_language_'.$sef, Text::_('EB_USE_DATA_FROM_DEFAULT_LANGUAGE'), Text::_('EB_USE_DATA_FROM_DEFAULT_LANGUAGE_EXPLAIN')) ?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<input type="checkbox" name="use_data_from_default_language_<?php echo $sef; ?>" value="1" />
		</div>
    </div>
	
	<div class="<?php echo $controlGroupClass; ?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo  Text::_('EB_TITLE'); ?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<input class="input-xlarge" type="text" name="title_<?php echo $sef; ?>" id="title_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->escape($this->item->{'title_'.$sef}); ?>" />
		</div>
	</div>
    <?php
	if ($this->config->get('fes_show_alias', 1))
    {
    ?>
        <div class="<?php echo $controlGroupClass; ?>">
            <div class="<?php echo $controlLabelClass; ?>">
			    <?php echo  Text::_('EB_ALIAS'); ?>
            </div>
            <div class="<?php echo $controlsClass; ?>">
                <input class="input-xlarge" type="text" name="alias_<?php echo $sef; ?>" id="alias_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'alias_'.$sef}; ?>" />
            </div>
        </div>
    <?php
    }

	if ($this->config->get('fes_show_price_text'))
    {
    ?>
        <div class="<?php echo $controlGroupClass; ?>">
            <div class="<?php echo $controlLabelClass; ?>">
			    <?php echo  Text::_('EB_PRICE_TEXT'); ?>
            </div>
            <div class="<?php echo $controlsClass; ?>">
                <input class="input-xlarge" type="text" name="price_text_<?php echo $sef; ?>" id="price_text_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->escape($this->item->{'price_text_'.$sef}); ?>" />
            </div>
        </div>
    <?php
    }

	if ($this->config->get('fes_show_custom_registration_handle_url', 1))
    {
    ?>
        <div class="<?php echo $controlGroupClass; ?>">
            <div class="<?php echo $controlLabelClass; ?>">
			    <?php echo  Text::_('EB_CUSTOM_REGISTRATION_HANDLE_URL'); ?>
            </div>
            <div class="<?php echo $controlsClass; ?>">
                <input class="input-xlarge" type="text" name="registration_handle_url_<?php echo $sef; ?>" id="registration_handle_url_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'registration_handle_url_'.$sef}; ?>" />
            </div>
        </div>
    <?php
    }

	if ($this->config->get('fes_show_short_description', 1))
    {
    ?>
        <div class="<?php echo $controlGroupClass; ?>">
            <div class="<?php echo $controlLabelClass; ?>">
			    <?php echo Text::_('EB_SHORT_DESCRIPTION'); ?>
            </div>
            <div class="<?php echo $controlsClass; ?>">
			    <?php echo $editor->display( 'short_description_'.$sef,  $this->item->{'short_description_'.$sef} , '100%', '250', '75', '10' ) ; ?>
            </div>
        </div>
    <?php
    }

	if ($this->config->get('fes_show_description', 1))
    {
    ?>
        <div class="<?php echo $controlGroupClass; ?>">
            <div class="<?php echo $controlLabelClass; ?>">
			    <?php echo Text::_('EB_DESCRIPTION'); ?>
            </div>
            <div class="<?php echo $controlsClass; ?>">
			    <?php echo $editor->display( 'description_'.$sef,  $this->item->{'description_'.$sef} , '100%', '250', '75', '10' ) ; ?>
            </div>
        </div>
    <?php
    }
    ?>
	<div class="<?php echo $controlGroupClass; ?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo  Text::_('EB_META_KEYWORDS'); ?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<textarea rows="5" cols="30" class="input-lage" name="meta_keywords_<?php echo $sef; ?>"><?php echo $this->item->{'meta_keywords_'.$sef}; ?></textarea>
		</div>
	</div>
	<div class="<?php echo $controlGroupClass; ?>">
		<div class="<?php echo $controlLabelClass; ?>">
			<?php echo  Text::_('EB_META_DESCRIPTION'); ?>
		</div>
		<div class="<?php echo $controlsClass; ?>">
			<textarea rows="5" cols="30" class="input-lage" name="meta_description_<?php echo $sef; ?>"><?php echo $this->item->{'meta_description_'.$sef}; ?></textarea>
		</div>
	</div>
	<?php
	echo HTMLHelper::_($tabApiPrefix . 'endTab');
}

echo HTMLHelper::_($tabApiPrefix . 'endTabSet');
echo HTMLHelper::_($tabApiPrefix . 'endTab');

