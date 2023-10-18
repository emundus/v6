<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if(!empty($html)){
?>
<div class="hikashop_filter_module <?php if(isset($params)) echo $params->get('moduleclass_sfx');?>">
<?php echo $html;?>
<div style="clear:both;"></div>
</div>
<?php } ?>
