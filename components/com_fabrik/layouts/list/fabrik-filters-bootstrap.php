<?php
/**
 * Layout: List filters bootstrap
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2022  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.4
 * called if filter columns >1
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\ArrayHelper;
use Joomla\CMS\Language\Text;

$d             = $displayData;
$underHeadings = $d->filterMode === 3 || $d->filterMode === 4;
$clearFiltersClass = $d->gotOptionalFilters ? "clearFilters hasFilters" : "clearFilters";
$style = $d->toggleFilters ? 'style="display:none"' : '';

?>
<?php
if (!$underHeadings) :
?>
<div class=" fabrikFilterContainer p-3 bg-light  mb-3" <?php echo $style ?>>

        <div class="row d-flex justify-content-between">
            <div class="col-auto fabrik___heading"><?php echo Text::_('COM_FABRIK_SEARCH'); ?>:</div>
            <div class="col-auto fabrik___heading" >
                <?php if ($d->showClearFilters) : ?>
                    <a class="<?php echo $clearFiltersClass; ?>" href="#">
                        <?php echo FabrikHelperHTML::icon('icon-undo', Text::_('COM_FABRIK_CLEAR')); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
        <?php
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
            <?php

		if ($d->filter_action != 'onchange') :
        ?>
        <div class="row d-flex justify-content-end">
            
                <input type="button" class="col-auto  btn-info btn fabrik_filter_submit button"
                        value="<?php echo Text::_('COM_FABRIK_GO'); ?>" name="filter">
          
        </div>
        <?php
	    endif;
		?>
	</div>
	<?php
    endif;
    ?>