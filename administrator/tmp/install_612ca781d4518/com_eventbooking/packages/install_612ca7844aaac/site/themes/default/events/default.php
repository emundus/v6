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
use Joomla\CMS\Router\Route;

HTMLHelper::_('formbehavior.chosen', 'select');

$isJoomla4       = EventbookingHelper::isJoomla4();
$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
$hiddenPhone     = $bootstrapHelper->getClassMapping('hidden-phone');
?>
<h1 class="eb-page-heading"><?php echo $this->escape(Text::_('EB_USER_EVENTS')); ?></h1>
<div id="eb-events-manage-page" class="eb-container<?php if ($isJoomla4) echo ' eb-joomla4-container'; ?>">
    <div class="btn-toolbar" id="btn-toolbar">
		<?php echo JToolbar::getInstance('toolbar')->render(); ?>
    </div>
    <form method="post" name="adminForm" id="adminForm" action="<?php echo Route::_('index.php?option=com_eventbooking&view=events&Itemid='.$this->Itemid); ; ?>">
        <div class="filters btn-toolbar clearfix mt-2 mb-2">
            <?php echo $this->loadTemplate('search_bar'); ?>
        </div>
        <?php
        if(count($this->items))
        {
        ?>
            <table class="<?php echo $bootstrapHelper->getClassMapping('table table-striped table-bordered'); ?> table-considered">
                <thead>
                    <tr>
                        <th width="20">
                            <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
                        </th>
                        <th>
	                        <?php echo HTMLHelper::_('grid.sort',  Text::_('EB_TITLE'), 'tbl.title', $this->state->filter_order_Dir, $this->state->filter_order); ?>
                        </th>
                        <th width="18%" class="<?php echo $hiddenPhone; ?>">
                            <?php echo Text::_('EB_CATEGORY'); ?>
                        </th>
                        <th class="center" width="10%">
                            <?php echo HTMLHelper::_('grid.sort',  Text::_('EB_EVENT_DATE'), 'tbl.event_date', $this->state->filter_order_Dir, $this->state->filter_order); ?>
                        </th>
                        <th width="7%" class="<?php echo $hiddenPhone; ?>">
                            <?php echo Text::_('EB_NUMBER_REGISTRANTS'); ?>
                        </th>
                        <th width="5%" nowrap="nowrap">
	                        <?php echo HTMLHelper::_('grid.sort',  Text::_('EB_STATUS'), 'tbl.published', $this->state->filter_order_Dir, $this->state->filter_order); ?>
                        </th>
                        <th width="1%" nowrap="nowrap" class="<?php echo $hiddenPhone; ?>">
                            <?php echo HTMLHelper::_('grid.sort',  Text::_('EB_ID'), 'tbl.id', $this->state->filter_order_Dir, $this->state->filter_order); ?>
                        </th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="7">
                            <div class="pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
                        </td>
                    </tr>
                </tfoot>
                <tbody>
                    <?php
                    $k      = 0;
                    $Itemid = EventbookingHelper::getItemid();
                    list($itemId, $layout) = EventbookingHelper::getAddEditEventFormLayout();

                    for ($i=0, $n=count( $this->items ); $i < $n; $i++)
                    {
	                    $row       = $this->items[$i];
	                    $link      = Route::_(EventbookingHelperRoute::getEventRoute($row->id, $row->main_category_id, $Itemid));
	                    $editLink  = Route::_('index.php?option=com_eventbooking&view=event&id=' . $row->id . '&layout=' . $layout . '&Itemid=' . $this->Itemid, false);
	                    $checked   = HTMLHelper::_('grid.id', $i, $row->id);

	                    if (EventbookingHelperAcl::canChangeEventStatus($row->id))
                        {
	                        $published = HTMLHelper::_('jgrid.published', $row->published, $i);
                        }
	                    else
	                    {
                            $published = $row->published ? Text::_('EB_PUBLISHED') : Text::_('EB_PENDING');
	                    }

	                    $canEditEvent = EventbookingHelperAcl::checkEditEvent($row->id);
                        ?>
                        <tr class="<?php echo "row$k"; ?>">
                            <td>
                                <?php echo $checked; ?>
                            </td>
                            <td>
                                <a href="<?php echo $canEditEvent ? $editLink: $link; ?>" title="<?php echo Text::_('EB_EDIT_EVENT'); ?>">
                                    <?php echo $row->title ; ?>
                                </a>
                                <?php
                                    if ($canEditEvent)
                                    {
                                    ?>
                                        <a href="<?php echo $link;?>" target="_blank" title="<?php echo Text::_('EB_VIEW_EVENT'); ?>" style="padding-left: 10px;"><i class="fa fa-2x fa-external-link"></i></a>
                                    <?php
                                    }
                                ?>
                            </td>
                            <td class="<?php echo $hiddenPhone; ?>">
                                <?php echo $row->category_name ; ?>
                            </td>
                            <td class="center">
                                <?php echo HTMLHelper::_('date', $row->event_date, $this->config->date_format, null); ?>
                            </td>
                            <td class="center <?php echo $hiddenPhone; ?>">
                                <?php echo (int) $row->total_registrants ; ?>
                            </td>
                            <td class="center">
                                <?php
                                    if ($row->published == 2)
                                    {
                                        echo Text::_('EB_CANCELLED');
                                    }
                                    else
                                    {
                                        echo $published;
                                    }
                                ?>
                            </td>
                            <td class="center <?php echo $hiddenPhone; ?>">
                                <?php echo $row->id; ?>
                            </td>
                        </tr>
                        <?php
                        $k = 1 - $k;
                    }
                    ?>
                </tbody>
            </table>
        <?php
        }
        ?>
        <input type="hidden" name="task" id="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="filter_order" value="<?php echo $this->state->filter_order; ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->filter_order_Dir; ?>" />

        <?php echo HTMLHelper::_( 'form.token' ); ?>
    </form>
</div>