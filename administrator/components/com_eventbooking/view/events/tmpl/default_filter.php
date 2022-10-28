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
?>
<div id="filter-bar" class="btn-toolbar js-stools-container-filters-visible clearfix">
	<div class="filter-search btn-group pull-left">
		<label for="filter_search" class="element-invisible"><?php echo Text::_('EB_FILTER_SEARCH_EVENTS_DESC');?></label>
		<input type="text" name="filter_search" inputmode="search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="hasTooltip form-control" title="<?php echo HTMLHelper::tooltipText('EB_SEARCH_EVENTS_DESC'); ?>" />
	</div>
	<div class="btn-group pull-left">
		<button type="submit" class="btn  btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
		<button type="button" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
	</div>
	<div class="btn-group pull-right">
		<?php
			echo EventbookingHelperHtml::getChoicesJsSelect($this->lists['filter_category_id'], Text::_('EB_TYPE_OR_SELECT_ONE_CATEGORY'));
			echo EventbookingHelperHtml::getChoicesJsSelect($this->lists['filter_location_id'], Text::_('EB_TYPE_OR_SELECT_ONE_LOCATION'));
			echo $this->lists['filter_state'];
		?>
	</div>
	<div class="btn-group pull-right">
		<?php
		    echo str_replace('input-medium ', '', $this->lists['filter_access']);
		    echo str_replace('input-medium ', '', $this->lists['filter_events']);
			echo $this->pagination->getLimitBox();
		?>
	</div>
</div>
