<?php
/**
 * Layout: List filters
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.4
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$d             = $displayData;
$underHeadings = $d->filterMode === 3 || $d->filterMode === 4;
$clearFiltersClass = $d->gotOptionalFilters ? "clearFilters hasFilters" : "clearFilters";

$style = $d->toggleFilters ? 'style="display:none"' : ''; ?>
<div class="fabrikFilterContainer" <?php echo $style ?>>
    <div class="fabrikFiltersBlock">
	<?php
	if (!$underHeadings) :
	?>
    <div class="row-fluid">
		<?php
		if ($d->filterCols === 1) :
		?>
        <div class="span6">
			<?php
			endif;
			?>
            <div class="filtertable table table-striped">
                <div class="em-flex-row em-flex-space-between em-mb-24 em-filter-intro">
                    <h4><?php echo FText::_('COM_FABRIK_FILTER') ?></h4>
	                <?php if ($d->showClearFilters) : ?>
                        <a class="<?php echo $clearFiltersClass; ?>" href="#">
                            <span class="material-icons-outlined">filter_alt_off</span>
                        </a>
	                <?php endif ?>
                </div>

                <div class="em-filter-body">

	            <?php if (array_key_exists('all', $d->filters) || $d->filter_action != 'onchange') {
		            ?>
                    <div class="em-mb-12">
			            <?php if (array_key_exists('all', $d->filters)) { ?>
                            <p class="em-mb-4 em-text-neutral-600"><?php echo FText::_('COM_FABRIK_ADVANCED_SEARCH') ?></p>
				            <?php echo $d->filters['all']->element;
			            };
			            ?>
                    </div>
		            <?php
	            }
	            ?>

	            <?php
	            $c = 0;
	            // $$$ hugh - filterCols stuff isn't operation yet, WiP, just needed to get it committed
	            if ($d->filterCols > 1) :
	            ?>
                <div>
                    <div>
                        <div class="filtertable_horiz">
				            <?php
				            endif;
				            $filter_count = array_key_exists('all', $d->filters) ? count($d->filters) - 1 : count($d->filters);
				            $colHeight    = ceil($filter_count / $d->filterCols);
				            foreach ($d->filters as $key => $filter) :
				            if ($d->filterCols > 1 && $c >= $colHeight && $c % $colHeight === 0) :
				            ?>
                        </div>
                        <div class="filtertable_horiz">
				            <?php
				            endif;
				            if ($key !== 'all') :
					            $c++;
					            $required = $filter->required == 1 ? ' notempty' : ''; ?>
                                <div data-filter-row="<?php echo $key; ?>"
                                    class="em-mb-16 fabrik_row oddRow<?php echo ($c % 2) . $required; ?>">
                                    <p class="em-mb-4 em-text-neutral-600"><?php echo $filter->label; ?></p>
                                    <p><?php echo $filter->element; ?></p>
                                </div>
				            <?php
				            endif;
				            endforeach;
				            if ($d->filterCols > 1) :
				            ?>
                        </div>
                    </div>
                </div>
            <?php
            endif;
            ?>
                </div>
            </div>
			<?php
			endif;
			?>
			<?php
			if (!($underHeadings)) :
			?>
			<?php
			if ($d->filterCols === 1) :
			?>
        </div>
	<?php
	endif;
	?>
    </div>
<?php endif; ?>
    </div>

	<?php
	if ($d->filter_action != 'onchange') :
		?>
        <div class="em-mt-16" id="fabrikFiltersButtonSubmit">
            <div>
                <input type="button" class="btn-info btn fabrik_filter_submit button"
                       style="width: -webkit-fill-available;"
                       value="<?php echo FText::_('COM_FABRIK_GO'); ?>" name="filter">
            </div>
        </div>
	<?php
	endif; ?>
</div>


<style>

    @media screen and (max-width: 768px) {
        .em-filter-body {
            display: none;
        }
    }

</style>

<script>
    if(screen.width < 768) {

        const filterIntro = document.querySelector('.em-filter-intro');
        const filterBody = document.querySelector('.em-filter-body');

        filterIntro.addEventListener('click', function(){
            if (filterBody.style.display === "none") {
                filterBody.style.display = "block";
            } else {

                filterBody.style.display = "none";
            }
        });
    }
</script>
