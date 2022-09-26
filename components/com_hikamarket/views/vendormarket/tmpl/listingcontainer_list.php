<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(empty($this->rows))
	return;

$only_if_products = $this->params->get('only_if_products', 0);

?><div class="hikamarket_vendors">
	<ul class="hikamarket_vendor_list<?php echo $this->params->get('ul_class_name'); ?>">
<?php
		$width = (int)(100 / $this->params->get('columns'));
		if(empty($width))
			$width = '';
		else
			$width = 'style="width:' . $width . '%;"';

		foreach($this->rows as $row) {
			if($only_if_products && $row->number_of_products < 1)
				continue;
			$link = hikamarket::completeLink('vendor&task=show&cid=' . $row->vendor_id . '&name=' . $row->vendor_name . $this->menu_id);
			$class = '';
?>
		<li class="hikamarket_vendor_list_item<?php echo $class; ?>" <?php echo $width; ?>>
			<a href="<?php echo $link; ?>" ><?php echo $row->vendor_name;
				if($this->params->get('number_of_products', 0))
					echo ' (' . $row->number_of_products . ')';
			?></a>
		</li>
<?php
		}
?>
	</ul>
</div>
