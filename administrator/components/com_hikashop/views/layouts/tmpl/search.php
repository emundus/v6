<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$value = $this->params->get('value', $this->pageInfo->search);
$map = $this->params->get('map', 'search');
$id = $this->params->get('id', $map);
$class = '';
if(HIKASHOP_J40){
	$class = 'form-control';
}
if(!empty($this->searchOptions)) {
	$this->searching = false;
	foreach($this->searchOptions as $option => $default) {
		if(isset($this->pageInfo->filter->$option) && $this->pageInfo->filter->$option != $default)
			$this->searching = true;
		$option = 'filter_'.$option;
		if(isset($this->pageInfo->filter->$option) && $this->pageInfo->filter->$option != $default)
			$this->searching = true;
	}
}
$icon = 'fa-chevron-down';
$clearClass = 'hikashop_not_searching';
$onclick = 'disabled onclick="return false;"';
$onclickSearch = '';
if(!empty($this->searching)) {
	$icon = 'fa-chevron-up';
	$clearClass = 'hikashop_searching';
	$this->openfeatures_class = 'show-features';
	$onclickSearch = 'window.hikashop.clearOptions([\''.@implode('\',\'', @array_keys($this->searchOptions)).'\'], [\''. @implode('\',\'', @$this->searchOptions).'\']);';
	$onclick = 'onclick="return '.$onclickSearch.'"';
}
if(!empty($value)) {
	$clearClass = 'hikashop_searching';
	$onclick = 'onclick="'.$onclickSearch.'document.getElementById(\''.$id.'\').value=\'\'; return true;"';
}
?>
<div class="input-group hikashop_search_listing">
	<input type="text" name="<?php echo $map; ?>" id="<?php echo $id; ?>" value="<?php echo $this->escape($value);?>" class="<?php echo $class; ?>" placeholder="<?php echo JText::_('HIKA_SEARCH'); ?>" onchange="this.form.submit();" />
	<span class="input-group-append hikashop_search_btn">
		<button class="btn btn-primary" onclick="this.form.limitstart.value=0;this.form.submit();"><i class="fa fa-search"></i></button>
	</span>
<?php
if(!empty($this->searchOptions)) {
?>
	<span class="hikashop_search_option">
		<button class="btn btn-primary" onclick="return window.hikashop.toggleOptions();">
			<span><?php echo JText::_('HIKASHOP_FILTER_OPTIONS'); ?></span>
			<span id="openSearch_btn">
				<i class="fas <?php echo $icon; ?>"></i>
			</span>
		</button>
	</span>
<?php
}
?>
	<span class="input-group-append hikashop_search_clear <?php echo $clearClass; ?>">
		<button class="btn btn-primary" <?php echo $onclick; ?>><?php echo JText::_('HIKASHOP_FILTER_CLEAR'); ?></button>
	</span>
</div>
