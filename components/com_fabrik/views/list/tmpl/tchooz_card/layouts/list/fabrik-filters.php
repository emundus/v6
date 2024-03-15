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

use Joomla\CMS\Language\Text;

$d             = $displayData;
$underHeadings = $d->filterMode === 3 || $d->filterMode === 4;
$clearFiltersClass = $d->gotOptionalFilters ? "clearFilters hasFilters" : "clearFilters";

$style = $d->toggleFilters ? 'style="display:none"' : ''; ?>
<div class="fabrikFilterContainer catalogue_filters_container" <?php echo $style ?>>
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
                    <h4><?php echo Text::_('COM_FABRIK_FILTER') ?></h4>
	                <?php if ($d->showClearFilters) : ?>
                        <button type="button" class="<?php echo $clearFiltersClass; ?>" href="#">
                            <span class="material-icons-outlined">filter_alt_off</span>
                        </button>
	                <?php endif ?>
                </div>

                <div class="em-filter-body">

	            <?php if (array_key_exists('all', $d->filters) || $d->filter_action != 'onchange') {
		            ?>
                    <div class="em-mb-12">
			            <?php if (array_key_exists('all', $d->filters)) { ?>
                            <p class="em-mb-4 em-text-neutral-800"><?php echo Text::_('COM_FABRIK_ADVANCED_SEARCH') ?></p>
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
                                    <p class="em-mb-4 em-text-neutral-800"><?php echo $filter->label; ?></p>
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
                       value="<?php echo Text::_('COM_FABRIK_GO'); ?>" name="filter">
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

        a.clearFilters {
            display: none;
        }

        .em-filter-intro.em-filter-intro__close h4::after {
            transform: rotate(-45deg);
            -webkit-transform: rotate(-45deg);
        }

        .em-filter-intro h4::after {
            transform: rotate(45deg);
            -webkit-transform: rotate(45deg);
            border: solid #3D3D3D;
            border-width: 0 2px 2px 0;
            display: inline-block;
            padding: 3px;
            content: '';
            left: 212px;
            position: relative;
            transition: 0.25s transform ease;
            height: 12px;
            width: 12px;
        }
    }

</style>

<script>
    if(screen.width < 768) {

        const filterIntro = document.querySelector('.em-filter-intro');
        const filterBody = document.querySelector('.em-filter-body');
        filterIntro.classList.add('em-filter-intro__close');

        filterIntro.addEventListener('click', function(){
            if (filterBody.style.display === "none") {
                filterBody.style.display = "block";
                filterIntro.classList.remove('em-filter-intro__close');
            } else {

                filterBody.style.display = "none";
                filterIntro.classList.add('em-filter-intro__close');
            }
        });

    }
</script>
