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
class hikashopTabsHelper {
	var $ctrl = 'tabs';
	var $tabs = null;
	var $openPanel = false;
	var $mode = null;
	var $data = array();
	var $options = null;
	var $name = '';

	function __construct() {
		if(!HIKASHOP_J30) {
			$this->mode = 'tabs';
		} else if(hikashop_isClient('administrator')) {
			$this->mode = 'hika_tabs';
		} else if(HIKASHOP_RESPONSIVE) {
			$this->mode = 'bootstrap';
		} else {
			$this->mode = 'tabs';
		}
	}

	function startPane($name) { return $this->start($name); }
	function startPanel($text, $id) { return $this->panel($text, $id); }
	function endPanel() { return ''; }
	function endPane() { return $this->end(); }

	function setOptions($options = array()) {
		if($this->options == null)
			$this->options = $options;
		else
			$this->options = array_merge($this->options, $options);
	}

	function start($name, $options = array()) {
		$ret = '';
		switch($this->mode) {
			case 'pane':
				jimport('joomla.html.pane');
				if(!empty($this->options))
					$options = array_merge($options, $this->options);
				$this->tabs = JPane::getInstance('tabs', $options);
				$ret .= $this->tabs->startPane($name);
				break;
			case 'tabs':
				if(!empty($this->options))
					$options = array_merge($options, $this->options);
				$ret .= JHtml::_('tabs.start', $name, $options);
				break;
			default:
				$this->name = $name;
				if($this->options == null)
					$this->options = $options;
				else
					$this->options = array_merge($this->options, $options);
				break;
		}
		return $ret;
	}

	function panel($text, $id) {
		$ret = '';
		switch($this->mode) {
			case 'pane':
				if($this->openPanel)
					$ret .= $this->tabs->endPanel();
				$ret .= $this->tabs->startPanel($text, $id);
				$this->openPanel = true;
				break;
			case 'tabs':
				$ret .= JHtml::_('tabs.panel', JText::_($text), $id);
				break;
			default:
				if($this->openPanel)
					$this->_closePanel();

				$obj = new stdClass();
				$obj->text = $text;
				$obj->id = $id;
				$obj->data = '';
				$this->data[] = $obj;
				ob_start();
				$this->openPanel = true;
				break;
		}
		return $ret;
	}

	function _closePanel() {
		if(!$this->openPanel)
			return;
		$panel = end($this->data);
		$panel->data .= ob_get_clean();
		$this->openPanel = false;
	}

	function end() {
		$ret = '';
		switch($this->mode) {
			case 'pane':
				if($this->openPanel)
					$ret .= $this->tabs->endPanel();
				$ret .= $this->tabs->endPane();
				break;
			case 'tabs' :
				$ret .= JHtml::_('tabs.end');
				break;
			case 'hika_tabs':
				if($this->openPanel)
					$this->_closePanel();
				$ret .= '<ul class="hika_tabs" rel="tabs:'.$this->name.'_" id="'.$this->name.'">'."\r\n";
				foreach($this->data as $k => $data) {
					$active = '';
					if((isset($this->options['startOffset']) && $this->options['startOffset'] == $k) || $k == 0)
						$active = ' class="active"';
					$ret .= '	<li' . $active.'><a href="#' . $data->id . '" rel="tab:'.$data->id.'" id="'.$this->name.'_'.$data->id.'_title" onclick="return window.hikashop.switchTab(this);">' . JText::_($data->text) . '</a></li>'."\r\n";
				}
				$ret .= '</ul>'."\r\n".'<div style="clear:both;" class="clr"></div><div class="hika_tabs_content">'."\r\n";
				$first = null;
				foreach($this->data as $k => $data) {
					if(is_null($first))
						$first = $this->name.'_' . $data->id;
					$ret .= '	<div class="hika_tabs_pane" id="'.$this->name.'_' . $data->id . '">'."\r\n".$data->data."\r\n".'	</div>'."\r\n";
					unset($data->data);
				}
				$ret .= '</div>
<script>
window.hikashop.ready( function(){
	window.hikashop.switchTab(document.getElementById(\''.$first.'_title\'));

});
</script>';
				unset($this->data);
				break;
			default:
				static $jsInit = false;

				if($this->openPanel)
					$this->_closePanel();

				$classes = '';
				if(isset($this->options['useCookie']) && $this->options['useCookie']) {
					$classes .= ' nav-remember';
				}

				$ret .= '<div><ul class="nav nav-tabs'.$classes.'" id="'.$this->name.'" style="width:100%;">'."\r\n";
				foreach($this->data as $k => $data) {
					$active = '';
					if((isset($this->options['startOffset']) && $this->options['startOffset'] == $k) || $k == 0)
						$active = ' class="active"';
					$ret .= '	<li' . $active.'><a href="#' . $data->id . '" id="'.$data->id.'_tablink" data-toggle="tab">' . JText::_($data->text) . '</a></li>'."\r\n";
				}
				$ret .= '</ul>'."\r\n".'<div class="tab-content">'."\r\n";
				foreach($this->data as $k => $data) {
					$active = '';
					if((isset($this->options['startOffset']) && $this->options['startOffset'] == $k) || $k == 0)
						$active = ' active';
					$ret .= '	<div class="tab-pane' . $active.'" id="' . $data->id . '">'."\r\n".$data->data."\r\n".'	</div>'."\r\n";
					unset($data->data);
				}
				$ret .= '</div></div>';
				unset($this->data);

				if(!$jsInit) {
					$jsInit = true;
					$js = 'jQuery(document).ready(function (){
		jQuery("ul.nav-remember").each(function(nav){
			var id = jQuery(this).attr("id");
			jQuery("#" + id + " a[data-toggle=\"tab\"]").on("shown", function (e) {
				if(localStorage) {
					localStorage.setItem("hikashop-lastTab-"+id, jQuery(e.target).attr("id"));
				} else {
					var expire = new Date(); expire.setDate(expire.getDate() + 5);
					document.cookie = "hikashop-lastTab-"+id+"="+escape(jQuery(e.target).attr("id"))+"; expires="+expire;
				}
				window.hikashop.checkConsistency();
			});
			var lastTab = null;
			if(localStorage) {
				lastTab = localStorage.getItem("hikashop-lastTab-"+id);
			} else {
				if(document.cookie.length > 0 && document.cookie.indexOf("hikashop-lastTab-"+id+"=") != -1) {
					var s = "hikashop-lastTab-"+id+"=", o = document.cookie.indexOf(s) + s.length, e = document.cookie.indexOf(";",o);
					if(e == -1) e = document.cookie.length;
					lastTab = unescape(document.cookie.substring(o, e));
				}
			}
			if (lastTab) {
				jQuery("#"+lastTab).tab("show");
			}
		});
	});';
					$doc = JFactory::getDocument();
					$doc->addScriptDeclaration($js);
				}
				break;
		}
		return $ret;
	}
}
