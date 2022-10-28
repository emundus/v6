<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/* @var EventbookingViewConfigurationHtml $this */
?>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('activate_eu_tax_rules', Text::_('EB_ACTIVATE_EU_TAX_RULES'), Text::_('EB_ACTIVATE_EU_TAX_RULES_EXPLAIN')); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('activate_eu_tax_rules', $config->get('activate_eu_tax_rules')); ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('eu_vat_number_field', Text::_('EB_VAT_NUMBER_FIELD'), Text::_('EB_VAT_NUMBER_FIELD_EXPLAIN')); ?>
    </div>
    <div class="controls">
		<?php echo $this->lists['eu_vat_number_field']; ?>
    </div>
</div>
<div class="control-group">
    <div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('hide_vat_field_for_home_country', Text::_('EB_HIDE_VAT_NUMBER_FIELD_FOR_HOME_COUNTRY'), Text::_('EB_HIDE_VAT_NUMBER_FIELD_FOR_HOME_COUNTRY_EXPLAIN')); ?>
    </div>
    <div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('hide_vat_field_for_home_country', $config->get('hide_vat_field_for_home_country')); ?>
    </div>
</div>