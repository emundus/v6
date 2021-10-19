<?php
/**
 * @package        Joomla
 * @subpackage     Event Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$bootstrapHelper   = EventbookingHelperBootstrap::getInstance();
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');
?>					
<form method="post" name="eb_search_form" id="eb_search_form" action="<?php echo Route::_('index.php?option=com_eventbooking&task=search&&Itemid='.$itemId);  ?>" class="form">
    <div class="<?php echo $controlGroupClass; ?>">
        <div class="<?php echo $controlsClass; ?>">
            <input name="search" id="search_eb_box" maxlength="50"  class="inputbox<?php echo $bootstrapHelper->getFrameworkClass('form-control', 1); ?>" type="text" size="20" value="<?php echo $text; ?>" placeholder="<?php echo Text::_('EB_SEARCH_WORD'); ?>" />
        </div>
    </div>
    <?php
    if ($showFromDate)
    {
    ?>
        <div class="<?php echo $controlGroupClass; ?>">
            <div class="<?php echo $controlLabelClass; ?>"><?php echo Text::_('EB_SEARCH_FROM_DATE'); ?></div>
            <div class="<?php echo $controlsClass; ?>"><?php echo HTMLHelper::_('calendar', $fromDate, 'filter_from_date', 'filter_from_date', $datePickerFormat, ['class' => 'input-medium' . $bootstrapHelper->getFrameworkClass('form-control')]); ?></div>
        </div>
    <?php
    }

    if ($showToDate)
    {
    ?>
        <div class="<?php echo $controlGroupClass; ?>">
            <div class="<?php echo $controlLabelClass; ?>"><?php echo Text::_('EB_SEARCH_TO_DATE'); ?></div>
            <div class="<?php echo $controlsClass; ?>"><?php echo HTMLHelper::_('calendar', $toDate, 'filter_to_date', 'filter_to_date', $datePickerFormat, ['class' => 'input-medium' . $bootstrapHelper->getFrameworkClass('form-control')]); ?></div>
        </div>
    <?php
    }

    if ($showCategory && !$presetCategoryId)
    {
    ?>
        <div class="<?php echo $controlGroupClass; ?>">
            <div class="<?php echo $controlLabelClass; ?>"><?php echo Text::_('EB_CATEGORY'); ?></div>
            <div class="<?php echo $controlsClass; ?>"><?php echo $lists['category_id']; ?></div>
        </div>
    <?php
    }

    if ($showLocation)
    {
	?>
        <div class="<?php echo $controlGroupClass; ?>">
            <div class="<?php echo $controlLabelClass; ?>"><?php echo Text::_('EB_LOCATION'); ?></div>
            <div class="<?php echo $controlsClass; ?>"><?php echo $lists['location_id']; ?></div>
        </div>
	 <?php
    }

    if ($enableRadiusSearch)
    {
    ?>
        <div class="<?php echo $controlGroupClass; ?>">
            <div class="<?php echo $controlsClass; ?>">
                <input type="text" name="filter_address"<?php echo $bootstrapHelper->getFrameworkClass('form-control', 3); ?> placeholder="<?php echo Text::_('EB_ADDRESS_CITY_POSTCODE'); ?>" value="<?php echo htmlspecialchars($filterAddress, ENT_COMPAT, 'UTF-8'); ?>">
            </div>
        </div>
        <div class="<?php echo $controlGroupClass; ?>">
            <div class="<?php echo $controlsClass; ?>">
                <div class="<?php echo $controlsClass; ?>"><?php echo $lists['filter_distance']; ?></div>
            </div>
        </div>
    <?php
    }
    ?>
    <div class="form-actions">
        <input type="submit" class="btn btn-primary button search_button" value="<?php echo Text::_('EB_SEARCH'); ?>" />
    </div>
	<input type="hidden" name="layout" value="<?php echo $layout; ?>" />
    <?php
        if ($presetCategoryId)
        {
        ?>
            <input type="hidden" name="category_id" value="<?php echo $presetCategoryId; ?>" />
        <?php
        }
    ?>
</form>
