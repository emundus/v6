<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopSlidersHelper {
	var $ctrl = 'sliders';
	var $tabs = null;
	var $openPanel = false;
	var $mode = null;
	var $count = 0;
	var $name = '';
	var $options = null;

	function __construct() {
		if(HIKASHOP_J40) {
			$this->mode = 'bootstrap';
		} elseif(!HIKASHOP_J30) {
			$this->mode = 'sliders';
		} else {
			$app = JFactory::getApplication();
			if((hikashop_isClient('administrator') && HIKASHOP_BACK_RESPONSIVE) || (!hikashop_isClient('administrator') && HIKASHOP_RESPONSIVE)){
				$this->mode = 'bootstrap';
			}else{
				$this->mode = 'sliders';
			}
		}
	}

	function startPane($name) { return $this->start($name); }
	function startPanel($text, $id, $child = 0, $toOpen = 0) { return $this->panel($text, $id, $child, $toOpen); }
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
		if($this->mode == 'pane') {
			jimport('joomla.html.pane');
			if(!empty($this->options))
				$options = array_merge($options, $this->options);
			$this->tabs = JPane::getInstance('sliders', $options);
			$ret .= $this->tabs->startPane($name);
		} elseif($this->mode == 'sliders') {
			if(!empty($this->options))
				$options = array_merge($options, $this->options);
			$ret .= JHtml::_('sliders.start', $name, $options);
		} else {
			if(HIKASHOP_J40) {
				\Joomla\CMS\HTML\HTMLHelper::_('bootstrap.collapse', '#'.$name);
			}
			if($this->options == null)
				$this->options = $options;
			else
				$this->options = array_merge($this->options, $options);
			$this->name = $name;
			$this->count = 0;
			$ret .= '<div class="accordion" id="'.$name.'">';
		}
		return $ret;
	}

	function panel($text, $id, $child = 0, $toOpen = 0) {
		$ret = '';
		if($child) $child = 'has-child';
		else $child = '';

		if($this->mode == 'pane') {
			if($this->openPanel)
				$ret .= $this->tabs->endPanel();
			$ret .= $this->tabs->startPanel($text, $id);
			$this->openPanel = true;
		} elseif($this->mode == 'sliders') {
			$ret .= JHtml::_('sliders.panel', JText::_($text), $id);
		} else {
			if($this->openPanel)
				$ret .= $this->_closePanel();

			$open = '';
			$linkAttrib = '';
			$this->options['displayFirst'] = isset($this->options['displayFirst'])?$this->options['displayFirst']:1;
			if($toOpen || ($this->options['displayFirst'] && (isset($this->options['startOffset']) && $this->options['startOffset'] == $this->count || $this->count == 0))) {
				$open = 'in show';
				$linkAttrib = ' aria-expanded="true"';
			}
			$this->count++;

			$text = trim($text);
			if(preg_match('#<a .*>.*</a>#i', $text))
				$text = '</a>' . str_replace('</a>', '', $text);

			$ret .= '
<div class="accordion-group">
		<div class="accordion-heading '.$child.'">
			<h4>
				<a class="accordion-toggle" data-bs-toggle="collapse" data-toggle="collapse" data-parent="#'.$this->name.'" href="#'.$id.'"'.$linkAttrib.'>
					'.$text.'
				</a>
			</h4>
		</div>
		<div id="'.$id.'" class="accordion-body collapse '.$open.'" data-bs-parent="#'.$this->name.'">
			<div class="accordion-inner">
';
			$this->openPanel = true;
		}
		return $ret;
	}

	function _closePanel() {
		if(!$this->openPanel)
			return '';
		$this->openPanel = false;
		return '</div></div></div>';
	}

	function end() {
		$ret = '';
		if($this->mode == 'pane') {
			if($this->openPanel)
				$ret .= $this->tabs->endPanel();
			$ret .= $this->tabs->endPane();
		} elseif($this->mode == 'sliders') {
			$ret .= JHtml::_('sliders.end');
		} else {
			if($this->openPanel)
				$ret .= $this->_closePanel();
			$ret .= '</div>';
		}
		return $ret;
	}
}
