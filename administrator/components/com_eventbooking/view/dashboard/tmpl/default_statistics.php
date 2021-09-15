<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */


defined( '_JEXEC' ) or die;

$config = $this->config;

use Joomla\CMS\Language\Text;

?>
<table class="table table-striped table-bordered">
	<thead>
		<tr>
			<th class="title"><?php echo Text::_('EB_TIME')?></th>
			<th class="center"><?php echo Text::_('EB_NUMBER_REGISTRANTS')?></th>
			<th class="title"><?php echo Text::_('EB_AMOUNT')?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<?php echo Text::_('EB_TODAY'); ?>
			</td>
			<td class="center">
				<?php echo $this->data['today']['total_registrants']; ?>
			</td>
			<td>
				<?php echo EventbookingHelper::formatCurrency($this->data['today']['total_amount'], $config) ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo Text::_('EB_YESTERDAY'); ?>
			</td>
			<td class="center">
				<?php echo $this->data['yesterday']['total_registrants']; ?>
			</td>
			<td>
				<?php echo EventbookingHelper::formatCurrency($this->data['yesterday']['total_amount'], $config) ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo Text::_('EB_THIS_WEEK'); ?>
			</td>
			<td class="center">
				<?php echo $this->data['this_week']['total_registrants']; ?>
			</td>
			<td>
				<?php echo EventbookingHelper::formatCurrency($this->data['this_week']['total_amount'], $config) ?>
			</td>
		</tr>

		<tr>
			<td>
				<?php echo Text::_('EB_LAST_WEEK'); ?>
			</td>
			<td class="center">
				<?php echo $this->data['last_week']['total_registrants']; ?>
			</td>
			<td>
				<?php echo EventbookingHelper::formatCurrency($this->data['last_week']['total_amount'], $config) ?>
			</td>
		</tr>

		<tr>
			<td>
				<?php echo Text::_('EB_THIS_MONTH'); ?>
			</td>
			<td class="center">
				<?php echo $this->data['this_month']['total_registrants']; ?>
			</td>
			<td>
				<?php echo EventbookingHelper::formatCurrency($this->data['this_month']['total_amount'], $config) ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo Text::_('EB_LAST_MONTH'); ?>
			</td>
			<td class="center">
				<?php echo $this->data['last_month']['total_registrants']; ?>
			</td>
			<td>
				<?php echo EventbookingHelper::formatCurrency($this->data['last_month']['total_amount'], $config) ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo Text::_('EB_THIS_YEAR'); ?>
			</td>
			<td class="center">
				<?php echo $this->data['this_year']['total_registrants']; ?>
			</td>
			<td>
				<?php echo EventbookingHelper::formatCurrency($this->data['this_year']['total_amount'], $config) ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo Text::_('EB_LAST_YEAR'); ?>
			</td>
			<td class="center">
				<?php echo $this->data['last_year']['total_registrants']; ?>
			</td>
			<td>
				<?php echo EventbookingHelper::formatCurrency($this->data['last_year']['total_amount'], $config) ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo Text::_('EB_TOTAL_REGISTRATION'); ?>
			</td>
			<td class="center">
				<?php echo $this->data['total_registration']['total_registrants']; ?>
			</td>
			<td>
				<?php echo EventbookingHelper::formatCurrency($this->data['total_registration']['total_amount'], $config) ?>
			</td>
		</tr>
	</tbody>
</table>