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
class hikashopCheckout_workflowType {
	protected $checkoutlist = null;

	protected function load() {
		if(!empty($this->checkoutlist))
			return $this->checkoutlist;

		$this->checkoutlist = array(
			'login' => array(
				'name' => JText::_('HIKASHOP_CHECKOUT_LOGIN'),
				'params' => array(),
			),
			'address' => array(
				'name' => JText::_('HIKASHOP_CHECKOUT_ADDRESS'),
				'params' => array(),
			),
			'shipping' => array(
				'name' => JText::_('HIKASHOP_CHECKOUT_SHIPPING'),
				'params' => array(),
			),
			'payment' => array(
				'name' => JText::_('HIKASHOP_CHECKOUT_PAYMENT'),
				'params' => array(),
			),
			'coupon' => array(
				'name' => JText::_('HIKASHOP_CHECKOUT_COUPON'),
				'params' => array(),
			),
			'cart' => array(
				'name' => JText::_('HIKASHOP_CHECKOUT_CART'),
				'params' => array(),
			),
			'cartstatus' => array(
				'name' => JText::_('HIKASHOP_CHECKOUT_CART_STATUS'),
				'params' => false, 'legacy' => true,
			),
			'status' => array(
				'name' => JText::_('HIKASHOP_CHECKOUT_STATUS'),
				'params' => array(),
			),
			'fields' => array(
				'name' => JText::_('HIKASHOP_CHECKOUT_FIELDS'),
				'params' => array(),
			),
			'terms' => array(
				'name' => JText::_('HIKASHOP_CHECKOUT_TERMS'),
				'params' => array(),
			),
			'text' => array(
				'name' => JText::_('HIKASHOP_CHECKOUT_TEXT'),
				'params' => array(), 'legacy' => false,
			),
			'separator' => array(
				'name' => JText::_('HIKASHOP_CHECKOUT_SEPARATOR'),
				'params' => array(), 'legacy' => false,
			),
		);

		foreach($this->checkoutlist as $k => &$v) {
			if(!empty($v['legacy']) || $v['params'] === false)
				continue;

			$helper = hikashop_get('helper.checkout-' . $k);
			if(empty($helper))
				continue;

			$v['params'] = $helper->getParams();

			unset($helper);

			if(empty($v['params']))
				continue;

			if(is_array($v['params'])) {
				foreach($v['params'] as &$p) {
					if(is_array($p) && !empty($p['name']))
						$p['name'] = JText::_($p['name']);
				}
			}
			unset($p);
		}
		unset($v);

		JPluginHelper::importPlugin('hikashop');
		JPluginHelper::importPlugin('hikashopshipping');
		JPluginHelper::importPlugin('hikashoppayment');
		$app = JFactory::getApplication();

		$list = array();
		$app->triggerEvent('onCheckoutStepList', array(&$list));
		if(!empty($list)) {
			foreach($list as $k => $v) {
				if(isset($this->checkoutlist[$k]))
					continue;

				if(is_string($v))
					$v = array('name' => $v, 'params' => array());
				$this->checkoutlist[$k] = $v;
			}
		}

		return $this->checkoutlist;
	}

	public function &getCheckoutData() {
		$this->load();
		return $this->checkoutlist;
	}

	public function displayTextarea($map, $value) {
		$html = '<textarea class="inputbox" name="'.$map.'" cols="30" rows="5">'.$value.'</textarea>';
		return $html;
	}

	public function display($map, $value) {
		$checkoutlist = $this->load();
		$id = 'checkworkflow_'. trim(str_replace(array('][','[',']'), '_', $map), '_');

		$original_value = $value;
		$workflow = json_decode($value, true);
		if($workflow === null) {
			$workflow = $this->convertLegacyData($value);
			$value = json_encode($workflow);
		}

		foreach($checkoutlist as $k => $el) {
			if(!empty($el['legacy']))
				unset($checkoutlist[$k]);
		}

		$this->initJS($id, $checkoutlist);

		$html = '
<input type="hidden" name="'.$map.'" id="'.$id.'" value="'.$this->escape($value).'"/>
<div id="'.$id.'_container" class="checkout_edition" data-checkout="container" data-checkout-id="'.$id.'">';

		foreach($workflow['steps'] as $step_id => $step_content) {
			list($begin, $end) = $this->getStepTemplate((int)$step_id, @$step_content['name'], ($step_id < count($workflow['steps'])-1));
			$html .= $begin;
			unset($begin);

			foreach($step_content['content'] as $content) {
				if(!isset($checkoutlist[ $content['task'] ]))
					continue;

				$html .= str_replace(
					array(
						'{{TASK}}',
						'{{NAME}}',
						'{{PARAMS}}'
					),
					array(
						$this->escape($content['task']),
						$checkoutlist[$content['task']]['name'],
						$this->processParams( $checkoutlist[$content['task']]['params'], @$content['params'] )
					),
					$this->getBlockTemplate()
				);
			}

			$html .= $end;
		}

		$html .= '
	<div class="checkout_edition_add_step">
		<a href="#addStep" class="btn btn-primary" data-checkout-id="'.$id.'" onclick="return window.checkoutWorkflowEditor.addStep(this);">'.JText::_('HK_ADD_STEP').'</a>
	</div>
		';
		$html .= '
</div>';
		return $html;
	}

