<?php
/**
 * Layout: List filters
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2022 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.4
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Html;
use Joomla\CMS\Language\Text;
use Fabrik\Helpers\ArrayHelper;

$d = $displayData;

$showClearFilters = false;
foreach ($d->filters as $key => $filter) :
	if ($filter->displayValue !== '') :
		$showClearFilters = true;
	endif;
endforeach;

?>
  <script>
  $( function() {
    jQuery( ".modal-content.draggable" ).draggable();
  } );
  </script>
<div data-modal-state-container style="display:<?php echo $showClearFilters ? '' : 'none'; ?>">
	<?php echo Text::_('COM_FABRIK_FILTERS_ACTIVE'); ?>
	<span data-modal-state-display>
	<?php $layout = Html::getLayout('list.fabrik-filters-modal-state-label');

	foreach ($d->filters as $key => $filter) :
		if ($filter->displayValue !== '') :

			$layoutData = (object) array(
				'label' => $filter->label,
				'displayValue' => $filter->displayValue,
				'key' => $key
			);
			echo $layout->render($layoutData);
		endif;
	endforeach;
	?>
	</span>
</div>
<div class="fabrikFilterContainer modal fade" id="filter_modal">

	<div class="modal-dialog modal-lg">
		<div class="modal-content draggable">

			<div class="modal-header ">
					<h5 class="modal-title"><?php echo Html::icon('icon-filter', Text::_('COM_FABRIK_FILTER')); ?></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			
			<div class="modal-body p-3">

					<div class="row">
					<?php /*Filter block as in fabrik-filters-bootstrap*/
						$chunkedFilters = array();
						$span = floor(12 / $d->filterCols);
						foreach ($d->filters as $key => $filter) :
							if ($key !== 'all') :
								$required = $filter->required == 1 ? ' notempty' : '';
								if ($d->filterCols === 1) :
									$chunkedFilters[] = <<<EOT
								<div class="row mt-3" data-filter-row="$key">
									<div class="col-sm-2 "><label for={$filter->id} >{$filter->label}</label></div>
									<div class="col-sm-10">{$filter->element}</div>
								</div>
			EOT;
								else :
									$chunkedFilters[] = <<<EOT
								<div class="row mt-3" data-filter-row="$key">
									<div class="col-sm-12"><label for={$filter->id} >{$filter->label}</label></div>
									<div class="col-sm-12">{$filter->element}</div>
								</div>
			EOT;
								endif;
							endif;
						endforeach;

						// last arg controls whether rows and cols are flipped (pivot)
						$chunkedFilters = ArrayHelper::chunk($chunkedFilters, $d->filterCols, true);

						foreach ($chunkedFilters as $chunk) :
							foreach ($chunk as $filter) :
								?>
								<div class="col-sm-<?php echo $span; ?>">
								<?php
									echo $filter;
								?>
								</div>
								<?php
							endforeach;
						endforeach;
					?>
					</div>
			</div>
			<div class="modal-footer justify-content-between">
				<?php
				if ($d->filter_action != 'onchange') :
					?>
					<input type="button" data-bs-dismiss="modal" class="btn btn-primary fabrik_filter_submit"
						value="<?php echo Text::_('COM_FABRIK_GO'); ?>" name="filter">
					<?php
				endif;
				?>
				<?php
				if ($d->showClearFilters) :
					$clearFiltersClass = $d->gotOptionalFilters ? "btn btn-outline-secondary clearFilters hasFilters" : "btn btn-outline-secondary clearFilters";
				?>
					<input type="button" class="<?php echo $clearFiltersClass; ?>"
						value="<?php echo Text::_('COM_FABRIK_CLEAR'); ?>" />
				<?php endif ?>
			</div>
		</div>

	</div>

</div>
