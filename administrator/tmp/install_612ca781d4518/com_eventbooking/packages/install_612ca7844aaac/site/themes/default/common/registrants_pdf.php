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

$config = EventbookingHelper::getConfig();
$i = 1;
?>
<p style="padding-bottom: 20px; text-align: center;">
<h1><?php echo Text::_('EB_REGISTRANTS_LIST'); ?></h1>
</p>
<table border="1" width="100%" cellspacing="0" cellpadding="2" style="margin-top: 100px;">
	<thead>
		<tr>
			<th width="3%" height="20" style="text-align: center;">
				No
			</th>
			<th height="20" width="10%">
				<?php echo Text::_('EB_FIRST_NAME'); ?>
			</th height="20">
			<th height="20" width="10%">
				<?php echo Text::_('EB_LAST_NAME'); ?>
			</th height="20">
			<th height="20" width="20%" style="text-align: center;">
				<?php echo Text::_('EB_EVENT'); ?>
			</th>
			<th height="20" width="10%">
				<?php echo Text::_('EB_EVENT_DATE'); ?>
			</th>
			<th height="20" width="16%">
				<?php echo Text::_('EB_EMAIL'); ?>
			</th>
			<th height="20" width="10%" style="text-align: center;">
				<?php echo Text::_('EB_NUMBER_REGISTRANTS'); ?>
			</th>
			<th height="20" width="10%" style="text-align: center;">
				<?php echo Text::_('EB_REGISTRATION_DATE'); ?>
			</th>
			<th width="8%" height="20" style="text-align: right;">
				<?php echo Text::_('EB_AMOUNT'); ?>
			</th>
			<th width="3%" height="20" style="text-align: center;">
				<?php echo Text::_('EB_ID'); ?>
			</th>
		</tr>
	</thead>
	<tbody>
	<?php
		foreach ($rows as $row)
		{
		?>
			<tr>
				<td width="3%" style="text-align: center;"><?php echo $i++; ?></td>
				<td width="10%"><?php echo $row->first_name; ?></td>
				<td width="10%"><?php echo $row->last_name; ?></td>
				<td width="20%;"><?php echo $row->title; ?></td>
				<td width="10%"><?php echo $row->event_date; ?></td>
				<td width="16%"><?php echo $row->email; ?></td>
				<td width="10%" style="text-align: center;"><?php echo $row->number_registrants; ?></td>
				<td width="10%" style="text-align: center;"><?php echo $row->register_date; ?></td>
				<td width="8%" style="text-align: right;"><?php echo $row->amount; ?></td>
				<td width="3%" style="text-align: center;"><?php echo $row->id; ?></td>
			</tr>
		<?php
		}
	?>
	</tbody>
</table>