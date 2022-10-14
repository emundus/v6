<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><script type="text/javascript">
window.pluginMgr = {};
window.pluginMgr.cpt = {};
</script>
<form action="<?php echo hikamarket::completeLink('plugin&plugin_type='.$this->type);?>" method="post" name="hikamarket_form" id="hikamarket_plugin_form">
	<h1><?php
		echo JText::_('HIKA_PLUGIN');
		$key = 'PLG_HIKASHOP'.strtoupper($this->type).'_'.strtoupper($this->name);
		if(JText::_($key) != $key)
			echo JText::_($key);
		else
			echo $this->name;
	?></h1>
	<h3><?php echo JText::_('MAIN_INFORMATION'); ?></h3>
<?php
	if(!empty($this->main_form)) {
		echo $this->processConfig($this->main_form, $this->type, '', @$this->element, '', true);
	}

	if(!empty($this->content) && hikamarket::acl($this->type.'plugin/edit/specific')) {
?>
	<h3><?php echo JText::_('PLUGIN_SPECIFIC_CONFIGURATION'); ?></h3>
<?php if(!empty($this->pluginTemplateMode) && $this->pluginTemplateMode == 'html') { ?>
		<table class="hikam_listing table"><?php
			echo $this->content;
		?></table>
<?php } else
			echo $this->content;
	}

	if(!empty($this->extra_blocks)) {
		foreach($this->extra_blocks as $extra_block) {
			if(is_string($extra_block))
				echo $extra_block."\r\n";
			else
				echo $this->processConfig($extra_block, $this->type, '', @$this->element, '', true);
		}
	}

	if(!empty($this->restriction_form)) {
		$values = array();
		foreach($this->restriction_form as $key => $r) {
			if(empty($r['category']))
				continue;
			$c = $r['category'];
			if(empty($values[$c]))
				$values[$c] = JHTML::_('select.option', $c, JText::_('HIKA_RESTRICTION_'.strtoupper($c)));
			if($values[$c]->disable == false && (!isset($r['category_check']) || !empty($r['category_check']))) {
				$l = '';
				$data = $this->getParamsData(@$this->element, $key, $this->type, '', $l);
				if(!empty($data) && (empty($r['empty_value']) || $r['empty_value'] != $data))
					$values[$c]->disable = true;
			}
		}
		foreach($this->restriction_form as &$r) {
			if(empty($r['category']))
				continue;
			$c = $r['category'];
			if(isset($values[$c]) && $values[$c]->disable == false)
				$r['hidden'] = true;
		}
		unset($r);
?>
	<h3><?php echo JText::_('HIKA_RESTRICTIONS'); ?></h3>
<?php
		if(!empty($values)) {
?>
	<div id="plugin_add_restriction_zone" style="display:none;"><?php
		echo JHTML::_('select.genericlist', $values, '', 'id="plugin_add_restriction_value"', 'value', 'text', '');
	?><button class="hikabtn hikabtn-danger" onclick="return window.pluginMgr.cancelRestriction();"><i class="far fa-times-circle"></i> <?php echo JText::_('HIKA_CANCEL'); ?></button></div>
	<button class="hikabtn hikabtn-default" onclick="return window.pluginMgr.addRestriction();"><i class="fas fa-plus"></i> <?php echo JText::_('HIKA_ADD_RESTRICTION'); ?></button>
<?php
		}

		echo $this->processConfig($this->restriction_form, $this->type, '', @$this->element, 'hikamarket_'.$this->type.'_restrictions', true);
	}
?>
	<input type="hidden" name="cid" value="<?php echo @$this->element->{$this->type.'_id'}; ?>"/>
	<input type="hidden" name="name" value="<?php echo $this->escape($this->plugin_name); ?>"/>
	<input type="hidden" name="plugin_type" value="<?php echo $this->escape($this->type); ?>"/>
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>"/>
	<input type="hidden" name="task" value="show"/>
	<input type="hidden" name="ctrl" value="plugin"/>
	<?php echo JHTML::_('form.token'); ?>
</form>
<script type="text/javascript">
window.hikashop.ready(function(){ window.hikamarket.dlTitle('hikamarket_plugin_form'); });
window.pluginMgr.addRestriction = function() {
	var d = document, el = d.getElementById('plugin_add_restriction_zone');
	if(!el) return false;
	if(el.style.display == 'none') {
		el.style.display = 'inline';
		return false;
	}

	var sel = d.getElementById('plugin_add_restriction_value');
	if(!sel) return false;
	var v = sel.value, i = sel.selectedIndex, key = 'hikamarket_<?php echo $this->type; ?>_cat_' + v, e = null,
		container = d.getElementById('hikamarket_<?php echo $this->type; ?>_restrictions');
	container.querySelectorAll('[data-hkm-key="'+key+'"]').forEach(function(el){
		el.style.display = '';
	});
	sel.options[i].disabled = 'disabled';
	if(typeof(jQuery) != 'undefined' && typeof(jQuery().chosen) == 'function')
		jQuery(sel).trigger('liszt:updated');
	el.style.display = 'none';
	return false;
};
window.pluginMgr.cancelRestriction = function() {
	var d = document, el = d.getElementById('plugin_add_restriction_zone');
	if(el) el.style.display = 'none';
	return false;
};
window.pluginMgr.links = <?php if(!empty($this->displayTriggers)) { echo json_encode($this->displayTriggers); } else { echo '{}'; } ?>;
window.Oby.registerAjax('field_changed', function(params) {
	if(!!window.pluginMgr.links || !window.pluginMgr.links[params.key])
		return;
	var d = document, value = null;
	if(params.obj && params.obj.value)
		value = params.obj.value;
	if(value === null)
		return;
	var tTitle = null, tValue = null,
		items = window.pluginMgr.links[params.key];
	for(var k in items) {
		if(!items.hasOwnProperty(k))
			continue;

		tTitle = d.getElementById(items[k][0].replace('{TYPE}', 'title'));
		tValue = d.getElementById(items[k][0].replace('{TYPE}', 'value'));
		if(items[k][1].indexOf(value) < 0) {
			tTitle.style.display = 'none';
			tValue.style.display = 'none';
		} else {
			tTitle.style.display = '';
			tValue.style.display = '';
		}
	}
});
<?php if(!empty($this->hiddenElements)) { ?>
window.hikashop.ready(function(){
	var d = document, el = null,
		hide = <?php echo json_encode($this->hiddenElements); ?>;
	for(var i = hide.length - 1; i >= 0; i--) {
		el = d.getElementById(hide[i]);
		if(el) el.style.display = 'none';
	}
});
<?php } ?>
</script>
