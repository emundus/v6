<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="config_plugins">

<div class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('PLUGINS'); ?></div>

<table class="adminlist table table-striped table-hover" cellpadding="1">
	<thead>
		<tr>
			<th class="title titlenum"><?php
				echo JText::_('HIKA_NUM');
			?></th>
			<th class="title"><?php
				echo JText::_('HIKA_NAME');
			?></th>
			<th class="title titletoggle"><?php
				echo JText::_('HIKA_ENABLED');
			?></th>
			<th class="title titleid"><?php
				echo JText::_('ID');
			?></th>
		</tr>
	</thead>
	<tbody>
<?php
	$k = 0;
	$publishedid = 'enabled-';
	$url = 'index.php?option=com_plugins&amp;task=plugin.edit&amp;extension_id=';

	foreach($this->plugins as $i => &$row) {
?>
		<tr class="row<?php echo $k; ?>">
			<td class="hk_center"><?php
				echo $i + 1
			?></td>
			<td>
				<a target="_blank" href="<?php echo $url.$row->id; ?>"><?php echo $row->name; ?></a>
			</td>
			<td class="hk_center"><?php
		if($this->manage){
?>
				<span id="<?php echo $publishedid.$row->id; ?>" class="loading"><?php echo $this->toggleClass->toggle($publishedid.$row->id, $row->published, 'plugins') ?></span>
<?php
		} else {
			echo $this->toggleClass->display('activate', $row->published);
		}
			?></td>
			<td class="hk_center"><?php
				echo $row->id;
			?></td>
		</tr>
<?php
		$k = 1-$k;
	}
	unset($row);
?>
	</tbody>
</table>

	</div></div>
</div>
</div>
