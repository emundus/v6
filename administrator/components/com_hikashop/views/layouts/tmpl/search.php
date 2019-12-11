<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.2.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2019 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$value = $this->params->get('value', $this->pageInfo->search);
$map = $this->params->get('map', 'search');
$id = $this->params->get('id', $map);
if(!HIKASHOP_J40) {
 ?>
<div class="input-prepend input-append" style="margin-top:4px;">
	<span class="add-on"><i class="icon-filter"></i></span>
	<input type="text" name="<?php echo $map; ?>" id="<?php echo $id; ?>" value="<?php echo $this->escape($value);?>" class="text_area" onchange="this.form.submit();" />
	<button class="btn" onclick="this.form.limitstart.value=0;this.form.submit();"><i class="icon-search"></i></button>
	<button class="btn" onclick="window.hikashop.clearSearch(this, '<?php echo $id; ?>');"><i class="icon-remove"></i></button>
</div>
<?php
} else {
?>
<div class="input-group">
	<input type="text" name="<?php echo $map; ?>" id="<?php echo $id; ?>" value="<?php echo $this->escape($value);?>" class="form-control" placeholder="<?php echo JText::_('HIKA_SEARCH'); ?>" onchange="this.form.submit();" />
	<span class="input-group-append">
		<button class="btn btn-primary" onclick="this.form.limitstart.value=0;this.form.submit();"><i class="fa fa-search"></i></button>
	</span>
	<span class="input-group-append">
		<button class="btn btn-primary" onclick="window.hikashop.clearSearch(this, '<?php echo $id; ?>');"><i class="fa fa-times"></i></button>
	</span>
</div>
<?php
}
