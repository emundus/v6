<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
$showCity = JModuleHelper::isEnabled('mod_eb_cities');
$showState = JModuleHelper::isEnabled('mod_eb_cities');
$cols = 4;
?>
<h1 class="eb_title"><?php echo $this->escape(Text::_('EB_LOCATIONS_MANAGEMENT')); ?>
	<span class="add_location_link" style="float: right; font-size:14px;"><a
			href="<?php echo Route::_('index.php?option=com_eventbooking&view=location&layout=form&Itemid=' . $this->Itemid); ?>"><i class="icon-plus"></i></i><?php echo Text::_('EB_SUBMIT_LOCATION'); ?></a></span>
</h1>
<form method="post" name="adminForm" id="adminForm"
      action="<?php echo Route::_('index.php?option=com_eventbooking&view=locations&Itemid=' . $this->Itemid);; ?>">
	<table class="<?php echo $bootstrapHelper->getClassMapping('table table-striped table-bordered') ?> table-condensed" style="margin-top: 10px;">
		<thead>
		<tr>
			<th>
				<?php echo Text::_('EB_NAME'); ?>
			</th>
			<th>
				<?php echo Text::_('EB_ADDRESS'); ?>
			</th>
            <?php
                if ($showCity)
                {
                    $cols++;
                ?>
                    <th>
		                <?php echo Text::_('EB_CITY'); ?>
                    </th>
                <?php
                }

                if ($showState)
                {
                    $cols++;
                ?>
                    <th>
		                <?php echo Text::_('EB_STATE'); ?>
                    </th>
                <?php
                }
            ?>
			<th>
				<?php echo Text::_('EB_LATITUDE'); ?>
			</th>
			<th>
				<?php echo Text::_('EB_LONGITUDE'); ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php
		$k = 0;
		for ($i = 0, $n = count($this->items); $i < $n; $i++)
		{
			$item = $this->items[$i];
			$url  = Route::_('index.php?option=com_eventbooking&view=location&layout=form&id=' . $item->id . '&Itemid=' . $this->Itemid);
			?>
			<tr>
				<td>
					<a href="<?php echo $url; ?>" title="<?php echo $item->name; ?>">
						<?php echo $this->escape($item->name); ?>
					</a>
				</td>
				<td>
					<?php echo $this->escape($item->address); ?>
				</td>
                <?php
                    if ($showCity)
                    {
                    ?>
                        <td>
		                    <?php echo $this->escape($item->city); ?>
                        </td>
                    <?php
                    }

                    if ($showState)
                    {
                    ?>
                        <td>
		                    <?php echo $this->escape($item->state); ?>
                        </td>
                    <?php
                    }
                ?>
				<td>
					<?php echo $item->lat; ?>
				</td>
				<td>
					<?php echo $item->long; ?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		if (count($this->items) == 0)
		{
			?>
			<tr>
				<td colspan="<?php echo $cols; ?>" style="text-align: center;">
					<div class="info"><?php echo Text::_('EB_NO_LOCATION_RECORDS');?></div>
				</td>
			</tr>
		<?php
		}
		?>
		</tbody>
		<?php
		if ($this->pagination->total > $this->pagination->limit)
		{
		?>
			<tfoot>
			<tr>
				<td colspan="<?php echo $cols; ?>">
					<div class="pagination">
						<?php echo $this->pagination->getListFooter(); ?>
					</div>
				</td>
			</tr>
			</tfoot>
		<?php
		}
		?>
	</table>
</form>