	public function newBlock($name) {
		$checkoutlist = $this->load();
		foreach($checkoutlist as $k => $el) {
			if(!empty($el['legacy']))
				unset($checkoutlist[$k]);
		}

		if(!isset($checkoutlist[$name]))
			return '';
		$null = null;
		return trim(str_replace(
			array(
				'{{TASK}}',
				'{{NAME}}',
				'{{PARAMS}}'
			),
			array(
				$this->escape($name),
				$checkoutlist[$name]['name'],
				$this->processParams( $checkoutlist[$name]['params'], $null )
			),
			$this->getBlockTemplate()
		));
	}

	public function newStep($num) {
		return trim(implode('', $this->getStepTemplate($num)));
	}

	protected function getStepTemplate($id = null, $name = '', $add = true) {
		if($id === null) {
			$id = '{ID}';
			$id_inc = '{NUM}';
		} else {
			$id = (int)$id;
			$id_inc = ($id + 1);
		}

		$end = '
		</div>
	</div>';

		if($add) {
			$checkoutlist = $this->load();
			foreach($checkoutlist as $k => $el) {
				if(!empty($el['legacy']))
					unset($checkoutlist[$k]);
			}

			$values = array();
			foreach($checkoutlist as $key => $v) {
				$values[$key] = JHTML::_('select.option', $key, $v['name']);
			}

			$end = '
<div class="checkout_content_block checkout_content_new_block" data-checkout="add">
	<div class="checkout_content_block_main">
		<span class="checkout_content_block_title checkout_content_new_block_title">'.JText::_('NEW_BLOCK').'</span>
		'.JHTML::_('select.genericlist', $values, '', 'class="custom-select" data-checkout="addlist"', 'value', 'text', '').'<div class="checkout_content_add_block">
		<a href="#addblock" class="btn btn-primary" onclick="return window.checkoutWorkflowEditor.addBlock(this);">'.JText::_('HK_ADD_CHECKOUT_BLOCK').'</a></div>
	</div>
</div>'.$end;
		}

		return array('
	<div class="checkout_step_block" data-checkout="step" data-checkout-step="'.$id.'">
		<span class="checkout_step_title">'.JText::sprintf('STEP_X', '<span data-checkout="num">'.$id_inc.'</span>').'</span>
		<input type="text" value="'.$this->escape(@$name).'" data-checkout-step-name="'.$id.'" data-placeholder="'.JText::_('HIKASHOP_CHECKOUT_END').'" onblur="window.checkoutWorkflowEditor.onChange(this);"/>
		<a href="#delete" class="checkout_content_step_delete" title="'.JText::_('DELETE_THIS_STEP').'" onclick="return window.checkoutWorkflowEditor.stepDelete(this);"></a>
		<div class="checkout_step_content" data-consistencyheight=".checkout_content_block">',$end
		);
	}

	protected function getBlockTemplate() {
		return '
<div class="checkout_content_block {{TASK}}" data-checkout="content" data-checkout-content="{{TASK}}">
	<a href="#up" class="checkout_content_block_nav checkout_content_block_up" title="'.JText::_('SWAP_WITH_PREVIOUS_BLOCK').'" onclick="return window.checkoutWorkflowEditor.blockUp(this);"></a>
	<a href="#previous" class="checkout_content_block_nav checkout_content_block_previous" title="'.JText::_('MOVE_TO_PREVIOUS_STEP').'" onclick="return window.checkoutWorkflowEditor.blockPrevious(this);"></a>
	<div class="checkout_content_block_main">
		<span class="checkout_content_block_title">{{NAME}}</span>
		<a href="#delete" class="checkout_content_block_delete" title="'.JText::_('DELETE_THIS_BLOCK').'" onclick="return window.checkoutWorkflowEditor.blockDelete(this);"></a>
		<div class="checkout_block_params">{{PARAMS}}</div>
	</div>
	<a href="#next" class="checkout_content_block_nav checkout_content_block_next" title="'.JText::_('MOVE_TO_NEXT_STEP').'" onclick="return window.checkoutWorkflowEditor.blockNext(this);"></a>
	<a href="#down" class="checkout_content_block_nav checkout_content_block_down" title="'.JText::_('SWAP_WITH_NEXT_BLOCK').'" onclick="return window.checkoutWorkflowEditor.blockDown(this);"></a>
</div>';
	}

