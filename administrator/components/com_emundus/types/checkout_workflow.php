<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.0.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopCheckout_workflowType {
	protected $checkoutlist = null;

	protected function load() {
		if(!empty($this->checkoutlist))
			return $this->checkoutlist;

		$this->checkoutlist = array(
			'login' => JText::_('HIKASHOP_CHECKOUT_LOGIN'),
			'address' => JText::_('HIKASHOP_CHECKOUT_ADDRESS'),
			'shipping' => JText::_('HIKASHOP_CHECKOUT_SHIPPING'),
			'payment' => JText::_('HIKASHOP_CHECKOUT_PAYMENT'),
			'coupon' => JText::_('HIKASHOP_CHECKOUT_COUPON'),
			'cart' => JText::_('HIKASHOP_CHECKOUT_CART'),
			'cartstatus' => JText::_('HIKASHOP_CHECKOUT_CART_STATUS'),
			'status' => JText::_('HIKASHOP_CHECKOUT_STATUS'),
			'fields' => JText::_('HIKASHOP_CHECKOUT_FIELDS'),
			'terms' => JText::_('HIKASHOP_CHECKOUT_TERMS')
		);

		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashopshipping');
		JPluginHelper::importPlugin('hikashoppayment');
		$dispatcher = JDispatcher::getInstance();

		$list = array();
		$dispatcher->trigger('onCheckoutStepList', array(&$list));
		if(!empty($list)) {
			foreach($list as $k => $v) {
				if(!isset($this->checkoutlist[$k]))
					$this->checkoutlist[$k] = $v;
			}
		}
		return $this->checkoutlist;
	}

	public function displayTextarea($map, $value) {
		$html = '<textarea class="inputbox" name="'.$map.'" cols="30" rows="5">'.$value.'</textarea>';
		return $html;
	}

	public function displayLegacy($map, $value) {
		$checkoutlist = $this->load();

		$id = 'checkworkflow_'. trim(str_replace(array('][','[',']'), '_', $map), '_');

		$html = '<textarea class="inputbox" name="'.$map.'" id="'.$id.'" cols="30" rows="5">'.$value.'</textarea>';

		hikashop_loadJsLib('jquery');

		$html .= '<div class="checkout_workflow_zone" style="width:100%">' .
			'<ul id="'.$id.'_delete" class="checkout_trash">' .
			'</ul>' .
			'<ul class="checkout_items">';

		foreach($checkoutlist as $k => $v) {
			$html .= '<li class="checkoutElem" rel="'.$k.'">'.$v.'</li>';
		}

		$html .= '</ul>' .
			'<div style="clear:both">';

		$workflow = explode(',', $value);
		$checkoutRel = 0;
		if(!empty($workflow)) {
			foreach($workflow as $flow) {
				if( $flow == 'end')
					continue;

				$html.= '<ul class="checkout_step" rel="'.$checkoutRel.'" id="'.$id.'_step_'.$checkoutRel.'">';
				$checkoutRel++;
				$flow = explode('_', $flow);
				foreach($flow as $f) {
					if(isset($checkoutlist[$f])) {
						$html .= '<li class="checkoutElem" rel="'.$f.'">'. $checkoutlist[$f] .'</li>';
					}
				}
				$html .= '</ul>';
			}
		}

		$html .= '<ul class="checkout_step" rel="'.$checkoutRel.'" id="'.$id.'_step_'.$checkoutRel.'"></ul>' .
			'</div>'.
			'<div style="clear:both"></div>'.
			'</div>';

		$this->initLegacyJS($id, $checkoutRel);
		return $html;
	}

	protected function initLegacyJS($id, $checkoutRel) {
		static $init = false;

		$js = '';
		if(!$init) {
			$js = "
var checkoutWorkflowHelper = {
	el_id: '',
	maxRel: 0,
	init: function(id, maxRel) {
		var t = this;
		t.maxRel = maxRel;
		t.el_id = id;
		jQuery(\"ul.checkout_trash\").droppable({
			accept: \"ul.checkout_step li\",
			hoverClass: \"drophover\",
			drop: function(event, ui) { ui.draggable.remove(); }
		});
		jQuery(\"ul.checkout_items li\").draggable({
			dropOnEmpty: true,
			connectToSortable: \"ul.checkout_step\",
			helper: \"clone\",
			revert: \"invalid\"
		}).disableSelection();
		jQuery(\"ul.checkout_step\").sortable({
			revert: true,
			dropOnEmpty: true,
			connectWith: \"ul.checkout_step, ul.checkout_trash\",
			update: function(event, ui) { t.serialize(); }
		}).disableSelection();
		jQuery('#'+t.el_id).hide();
	},
	serialize: function() {
		var t = this, max = 0, data = '';
		jQuery(\"ul.checkout_step li\").each(function(index, el) {
			var p = parseInt(jQuery(el).parent().attr(\"rel\"), r = jQuery(el).attr(\"rel\"));
			if(p > max) {
				max = p;
				if( data != '')
					data += ',';
			} else if( data != '') {
				data += '_';
			}
			data += r;
		});
		data += '_confirm,end';
		jQuery('#'+t.el_id).val(data);

		if(max == this.maxRel) {
			this.maxRel++;
			var t = this;
			jQuery('<ul class=\"checkout_step\" rel=\"' + this.maxRel + '\" id=\"'+t.el_id+'_step_' + this.maxRel + '\"></ul>').insertAfter('#'+t.el_id+'_step_' + (this.maxRel-1) ).sortable({
				revert: true,
				dropOnEmpty: true,
				connectWith: \"ul.checkout_step, ul.checkout_trash\",
				update: function(event, ui) { t.serialize(); }
			});
			jQuery(\"ul.checkout_step\").sortable('refresh');
		}
		if(max < (this.maxRel - 1)) {
			for(var i = this.maxRel; i > (max+1); i--) {
				jQuery('#'+t.el_id+'_step_' + i).sortable(\"destroy\").remove();
				jQuery(\"ul.checkout_step\").sortable('refresh');
			}
			this.maxRel = max + 1;
		}
	}
};
";
			$init = true;
		}

		$js .= '
window.hikashop.ready(function(){ checkoutWorkflowHelper.init("'.$id.'",'.$checkoutRel.'); });
';

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
	}
}
