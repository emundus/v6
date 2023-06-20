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
class hikashopToggleHelper{
	var $ctrl = 'toggle';
	var $extra = '';
	var $token = '';

	function __construct(){
		$this->token = '&'.hikashop_getFormToken().'=1';
	}

	function _getToggle($column) {
		$params = new stdClass();
		$params->mode = 'pictures';
		if(!HIKASHOP_J30){
			$params->aclass = array(0=>'grid_false',1=>'grid_true',-2=>'grid_false');
		} else {
			$params->aclass = array(0=>'icon-unpublish',1=>'icon-publish',-2=>'icon-unpublish');
		}
		$params->values = array(0=>1,1=>0,-2=>1);
		return $params;
	}

	function toggle($id,$value,$table,$extra = null){
		$column = substr($id,0,strpos($id,'-'));
		$params = $this->_getToggle($column);
		$newValue = $params->values[$value];
		$jsparams = '';
		$url = '';
		$values = '';
		if(!empty($extra)){
			foreach($extra as $k => $v){
				$jsparams .= ', '.$k;
				$url .= "+'&extra[".$k."]='+".$k;
				$values .= ', \''.urlencode($v).'\'';
			}
		}
		if($params->mode == 'pictures'){
			static $pictureincluded = false;
			if(!$pictureincluded){
				$pictureincluded = true;
				$js = "function joomTogglePicture(id, newvalue, table ".$jsparams."){
					var mydiv = document.getElementById(id);
					mydiv.className = 'onload';
					window.Oby.xRequest('index.php?option=com_hikashop&tmpl=component&ctrl=".$this->ctrl.$this->token."&task='+id+'&value='+newvalue+'&table='+table".$url.", {update: mydiv, mode:'GET'}, function(xhr){ mydiv.className = 'loading'; });
				}";

				$doc = JFactory::getDocument();
				$doc->addScriptDeclaration( $js );
			}
			$desc = empty($params->description[$value]) ? '' : $params->description[$value];
			if(empty($params->pictures)){
				$text = ' ';
				$class='class="'.$params->aclass[$value].'"';
			}else{
				$text = '<img src="'.$params->pictures[$value].'"/>';
				$class = '';
			}
			return '<a href="javascript:void(0);" '.$class.' onclick="joomTogglePicture(\''.$id.'\', \''.$newValue.'\', \''.$table.'\''.$values.');" title="'.$desc.'">'.$text.'</a>';
		}elseif($params->mode == 'class'){
			static $classincluded = false;
			if(!$classincluded){
				$classincluded = true;
				$js = "function joomToggleClass(id, newvalue, table ".$jsparams."){
					var mydiv=document.getElementById(id); mydiv.innerHTML = ''; mydiv.className = 'onload';
					window.Oby.xRequest('index.php?option=com_hikashop&tmpl=component&ctrl=".$this->ctrl.$this->token."&task='+id+'&value='+newvalue+'&table='+table".$url.", {update: mydiv, mode:'GET'}, function(xhr){ mydiv.className = 'loading'; });
				}";

				$doc = JFactory::getDocument();
				$doc->addScriptDeclaration( $js );
			}
			$desc = empty($params->description[$value]) ? '' : $params->description[$value];
			$return = '<a class="btn btn-micro active" href="javascript:void(0);" onclick="joomToggleClass(\''.$id.'\', \''.$newValue.'\', \''.$table.'\''.$values.');" title="'.$desc.'"><div class="'. $params->class[$value] .'" style="background-color:'.$extra['color'].'">';
			if(!empty($extra['tooltip'])) $return .= JHTML::_('tooltip', $extra['tooltip'], '','','&nbsp;&nbsp;&nbsp;&nbsp;');
			$return .= '</div></a>';
			return $return;
		}
	}

	function radio($id,$value,$table,$extra = null, $options=array()){
		$column = substr($id,0,strrpos($id,'-'));
		$params = $this->_getToggle($column);
		$newValue = $params->values[$value];
		$jsparams = '';
		$url = '';
		$values = '';
		if(!empty($extra)){
			foreach($extra as $k => $v){
				$jsparams .= ', '.$k;
				$url .= "+'&extra[".$k."]='+".$k;
				$values .= ',\''.urlencode($v).'\'';
			}
		}

		static $pictureincluded = false;
		if(!$pictureincluded){
			$pictureincluded = true;
			$js = "function joomRadioPicture(id, newvalue, table ".$jsparams."){
				var mydiv = window.document.getElementById(id);
				mydiv.className = 'onload';
				window.Oby.xRequest('index.php?option=com_hikashop&tmpl=component&ctrl=".$this->ctrl.$this->token."&task='+id+'&value='+newvalue+'&table='+table".$url.",{update: mydiv, mode:'GET'}, function(xhr){ mydiv.className = 'loading'; });
			}";
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration( $js );
		}
		$desc = empty($params->description[$value]) ? '' : $params->description[$value];
		$checked = empty($value) ? '' : 'checked="checked"';
		return '<input type="radio" '.$checked.' name="'.$column.'" onchange="joomRadioPicture(\''.$id.'\', \''.$newValue.'\', \''.$table.'\''.$values.');" />';
	}

	function display($column, $value) {
		$params = $this->_getToggle($column);
		if(empty($params->pictures)) {
			return '<div class="toggle_loading"><a class="'.$params->aclass[$value].'" href="#" onclick="return false;" style="cursor:default;"></a></div>';
		}
		return '<img src="'.$params->pictures[$value].'"/>';
	}

	function delete($lineId,$elementids,$table,$confirm = false,$text=''){
		$this->addDeleteJS();
		if(empty($text)) $text = '<i class="fas fa-trash"></i>';
		return '<a href="javascript:void(0);" onclick="joomDelete(\''.$lineId.'\',\''.$elementids.'\',\''.$table.'\','. ($confirm ? 'true' : 'false').')" title="'.JText::_('HIKA_DELETE').'">'.$text.'</a>';
	}

	function addDeleteJS(){
		static $deleteJS = false;
		if(!$deleteJS){
			$deleteJS = true;
			$js = "function joomDelete(lineid,elementids,table,reqconfirm){
				if(reqconfirm){
					if(!confirm('".JText::_('HIKA_VALIDDELETEITEMS',true)."')) return false;
				}

				window.Oby.xRequest('index.php?option=com_hikashop&tmpl=component&ctrl=".$this->ctrl.$this->extra.$this->token."&task=delete&value='+elementids+'&table='+table, {mode:'GET'}, function(xhr) {window.document.getElementById(lineid).style.display = 'none';});
			}";
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration( $js );
		}
	}
}
