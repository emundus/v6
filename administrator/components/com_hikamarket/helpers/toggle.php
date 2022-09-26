<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikamarketToggleHelper{
	private $ctrl = 'toggle';
	private $extra = '';
	private $token = '';

	public function __construct() {
		$this->token = hikamarket::getFormToken(); // '&'.hikamarket::getFormToken().'=1';
	}

	private function getToggle($column, $table = ''){
		$params = new stdClass();
		$params->mode = 'pictures';
		$params->values = array(
			0 => 1,
			1 => 0
		);
		$params->aclass = array(
			0 => 'unpublish',
			1 => 'publish'
		);
		return $params;
	}

	public function toggle($id, $value, $table, $extra = null) {
		static $jsIncluded = false;

		$column = substr($id, 0, strpos($id, '-'));
		$params = $this->getToggle($column, $table);
		$newValue = $params->values[$value];

		if(!$jsIncluded && ($params->mode == 'pictures' || $params->mode == 'class')) {
			$jsIncluded = true;
			$js = 'function hikamToggleElem(el,id,v,t,e){'."\r\n".
				'var w=window, d=document, o=w.Oby, el=el.parentNode;'."\r\n".
				'if(!el) return; el.className="toggle_onload";'.
				'var url="'.hikamarket::completeLink($this->ctrl,true,true).'";'."\r\n".
				'var data=o.encodeFormData({"task":id,"value":v,"table":t,"'.$this->token.'":1});'. // &task={TASK-}&value={VALUE-}&table={TABLE-}
				'o.xRequest(url,{update:el,mode:"POST",data:data},function(x,p){el.className="toggle_loading";});'."\r\n".
				'}';
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration($js);
		}

		if($params->mode == 'pictures') {
			$desc = empty($params->description[$value]) ? '' : $params->description[$value];
			if(empty($params->pictures)) {
				$text = ' ';
				$class='class="'.$params->aclass[$value].'"';
			} else {
				$text = '<img src="'.$params->pictures[$value].'"/>';
				$class = '';
			}
			$return = '<a href="javascript:void(0);" '.$class.' onclick="hikamToggleElem(this,\''.$id.'\',\''.$newValue.'\',\''.$table.'\')" title="'.str_replace('"','&quot;',$desc).'">'.$text.'</a>';
			if(hikaInput::get()->getCmd('ctrl') != 'toggle')
				$return = '<div class="toggle_loading">' . $return . '</div>';
			return $return;
		}

		if($params->mode == 'class') {
			$desc = empty($params->description[$value]) ? '' : $params->description[$value];
			$return = '<a href="javascript:void(0);" onclick="hikamToggleElem(this,\''.$id.'\',\''.$newValue.'\',\''.$table.'\',\''.urlencode($extra['color']).'\');" title="'.str_replace('"','&quot;',$desc).'"><div class="'. $params->class[$value] .'" style="background-color:'.$extra['color'].'">';
			if(!empty($extra['tooltip']))
				$return .= JHTML::_('tooltip', $extra['tooltip'], '','','&nbsp;&nbsp;&nbsp;&nbsp;');
			$return .= '</div></a>';
			if(hikaInput::get()->getCmd('ctrl') != 'toggle')
				$return = '<div class="toggle_loading">' . $return . '</div>';
			return $return;
		}

		return '';
	}

	public function display($column, $value) {
		$params = $this->getToggle($column);
		if(empty($params->pictures)) {
			return '<div class="toggle_loading"><a class="'.$params->aclass[$value].'" href="#" onclick="return false;" style="cursor:default;"></a></div>';
		}
		return '<img src="'.$params->pictures[$value].'"/>';
	}

	public function delete($lineId, $elementids, $table, $confirm = false, $text = '') {
		static $jsIncluded = false;

		if(!$jsIncluded) {
			$jsIncluded = true;
			$js = 'function hikamDeleteElem(el,id,v,t,r){'."\r\n".
				'var w=window, d=document, o=w.Oby, el=el.parentNode;'."\r\n".
				'if(r && !confirm("'.JText::_('HIKA_VALIDDELETEITEMS',true).'")) return false;'."\r\n".
				'var url="'.hikamarket::completeLink($this->ctrl.$this->extra, true, false, true).'";'.
				'var data=o.encodeFormData({"task":"delete","value":v,"table":t,"'.$this->token.'":1});'.
				'o.xRequest(url,{mode:"POST",data:data},function(x,p){if(x.responseText != "1") { alert(x.responseText); return; } var e = d.getElementById(id); if(e) e.style.display="none"; else el.style.display="none";});'."\r\n".
				'}';
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration($js);
		}

		if(empty($text))
			$text = '<i class="far fa-trash-alt"></i>';
		return '<a href="javascript:void(0);" onclick="hikamDeleteElem(this,\''.$lineId.'\',\''.$elementids.'\',\''.$table.'\','. ($confirm ? 'true' : 'false').')">'.$text.'</a>';
	}
}
