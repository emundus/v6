<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikamarket_plugin_listing">
<form action="<?php echo hikamarket::completeLink('plugin&plugin_type='.$this->type.'&task=listing'); ?>" method="post" id="adminForm" name="adminForm">

<div class="hk-row-fluid">
	<div class="hkc-md-12">
<?php
	echo $this->loadHkLayout('search', array(
		'id' => 'hikamarket_plugin_listing_search',
	));
?>
		<div class="hikam_sort_zone"><?php
			if(!empty($this->ordering_values))
				echo JHTML::_('select.genericlist', $this->ordering_values, 'filter_fullorder', 'onchange="this.form.submit();"', 'value', 'text', $this->full_ordering);
		?></div>
	</div>
	<div class="hkc-md-12">
		<div class="expand-filters">
<?php
	$status_types = array(
		-1 => JText::_('HIKA_ALL_STATUSES'),
		1 => JText::_('HIKA_PUBLISHED'),
		0 => JText::_('HIKA_UNPUBLISHED'),
	);
	echo JHTML::_('select.genericlist', $status_types, 'filter_published', 'data-search-reset="-1" onchange="this.form.submit();"', 'value', 'text', $this->pageInfo->filter->published);

?>
		</div>
		<div style="clear:both"></div>
	</div>
</div>
<div id="hikam_<?php echo $this->type; ?>_main_listing">
<?php
$p_id = $this->type.'_id';
$p_name = $this->type.'_name';
$p_order = $this->type.'_ordering';
$p_published = $this->type.'_published';
$p_type = $this->type.'_type';
$publish_type = 'plugin';
if(in_array($this->type, array('payment', 'shipping')))
	$publish_type = $this->type;

$publish_content = '<i class="fas fa-check"></i> ' . JText::_('HIKA_PUBLISHED');
$unpublish_content = '<i class="fas fa-times"></i> ' . JText::_('HIKA_UNPUBLISHED');

$icon = '<i class="fas fa-puzzle-piece"></i>';
if($this->type == 'payment')
	$icon = '<i class="far fa-credit-card"></i>';
if($this->type == 'shipping')
	$icon = '<i class="fas fa-shipping-fast"></i>';

$restriction_icons = array(
	'min_volume' => '<i class="fas fa-box hk-icon-green"></i>',
	'max_volume' => '<i class="fas fa-box hk-icon-orange"></i>',
	'min_weight' => '<i class="fas fa-weight-hanging hk-icon-green"></i>',
	'max_weight' => '<i class="fas fa-weight-hanging hk-icon-orange"></i>',
	'min_price' => '<i class="far fa-money-bill-alt hk-icon-green"></i>',
	'max_price' => '<i class="far fa-money-bill-alt hk-icon-orange"></i>',
	'zone' => '<i class="fas fa-map-marker-alt hk-icon-blue"></i>',
	'vendor' => '<i class="fas fa-user-tie hk-icon-blue"></i>',
);

foreach($this->plugins as $plugin) {
	$id = 'market_plugin_' . $this->type.'_' . $plugin->$p_id;
	$published_id = $this->type.'_published-' . $plugin->$p_id;

	$url = ($this->manage) ? hikamarket::completeLink('plugin&plugin_type='.$this->type.'&task=edit&name='. $plugin->$p_type .'&cid='.$plugin->$p_id.$this->url_itemid) : '#';
	$extra_classes = '';
?>
	<div class="hk-card hk-card-default hk-card-plugin hk-card-<?php echo $this->type; ?><?php echo $extra_classes; ?>" data-hkm-plugin="<?php echo (int)$plugin->$p_id; ?>">
		<div class="hk-card-header">
			<a class="hk-row-fluid" href="<?php echo $url; ?>">
				<div class="hkc-sm-6 hkm_plugin_name"><?php
	if(!empty($plugin->$p_name))
		echo $plugin->$p_name;
	else
		echo '<em>' . JText::_('HIKA_NONE') . '</em>';
				?></div>
				<div class="hkc-sm-6 hkm_plugin_type"><?php
	if(!empty($currentPlugin))
		echo $currentPlugin->name;
	else
		echo $plugin->$p_type;
				?></div>
			</a>
		</div>
		<div class="hk-card-body">
			<div class="hk-row-fluid">
				<div class="hkc-sm-7 hkm_plugin_details">
<?php
		if(!empty($this->listing_columns)) {
			foreach($this->listing_columns as $key => $column) {
				if(!isset($column['col']) || empty($plugin->{$column['col']}))
					continue;

				$data = $plugin->{$column['col']};

				if($key == 'price') {
?>
				<div class="hkm_plugin_price">
					<i class="fa fa-credit-card hk-icon-blue"></i> <?php
					if(!empty($data['fixed']))
						echo $data['fixed'];
					if(!empty($data['fixed']) && !empty($data['percent']))
						echo ' + ';
					if(!empty($data['percent']))
						echo $data['percent'] . '%';
				?></div>
<?php
					continue;
				}

				if($key == 'restriction') {
					foreach($data as $k => $v) {
?>
				<div class="hkm_plugin_restriction_<?php echo $k; ?>">
					<?php if(isset($restriction_icons[$k])) echo $restriction_icons[$k]; ?>
					<strong><?php echo JText::_($v['name']); ?></strong> - <?php echo $v['value']; ?>
				</div>
<?php
					}
					continue;
				}

?>				<div class="hkm_plugin_detail_<?php echo $key; ?>">
					<?php if(isset($restriction_icons[$key])) echo $restriction_icons[$key]; ?>
					<strong><?php echo JText::_($column['name']); ?></strong> <?php
					if(is_string($data))
						echo $data;
					else
						echo implode('<br/>', $data);
				?></div>
<?php
			}
		}
?>
				</div>
				<div class="hkc-sm-3 hkm_plugin_publish">
<?php
		if($this->plugin_action_publish) {
?>
					<a class="hikabtn hikabtn-<?php echo ($plugin->$p_published) ? 'success' : 'danger'; ?> hkm_publish_button" data-toggle-state="<?php echo $plugin->$p_published ? 1 : 0; ?>" data-toggle-id="<?php echo $plugin->$p_id; ?>" onclick="return window.localPage.togglePlugin(this);"><?php
						echo ($plugin->$p_published) ? $publish_content : $unpublish_content;
					?></a>
<?php
		} else {
?>
					<span class="hkm_publish_state hk-label hk-label-<?php echo ($plugin->$p_published) ? 'green' : 'red'; ?>"><?php echo ($plugin->$p_published) ? $publish_content : $unpublish_content; ?></span>
<?php
		}
?>
				</div>
				<div class="hkc-sm-2 hkm_plugin_actions"><?php
	$data = array(
		'details' => array(
			'name' => '<i class="fas fa-search"></i> ' . JText::_('HIKA_DETAILS', true),
			'link' => $url
		)
	);
	if($this->plugin_action_delete) {
		$data['delete'] = array(
			'name' => '<i class="fas fa-trash"></i> ' . JText::_('HIKA_DELETE', true),
			'link' => '#delete',
			'click' => 'return window.localPage.deletePlugin('.(int)$plugin->$p_id.', \''.urlencode(strip_tags($plugin->$p_name)).'\');'
		);
	}
	if(!empty($data)) {
		echo $this->dropdownHelper->display(
			JText::_('HIKA_ACTIONS'),
			$data,
			array('type' => '', 'class' => 'hikabtn-primary', 'right' => true, 'up' => false)
		);
	}
				?></div>
			</div>
		</div>
	</div>
<?php
}
?>
	<div class="hikamarket_plugins_footer">
		<div class="hikamarket_pagination">
			<?php echo $this->pagination->getListFooter(); ?>
			<?php echo $this->pagination->getResultsCounter(); ?>
		</div>
	</div>