	protected function escape($value) {
		return htmlentities((string)$value, ENT_QUOTES, 'UTF-8');
	}

	protected function initJS($id, $checkoutlist, $templates = array()) {
		static $init = false;

		$doc = JFactory::getDocument();
		$init_workflow = '
window.hikashop.ready(function(){ checkoutWorkflowEditor.init("'.$id.'"); });
';
		if($init) {
			$doc->addScriptDeclaration( $init_workflow );
			return;
		}

		$urls = array(
			'addblock' => hikashop_completeLink('config&task=checkout_newblock', true, true),
			'addstep' => hikashop_completeLink('config&task=checkout_newstep', true, true),
		);

		$js = '
window.checkoutBlocks = '.json_encode($checkoutlist).';
window.checkoutWorflowUrls = '.json_encode($urls).';
';

		$doc->addScriptDeclaration( $js . $init_workflow );
		$doc->addScript(HIKASHOP_JS . 'checkoutworkflow.js?v='.HIKASHOP_RESSOURCE_VERSION);
		$init = true;
	}

	public function processParams($structure, $data) {
		if(empty($structure))
			return '<span class="checkout_content_block_no_options">'.JText::_('NO_OPTIONS').'</span>';

		if(!is_array($structure))
			return '<span class="checkout_content_block_text">'.JText::_($structure).'</span>';

		$ret = '
<dl class="checkout_content_block_column">';
		foreach($structure as $k => $s) {
			if($s['type'] == 'separator') {
				$ret .= '</dl><dl class="checkout_content_block_column checkout_content_block_second_col">';
				continue;
			}
			$attributes = (!empty($s['tooltip'])) ? ' '.trim($this->docTip($s['tooltip'])) : '';
			$showon = (!empty($s['showon'])) ? ' data-showon-key="'.$this->escape(@$s['showon']['key']).'" data-showon-values="'.$this->escape(implode(',', @$s['showon']['values'])).'"' : '';

			$ret .= '
	<dt'.$attributes.$showon.'>'.$s['name'].'</dt>
	<dd'.$showon.'>';
			$ret .= $this->processParam($s, $k, $data);
			$ret .= '
	</dd>';
		}
		$ret .= '
</dl>';

		return $ret;
	}

	protected function getDoc($key) {
		$namekey = 'HK_CONFIG_' . strtoupper(trim($key));
		$ret = JText::_($namekey);
		if($ret == $namekey) {
			return '';
		}
		return $ret;
	}

	protected function docTip($key) {
		$ret = $this->getDoc($key);
		if(empty($ret))
			return '';
		return 	' data-toggle="hk-tooltip" data-title="'.htmlspecialchars($ret, ENT_COMPAT, 'UTF-8').'"';
	}

	protected function processParam(&$s, $k, &$data) {
		$ret = '';
		$v = isset($data[$k]) ? $data[$k] : @$s['default'];
		$uuid = uniqid();
		$name = 'hkCheckout['.$uuid.']';
		$id = 'hkCheckout_'.$uuid;
		switch($s['type']) {
			case 'boolean':
				$ret .= JHtml::_('hikaselect.booleanlist', $name, 'data-checkout-param="'.$k.'" onchange="window.checkoutWorkflowEditor.onChange(this);"', $v);
				break;
			case 'inherit':
				$extraValues = null;
				if(isset($s['values']))
					$extraValues = $s['values'];
				$ret .= JHtml::_('hikaselect.inheritradiolist', $name, $v, $extraValues, 'data-checkout-param="'.$k.'" onchange="window.checkoutWorkflowEditor.onChange(this);"');
				break;
			case 'radio':
				if(!isset($s['values']))
					break;
				$ret .= JHTML::_('hikaselect.radiolist',  $s['values'], $name, 'data-checkout-param="'.$k.'" onclick="window.checkoutWorkflowEditor.onChange(this);"', 'value', 'text', $v);
				break;
			case 'list':
				if(!isset($s['values']))
					break;
				$ret .= JHTML::_('select.genericlist',  $s['values'], $name, 'class="inputbox no-chzn" data-checkout-param="'.$k.'" onchange="window.checkoutWorkflowEditor.onChange(this);"', 'value', 'text', $v);
				break;
			case 'namebox':
				if(!isset($s['namebox']))
					break;
				$nameboxType = hikashop_get('type.namebox');
				if(!isset($s['select']))
					$s['select'] = hikashopNameboxType::NAMEBOX_SINGLE;
				if(!isset($s['namebox_params']))
					$s['namebox_params'] = array(
						'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
						'returnOnEmpty' => false,
					);
				$s['namebox_params']['attributes'] = 'data-checkout-param="'.$k.'" data-checkout-type="namebox"';
				$ret .= $nameboxType->display(
					$name,
					$v,
					$s['select'],
					$s['namebox'],
					$s['namebox_params']
				).'<script>window.hikashop.ready(function(){'.
					'var fct=function(p){window.checkoutWorkflowEditor.onChange("'.$id.'");}, n=window.oNameboxes["'.$id.'"];'.
					'if(n){n.register("set",fct);n.register("unset",fct);}'.
					'});</script>';
				break;
			case 'group':
				if(empty($s['data']))
					break;
				foreach($s['data'] as $key => $one){
					$ret .= $this->processParam($one, $key, $data);
				}
				break;
			case 'html':
				$ret .= @$s['html'];
				break;
			case 'textarea':
				$ret .= '<textarea data-checkout-param="'.$k.'" '.@$s['attributes'].' onchange="window.checkoutWorkflowEditor.onChange(this);" >'.$v.'</textarea>';
				break;
			case 'text':
			default:
				$ret .= '<input type="text" data-checkout-param="'.$k.'" value="'.$v.'" '.@$s['attributes'].' onchange="window.checkoutWorkflowEditor.onChange(this);" />';
				break;
		}
		return $ret;
	}

