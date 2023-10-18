<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=config" method="post"  name="adminForm" id="adminForm">
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" id="config_form_task" value="" />
	<input type="hidden" name="ctrl" value="config" />
	<?php echo JHTML::_('form.token'); ?>
	<ul class="hika_tabs" rel="tabs:hikashop_config_page_tab_">
<?php
	$configTabs = array(
		'config_main' => array('MAIN', 'main'),
		'config_checkout' => array('CHECKOUT', 'checkout'),
		'config_display' => array('DISPLAY', 'display'),
		'config_features' => array('HIKA_FEATURES', 'features'),
		'config_advanced' => array('HIKA_ADVANCED', 'advanced'),
		'config_languages' => array('LANGUAGES', 'languages')
	);

	if(hikashop_level(2)) {
		$configTabs['config_acl'] = array('ACCESS_LEVEL', 'acl');
	}

	if(hikashop_level(1)) {
		$configTabs['config_cron'] = array('CRON', 'cron');
	}
	$tabsContent = array();
	foreach($configTabs as $pane => $paneOpt) {
		if($paneOpt[1] != 'separator') {
			$this->setLayout($paneOpt[1]);
			$tabsContent[$pane] = $this->loadTemplate();
		}
	}
	$app = JFactory::getApplication();
	$app->triggerEvent('onHikashopConfigTabsList', array(&$configTabs, &$tabsContent));

	$active = 'config_main';
	$default_id = '';
	foreach($configTabs as $pane => $paneOpt) {
		$attr = '';
		$id = 'hikashop_config_tab_title_' . $paneOpt[1];
		if($active == $pane) {
			$attr = 'class="active"';
			$default_id = $id;
		}
		if($paneOpt[1] == 'separator') { 
			echo '<li role="separator" class="hikashop-menu n-separator"><a class="hikashop-hide" onclick="return false;" id="'.$id.'"></a></li>';
		} else {
			echo '<li '.$attr.'><a href="#'.$pane.'" rel="tab:'.$paneOpt[1].'" onclick="return configWatcher.switchTab(this);" id="'.$id.'">' . JText::_($paneOpt[0]) . '</a></li>';
		}
	}

?>
	</ul>
	<div style="clear:both;" class="clr"></div>
<?php
	foreach($configTabs as $pane => $paneOpt) {
		echo '<div id="hikashop_config_page_tab_'.$paneOpt[1].'">';
		if(!empty($tabsContent[$pane])) {
			echo $tabsContent[$pane];
		}
		echo '</div>';
	}
?>
	<div style="clear:both;" class="clr"></div>
</form>
<script>
var configWatcher = {
	currentHRef : '',
	init: function(){
		var t = this;
		setInterval( function(){ t.periodical(); }, 50 );
<?php
	if(HIKASHOP_BACK_RESPONSIVE) {
?>
		jQuery("ul.nav-remember").each(function(nav){
			var id = jQuery(this).attr("id");
			jQuery("#" + id + " a[data-toggle=\"tab\"]").on("shown", function (e) {
				var myHash = jQuery(this).attr("id").replace("config_","").replace("_tablink","");
				if(window.location.hash.substr(1, myHash.length) != myHash)
					window.location.hash = myHash;
			});
		});
<?php
	}
?>
		t.navTop = document.querySelector(".leftmenu-container").offsetHeight;
	},
	periodical: function() {
		var href = window.location.hash.substring(1);
		if( href != this.currentHRef ) {
			this.currentHRef = href;
			this.switchAndScroll(href);
		}
	},
	switchAndScroll: function(hash) {
		if(hash.length == 0)
			return;
		if(hash.indexOf('_') < 0) {
			var tabName = hash;
			hash = '';
		} else {
			var tabName = hash.substr(0, hash.indexOf('_'));
		}

		this.switchTab(document.getElementById('hikashop_config_tab_title_'+tabName));
		this.scrollToCust( hash );

	},
	scrollToCust: function(name) {
		var d = document, elem = d.getElementById(name);
		if( !elem ) { window.scrollTo(0, 0); return; }
		var topPos = elem.offsetTop - 80;
		window.scrollTo(0, topPos);
	},
	switchTab: function(el, force) {
		if(force)
			el = document.getElementById(force);
		window.hikashop.switchTab(el);
		if(el)
			localStorage.setItem('hikashop_backend_config_last_tab', el.id);
	}
}
window.hikashop.ready( function(){
	var d = document, w = window, o = w.Oby, s = localStorage.getItem('hikashop_backend_config_last_section');
	configWatcher.switchTab(d.getElementById('<?php echo $default_id; ?>'), localStorage.getItem('hikashop_backend_config_last_tab'));
	if(s)
		configWatcher.scrollToCust(s);
	setTimeout(function(){ configWatcher.init(); }, 50);
});
</script>
