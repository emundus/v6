<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die ;

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

echo HTMLHelper::_($tabApiPrefix . 'addTab', 'configuration', 'invoice-translation', Text::_('EB_INVOICE_TRANSLATION', true));
echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'invoice-translation', array('active' => 'invoice-translation-'.$this->languages[0]->sef));

foreach ($this->languages as $language)
{
	$sef = $language->sef;
	echo HTMLHelper::_($tabApiPrefix . 'addTab', 'invoice-translation', 'invoice-translation-' . $sef, $language->title . ' <img src="' . $rootUri . '/media/mod_languages/images/' . $language->image . '.gif" />');
	?>
	<div class="control-group">
		<div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('invoice_format', Text::_('EB_INVOICE_FORMAT'), Text::_('EB_INVOICE_FORMAT_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo $editor->display('invoice_format_' . $sef, $config->{'invoice_format_' . $sef}, '100%', '550', '75', '8');?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('invoice_format_cart', Text::_('EB_INVOICE_FORMAT_CART'), Text::_('EB_INVOICE_FORMAT_CART_EXPLAIN')); ?>
		</div>
		<div class="controls">
			<?php echo $editor->display('invoice_format_cart_' . $sef, $config->{'invoice_format_cart_' . $sef}, '100%', '550', '75', '8');?>
		</div>
	</div>
    <div class="control-group">
        <div class="control-label">
	        <?php echo EventbookingHelperHtml::getFieldLabel('default_ticket_layout_' . $sef, Text::_('EB_DEFAULT_TICKET_LAYOUT'), Text::_('EB_DEFAULT_TICKET_LAYOUT_EXPLAIN')); ?>
        </div>
        <div class="controls">
	        <?php echo $editor->display('default_ticket_layout_' . $sef, $config->{'default_ticket_layout_' . $sef}, '100%', '550', '75', '8'); ?>
        </div>
    </div>
	<?php
	echo HTMLHelper::_($tabApiPrefix . 'endTab');
}

echo HTMLHelper::_($tabApiPrefix . 'endTabSet');
echo HTMLHelper::_($tabApiPrefix . 'endTab');