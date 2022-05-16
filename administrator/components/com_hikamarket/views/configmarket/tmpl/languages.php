<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="page-languages">

<div class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('LANGUAGES'); ?></div>
<table class="adminlist table table-striped table-hover" cellpadding="1">
	<thead>
		<tr>
			<th class="title titlenum"><?php
				echo JText::_( 'HIKA_NUM' );
			?></th>
			<th class="title titletoggle"><?php
				echo JText::_('HIKA_EDIT');
			?></th>
			<th class="title"><?php
				echo JText::_('HIKA_NAME');
			?></th>
			<th class="title titletoggle"><?php
				echo JText::_('ID');
			?></th>
		</tr>
	</thead>
	<tbody>
<?php
	$k = 0;
	foreach($this->languages as $i => &$language) {
?>
		<tr class="row<?php echo $k; ?>">
			<td align="center"><?php
				echo $i+1;
			?></td>
			<td align="center"><?php
				if($this->manage)
					echo $language->edit;
			?></td>
			<td align="center"><?php
				echo $language->name;
			?></td>
			<td align="center"><?php
				echo $language->language;
			?></td>
		</tr>
<?php
		$k = 1 - $k;
		unset($language);
	}
?>
	</tbody>
</table>
	</div></div>
</div>

</div>
