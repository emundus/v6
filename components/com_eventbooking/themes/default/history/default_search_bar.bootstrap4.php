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

?>
<div class="filter-search btn-group pull-left">
	<div class="input-group">
		<label for="filter_search" class="sr-only"><?php echo Text::_('EB_FILTER_SEARCH_REGISTRATION_RECORDS_DESC');?></label>
		<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="hasTooltip form-control" title="<?php echo HTMLHelper::tooltipText('EB_SEARCH_REGISTRATION_RECORDS_DESC'); ?>" />
		<span class="input-group-append">
            <button type="submit" class="btn hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="fa fa-search"></span></button>
            <button type="button" class="btn hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="fa fa-remove"></span></button>
        </span>
	</div>
</div>
<div class="btn-group pull-left ml-2">
	<?php echo $this->lists['filter_event_id']; ?>
</div>