</div>
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="listing" />
	<input type="hidden" name="plugin_type" value="<?php echo $this->escape($this->type); ?>" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<?php echo JHTML::_('form.token'); ?>
</form>
</div>
<?php if($this->plugin_action_publish) { ?>
<script type="text/javascript">
if(!window.localPage) window.localPage = {};
window.localPage.togglePlugin = function(el) {
	var w=window, d=document, o=w.Oby,
		state = el.getAttribute('data-toggle-state'),
		id = el.getAttribute('data-toggle-id');
	if(!id) return false;
	var url="<?php echo hikamarket::completeLink('toggle','ajax',true); ?>",
		v = (state == 0) ? 1 : 0,
		data=o.encodeFormData({"task":"<?php echo $publish_type; ?>_published-"+id,"value":v,"table":"<?php echo $publish_type; ?>","<?php echo hikamarket::getFormToken(); ?>":1});
	el.disabled = true;
	if(state == 1) el.innerHTML = "<i class=\"fas fa-spinner fa-pulse\"></i> <?php echo JText::_('HIKA_UNPUBLISHING', true); ?>";
	else el.innerHTML = "<i class=\"fas fa-spinner fa-pulse\"></i> <?php echo JText::_('HIKA_PUBLISHING', true); ?>";
	el.classList.remove("hikabtn-success", "hikabtn-danger");
	o.xRequest(url,{mode:"POST",data:data},function(x,p){
		if(x.responseText && x.responseText == '1')
			state = v;
		el.disabled = false;
		el.setAttribute('data-toggle-state', v);
		if(state == 1) el.innerHTML = "<i class=\"fas fa-check\"></i> <?php echo JText::_('HIKA_PUBLISHED', true); ?>";
		else el.innerHTML = "<i class=\"fas fa-times\"></i> <?php echo JText::_('HIKA_UNPUBLISHED', true); ?>";
		el.classList.add( state ? "hikabtn-success" : "hikabtn-danger" );
	});
};
</script>
<?php } ?>
<?php if($this->plugin_action_delete) { ?>
<script type="text/javascript">
if(!window.localPage) window.localPage = {};
window.localPage.deletePlugin = function(id, name) {
	var confirmMsg = "<?php echo JText::_('CONFIRM_DELETE_PLUGIN_X'); ?>";
	if(!confirm(confirmMsg.replace('{PLUGIN}', decodeURI(name))))
		return false;
	var f = document.forms['hikamarket_delete_plugin_form'];
	if(!f) return false;
	f.plugin_id.value = id;
	f.submit();
	return false;
};
</script>
<form action="<?php echo hikamarket::completeLink('plugin&task=delete'); ?>" method="post" name="hikamarket_delete_plugin_form" id="hikamarket_delete_plugin_form">
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" value="delete" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<input type="hidden" name="plugin_type" value="<?php echo $this->escape($this->type); ?>" />
	<input type="hidden" name="plugin_id" value="0" />
	<?php echo JHTML::_('form.token'); ?>
</form>
<?php }
