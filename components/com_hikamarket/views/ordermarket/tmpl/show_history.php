<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><legend><?php echo JText::_('HISTORY'); ?></legend>
<div class="hikamarket_history_container">
<table id="hikamarket_order_history_listing" class="hikam_listing hikam_table table table-striped table-hover">
	<thead>
		<tr>
			<th class="title"><?php
				echo JText::_('HIKA_TYPE');
			?></th>
			<th class="title"><?php
				echo JText::_('ORDER_STATUS');
			?></th>
			<th class="title"><?php
				echo JText::_('REASON');
			?></th>
			<th class="title"><?php
				echo JText::_('HIKA_USER').' / '.JText::_('IP');
			?></th>
			<th class="title"><?php
				echo JText::_('DATE');
			?></th>
			<th class="title"><?php
				echo JText::_('INFORMATION');
			?></th>
		</tr>
	</thead>
	<tbody>
<?php
$userClass = hikamarket::get('shop.class.user');
foreach($this->order->history as $k => $history) {
?>
		<tr>
			<td><?php
				$val = preg_replace('#[^a-z0-9]#i','_',strtoupper($history->history_type));
				$trans = JText::_($val);
				if($val != $trans)
					$history->history_type = $trans;
				echo $history->history_type;
			?></td>
			<td><?php
				echo hikamarket::orderStatus($history->history_new_status);
			?></td>
			<td><?php
				echo $history->history_reason;
			?></td>
			<td><?php
				if(!empty($history->history_user_id)){
					$user = $userClass->get($history->history_user_id);
					echo $user->username.' / ';
				}
				echo $history->history_ip;
			?></td>
			<td><?php
				echo hikamarket::getDate($history->history_created,'%Y-%m-%d %H:%M');
			?></td>
			<td><?php
				echo $history->history_data;
			?></td>
		</tr>
<?php
}
?>
	</tbody>
</table>
</div>
