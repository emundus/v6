<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(!empty($html)) {
?>
<div id="hikashop_module_<?php echo $module->id;?>" class="hikamarket_module <?php echo @$module->params['moduleclass_sfx']; ?>"><?php
	echo $html;
?></div>
<?php
}
