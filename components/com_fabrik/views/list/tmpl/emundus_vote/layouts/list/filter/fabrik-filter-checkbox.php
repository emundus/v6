<?php
defined('JPATH_BASE') or die;

$d    = $displayData;
$inputDataAttribs = array('data-filter-name="' . $d->elementName . '"');
$clearFiltersClass = $d->gotOptionalFilters ? "clearFilters hasFilters" : "clearFilters";
?>
<div class="fabrikListFilterCheckbox">
    <a class="<?php echo $clearFiltersClass; ?> em-flex-row" href="#">
		<?php echo FText::_('COM_FABRIK_FILTER_PLEASE_SELECT'); ?>
    </a>
    <?php
echo implode("\n",
				FabrikHelperHTML::grid($d->values, $d->labels, $d->default, $d->name,
	'checkbox', false, 1, array('input' => array('fabrik_filter')), false, array(), $inputDataAttribs)
		);
?>
</div>