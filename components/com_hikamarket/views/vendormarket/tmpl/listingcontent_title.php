<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><span class="hikamarket_vendor_name">
<?php
	if($this->params->get('link_to_vendor_page')) {
?>
	<a href="<?php echo hikamarket::completeLink('vendor&task=show&cid='.$this->row->vendor_id.'&name='.$this->row->alias . $this->menu_id); ?>"><?php
	}
	echo $this->row->vendor_name;
	if($this->params->get('number_of_products', 0))
		echo ' (' . $this->row->number_of_products . ')';

	if($this->params->get('link_to_vendor_page')) {
	?></a>
<?php
	}
?>
</span>
