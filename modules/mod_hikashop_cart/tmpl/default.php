<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(!empty($html)){
?>
<div class="hikashop_cart_module <?php echo @$module->params['moduleclass_sfx']; ?>" id="hikashop_cart_module">
<?php echo $html; ?>
</div>
<?php }