	protected function convertLegacyData($checkout_config) {
		$legacy_steps = explode(',', $checkout_config);

		$checkout_workflow = array(
			'steps' => array()
		);
		foreach($legacy_steps as $steps) {
			$steps = explode('_', $steps);
			$content = array();
			foreach($steps as $step) {
				$c = array('task' => $step);
				if($step == 'cartstatus') {
					$c['task'] = 'cart';
					$c['params'] = array('readonly' => true);
				}
				$content[] = $c;
			}
			$checkout_workflow['steps'][] = array(
				'content' => $content
			);
		}
		return $checkout_workflow;
	}

	public function displayLegacy($map, $value) {
		$checkoutlist = $this->load();
		foreach($checkoutlist as $k => $el) {
			if(isset($el['legacy']) && $el['legacy'] === false)
				unset($checkoutlist[$k]);
		}

		$id = 'checkworkflow_'. trim(str_replace(array('][','[',']'), '_', $map), '_');

		$html = '<textarea class="inputbox" name="'.$map.'" id="'.$id.'" cols="30" rows="5">'.$value.'</textarea>';

		hikashop_loadJsLib('jquery');

		$html .= '<div class="checkout_workflow_zone" style="width:100%">' .
			'<ul id="'.$id.'_delete" class="checkout_trash">' .
			'</ul>' .
			'<ul class="checkout_items">';

		foreach($checkoutlist as $k => $v) {
			$n = (is_array($v) ? $v['name'] : $v);
			$html .= '<li class="checkoutElem" rel="'.$k.'">'.$n.'</li>';
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
					if(!isset($checkoutlist[$f]))
						continue;
					$n = (is_array($checkoutlist[$f]) ? $checkoutlist[$f]['name'] : $checkoutlist[$f]);
					$html .= '<li class="checkoutElem" rel="'.$f.'">'. $n .'</li>';
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
		jQuery('ul.checkout_trash').droppable({
			accept: 'ul.checkout_step li',
			hoverClass: 'drophover',
			drop: function(event, ui) { ui.draggable.remove(); }
		});
		jQuery('ul.checkout_items li').draggable({
			dropOnEmpty: true,
			connectToSortable: 'ul.checkout_step',
			helper: 'clone',
			revert: 'invalid'
		}).disableSelection();
		jQuery('ul.checkout_step').sortable({
			revert: true,
			dropOnEmpty: true,
			connectWith: 'ul.checkout_step, ul.checkout_trash',
			update: function(event, ui) { t.serialize(); }
		}).disableSelection();
		jQuery('#'+t.el_id).hide();
	},
	serialize: function() {
		var t = this, max = 0, data = '';
		jQuery('ul.checkout_step li').each(function(index, el) {
			var p = parseInt(jQuery(el).parent().attr('rel'), r = jQuery(el).attr('rel'));
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
				connectWith: 'ul.checkout_step, ul.checkout_trash',
				update: function(event, ui) { t.serialize(); }
			});
			jQuery('ul.checkout_step').sortable('refresh');
		}
		if(max < (this.maxRel - 1)) {
			for(var i = this.maxRel; i > (max+1); i--) {
				jQuery('#'+t.el_id+'_step_' + i).sortable('destroy').remove();
				jQuery('ul.checkout_step').sortable('refresh');
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
