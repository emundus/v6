<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die ;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$isJoomla4 = EventbookingHelper::isJoomla4();

HTMLHelper::_('bootstrap.tooltip');

if (!$isJoomla4)
{
	HTMLHelper::_('formbehavior.chosen', 'select');
}

$user      = Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->state->filter_order;
$listDirn  = $this->state->filter_order_Dir;
$saveOrder = $listOrder == 'tbl.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_eventbooking&task=field.save_order_ajax';

	if ($isJoomla4)
	{
		HTMLHelper::_('draggablelist.draggable');
	}
	else
	{
		HTMLHelper::_('sortablelist.sortable', 'fieldList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
	}
}

$customOptions = array(
	'filtersHidden'       => true,
	'defaultLimit'        => Factory::getApplication()->get('list_limit', 20),
	'searchFieldSelector' => '#filter_search',
	'orderFieldSelector'  => '#filter_full_ordering'
);
HTMLHelper::_('searchtools.form', '#adminForm', $customOptions);
?>
<form action="index.php?option=com_eventbooking&view=fields" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container"<?php if ($isJoomla4) echo ' class="eb-joomla4-container"'; ?>>
        <?php
        if ($isJoomla4)
        {
	        echo $this->loadTemplate('filter');
        }
        else
        {
        ?>
            <div id="filter-bar" class="btn-toolbar js-stools">
                <div class="filter-search btn-group pull-left">
                    <label for="filter_search" class="element-invisible"><?php echo Text::_('EB_FILTER_SEARCH_FIELDS_DESC');?></label>
                    <input type="text" name="filter_search" id="filter_search" inputmode="search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="hasTooltip input-medium form-control" title="<?php echo HTMLHelper::tooltipText('EB_SEARCH_FIELDS_DESC'); ?>" />
                </div>
                <div class="btn-group pull-left">
                    <button type="submit" class="btn<?php if ($isJoomla4) echo ' btn-primary'; ?> hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
                    <button type="button" class="btn<?php if ($isJoomla4) echo ' btn-primary'; ?> hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
                </div>
                <div class="btn-group pull-right">
			        <?php
			        if ($this->config->custom_field_by_category)
			        {
				        echo $this->lists['filter_category_id'];
			        }
			        else
			        {
				        echo $this->lists['filter_event_id'];
			        }

			        echo $this->lists['filter_show_core_fields'];
			        echo $this->lists['filter_fieldtype'];
			        echo $this->lists['filter_fee_field'];
			        echo $this->lists['filter_quantity_field'];
			        echo $this->lists['filter_state'];
			        echo $this->pagination->getLimitBox();
			        ?>
                </div>
            </div>
        <?php
        }
        ?>
		<div class="clearfix"></div>
		<table class="adminlist table table-striped" id="fieldList">
			<thead>
			<tr>
				<th width="1%" class="nowrap center hidden-phone">
					<?php echo HTMLHelper::_('searchtools.sort', '', 'tbl.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
				</th>
				<th width="2%" class="center">
					<?php echo HTMLHelper::_('grid.checkall'); ?>
				</th>
				<th class="title">
					<?php echo HTMLHelper::_('searchtools.sort',  Text::_('EB_NAME'), 'tbl.name', $this->state->filter_order_Dir, $this->state->filter_order); ?>
				</th>
				<th class="title">
					<?php echo HTMLHelper::_('searchtools.sort',  Text::_('EB_TITLE'), 'tbl.title', $this->state->filter_order_Dir, $this->state->filter_order); ?>
				</th>
				<th class="title">
					<?php echo HTMLHelper::_('searchtools.sort',  Text::_('EB_FIELD_TYPE'), 'tbl.field_type', $this->state->filter_order_Dir, $this->state->filter_order); ?>
				</th>
				<th class="title center">
					<?php echo HTMLHelper::_('searchtools.sort',  Text::_('EB_REQUIRE'), 'tbl.required', $this->state->filter_order_Dir, $this->state->filter_order); ?>
				</th>
				<th class="title center">
					<?php echo HTMLHelper::_('searchtools.sort',  Text::_('EB_PUBLISHED'), 'tbl.published', $this->state->filter_order_Dir, $this->state->filter_order); ?>
				</th>
				<th width="1%" class="center" nowrap="nowrap">
					<?php echo HTMLHelper::_('searchtools.sort',  Text::_('EB_ID'), 'tbl.id', $this->state->filter_order_Dir, $this->state->filter_order); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="8">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
			</tfoot>
            <tbody <?php if ($saveOrder) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($this->state->filter_order_Dir); ?>" <?php endif; ?>>
			<?php
			$k = 0;
			$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
			$iconPublish = $bootstrapHelper->getClassMapping('icon-publish');
			$iconUnPublish = $bootstrapHelper->getClassMapping('icon-unpublish');
			for ($i=0, $n=count( $this->items ); $i < $n; $i++)
			{
				$row       = $this->items[$i];
				$link      = Route::_('index.php?option=com_eventbooking&view=field&id=' . $row->id);
				$checked   = HTMLHelper::_('grid.id', $i, $row->id);
				$published = HTMLHelper::_('jgrid.published', $row->published, $i);
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td class="order nowrap center hidden-phone">
						<?php
						$iconClass = '';
						if (!$saveOrder)
						{
							$iconClass = ' inactive tip-top hasTooltip"';
						}
						?>
						<span class="sortable-handler<?php echo $iconClass ?>">
						<i class="icon-menu"></i>
						</span>
						<?php if ($saveOrder) : ?>
							<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $row->ordering ?>" class="width-20 text-area-order "/>
						<?php endif; ?>
					</td>
					<td class="center">
						<?php echo $checked; ?>
					</td>
					<td>
						<a href="<?php echo $link; ?>">
							<?php echo $row->name; ?>
						</a>
					</td>
					<td>
						<a href="<?php echo $link; ?>">
							<?php echo $row->title; ?>
						</a>
					</td>
					<td>
						<?php
							echo $row->fieldtype;
						?>
					</td>
					<td class="center">
                        <a class="tbody-icon"><span class="<?php echo $row->required ? $iconPublish : $iconUnPublish; ?>"></span></a>
					</td>
					<td class="center">
						<?php echo $published ; ?>
					</td>
					<td class="center">
						<?php echo $row->id; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
		</table>
	</div>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->state->filter_order; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->filter_order_Dir; ?>" />
	<input type="hidden" id="filter_full_ordering" name="filter_full_ordering" value="" />
	<?php echo HTMLHelper::_( 'form.token' ); ?>
</form>