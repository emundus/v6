<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikamarket::completeLink('config'); ?>" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
	<input type="hidden" name="task" id="config_form_task" value="" />
	<input type="hidden" name="ctrl" value="config" />
	<?php echo JHTML::_('form.token'); ?>
<?php

	$options = array(
		'startOffset' => $this->default_tab,
		'useCookie' => true
	);

	if(!HIKASHOP_J30) {
		$options['onActive'] = '
function(title, description) {
	description.setStyle("display", "block");
	title.addClass("open").removeClass("closed");
	if(title.getAttribute("class").indexOf("config_") >= 0)
		myHash = title.getAttribute("class").replace("tabs","").replace("open","").replace("config_","").replace(/^\s*|\s*$/g, "");
	else
		myHash = title.getAttribute("id").replace("config_","").replace(/^\s*|\s*$/g, "");
	if(window.location.hash.substr(1, myHash.length) != myHash)
		window.location.hash = myHash;
}';
	}
	echo $this->tabs->start('hikamarket_config_tab', $options);

	echo $this->tabs->panel(JText::_('MAIN'), 'config_main');
	{
		$this->setLayout('main');
		echo $this->loadTemplate();
	}

	if(hikamarket::level(1)) {
		echo $this->tabs->panel(JText::_('MARKET_OPTIONS'), 'config_market');
		{
			$this->setLayout('market');
			echo $this->loadTemplate();
		}
	}

	echo $this->tabs->panel(JText::_('YOUR_VENDOR'), 'config_vendor');
	{
		$params = new hikaParameter('');
		$params->set('configPanelIntegration', true);
		$js = '';
		echo hikamarket::getLayout('vendormarket', 'form', $params, $js);
		if(!empty($js)) {
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration($js);
		}
	}

	echo $this->tabs->panel(JText::_('ACCESS_LEVEL'), 'config_acl');
	{
		$this->setLayout('config_acl');
		echo $this->loadTemplate();
	}

	echo $this->tabs->panel(JText::_('LANGUAGES'), 'config_language');
	{
		$this->setLayout('languages');
		echo $this->loadTemplate();
	}

	echo $this->tabs->panel(JText::_('PRODUCT_TEMPLATES'), 'config_producttemplates');
	{
		$this->setLayout('product_template');
		echo $this->loadTemplate();
	}

	echo $this->tabs->end();

?>
	<div style="clear:both" class="clr"></div>
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
	} else {
		hikashop_loadJsLib('jquery');
?>
		t.win = jQuery(window);
		t.body = jQuery('body');
		t.navTop = jQuery('#adminForm').offset().top + 10;
		t.isFixed = 0;

		t.navIds = [
			jQuery('#menu_main'), jQuery('#menu_market')
		];
		t.saveIds = [
			jQuery('#menu-save-button-main'), jQuery('#menu-save-button-market')
		];
		t.scrollIds = [
			jQuery('#menu-scrolltop-main'), jQuery('#menu-scrolltop-market')
		];

		t.win.scroll(function(){t.processScroll(t);});
		t.processScroll();
<?php
	}
?>
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
<?php
	if(HIKASHOP_BACK_RESPONSIVE) {
?>
		jQuery("#config_"+tabName+"_tablink").tab("show");
		this.scrollToCust( hash );
<?php
	} else {
?>
		var childrens = $('hikamarket_config_tab').getChildren('dt'), elt = 0, j = 0;
		for (var i = 0; i < childrens.length; i++){
			var children = childrens[i];
			if(children.hasClass('tabs') || children.id.substr(0, children.id.indexOf('_'))){
				if(children.hasClass('config_'+tabName) || children.id == 'config_'+tabName){
					children.addClass('open').removeClass('closed');
					elt = j;
				}else{
					children.addClass('closed').removeClass('open');
				}
				j++;
			}
		}

		var tabsContent = $('hikamarket_config_tab').getNext('div');
		var tabChildrens = tabsContent.getChildren('dd');
		for (var i = 0; i < tabChildrens.length; i++){
			var childContent = tabChildrens[i];
			if(i == elt){
				childContent.style.display = 'block';
			}else{
				childContent.style.display = 'none';
			}
		}

		var d = document, elem = null;
		if(hash) elem = d.getElementById(hash);
		if(elem)
			window.scrollTo(0, elem.offsetTop);
		else
			window.scrollTo(0, 0);
	},
	processScroll: function() {
		var t = this, scrollTop = t.win.scrollTop();
		if(scrollTop >= t.navTop && !t.isFixed) {
			t.isFixed = 1;
			for(var i = 0; i < t.navIds.length; i++){
				t.navIds[i].addClass('navmenu-fixed');
				t.saveIds[i].removeClass('menu-save-button');
				t.scrollIds[i].removeClass('menu-scrolltop');
			}

		} else if(scrollTop <= t.navTop && t.isFixed) {
			t.isFixed = 0;
			for(var i = 0; i < t.navIds.length; i++){
				t.navIds[i].removeClass('navmenu-fixed');
				t.saveIds[i].addClass('menu-save-button');
				t.scrollIds[i].addClass('menu-scrolltop');
			}
		}
<?php
	}
?>
	},
	scrollToCust: function(name) {
		var d = document, elem = d.getElementById(name);
		if( !elem ) { window.scrollTo(0, 0); return; }
		var topPos = elem.offsetTop + 100;
		window.scrollTo(0, topPos);
	}
}
window.hikashop.ready( function(){ configWatcher.init(); });
</script>
