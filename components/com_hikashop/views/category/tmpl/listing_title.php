<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><span class="hikashop_category_name">
	<a href="<?php echo $this->row->link;?>">
		<?php
		echo $this->row->category_name;
		if($this->params->get('number_of_products',0))
			echo ' ('.$this->row->number_of_products.')';
		?>
	</a>
</span>
