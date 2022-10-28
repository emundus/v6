<?php
/**
 * @package        Joomla
 * @subpackage     Event Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 - 2021 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$isJoomla4       = EventbookingHelper::isJoomla4();
$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
?>
<div class="filter-search btn-group pull-left">
    <label for="filter_search" class="element-invisible"><?php echo Text::_('EB_FILTER_SEARCH_REGISTRATION_RECORDS_DESC');?></label>
    <input type="text" name="filter_search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->lists['search']); ?>" class="hasTooltip" title="<?php echo HTMLHelper::tooltipText('EB_SEARCH_REGISTRATION_RECORDS_DESC'); ?>" />
</div>
<div class="btn-group pull-left">
    <button type="submit" class="btn<?php if ($isJoomla4) echo ' btn-primary'; ?> hasTooltip"  title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="<?php echo $bootstrapHelper->getClassMapping('icon-search'); ?>"></span></button>
    <button type="button" class="btn<?php if ($isJoomla4) echo ' btn-primary'; ?> hasTooltip"  title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="<?php echo $bootstrapHelper->getClassMapping('icon-remove'); ?>"></span></button>
</div>
<div class="btn-group pull-left hidden-phone">
	<?php echo $this->lists['filter_event_id'] ; ?>
</div>
