<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>				<span id="result" >
					<?php echo @$this->rows[0]->product_id.' '.@$this->rows[0]->product_name; ?>
					<input type="hidden" name="<?php echo $this->control; ?>" value="<?php echo @$this->rows[0]->product_id; ?>" />
				</span>
