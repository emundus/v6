<?php
defined('JPATH_BASE') or die;

$d    = $displayData;
$inputDataAttribs = array('data-filter-name="' . $d->elementName . '"');
?>
<div class="fabrikListFilterCheckbox">
<?php
echo implode("\n",
				FabrikHelperHTML::grid($d->values, $d->labels, $d->default, $d->name, 'checkbox', false, 1, 
					['input' => ['fabrik_filter', 'form-check-input']], /* Classes */
					false, array(), $inputDataAttribs)
						);
?>
</div>