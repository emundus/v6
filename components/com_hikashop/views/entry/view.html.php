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
class EntryViewEntry extends HikaShopView {

	function display($tpl = null){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}

	function form(){
		if(hikashop_level(2)){
			$jversion = preg_replace('#[^0-9\.]#i','',JVERSION);
			if(version_compare($jversion, '3.4.0', '>='))
				JHTML::_('behavior.formvalidator');
			else
				JHTML::_('behavior.formvalidation');

			$app = JFactory::getApplication();
			$fieldsClass = hikashop_get('class.field');
			$fieldsClass->suffix='_1';
			$this->assignRef('fieldsClass',$fieldsClass);
			$null = null;
			$this->extraFields['entry'] = $fieldsClass->getFields('frontcomp',$null,'entry');
			if(!empty($this->extraFields['entry'])){
				foreach($this->extraFields['entry'] as $field){
					$key = $field->field_namekey;
					if(isset($_REQUEST[$field->field_namekey])){
						$null->$key = hikaInput::get()->getVar($field->field_namekey);
					}
				}
			}
			$this->assignRef('extraFields',$this->extraFields);
			$this->assignRef('entry',$null);
			$cart = hikashop_get('helper.cart');
			$this->assignRef('cart',$cart);
			$empty = '';
			jimport('joomla.html.parameter');
			$params = new HikaParameter($empty);
			$this->assignRef('params',$params);
			$values = array('entry'=>$null);
			$fieldsClass->checkFieldsForJS($this->extraFields,$this->requiredFields,$this->validMessages,$values);
			$fieldsClass->addJS($this->requiredFields,$this->validMessages,array('entry'));
			$this->assignRef('config',hikashop_config());
			$fieldsClass->jsToggle($this->extraFields['entry'],$null);
			$url=JURI::base(true).'/index.php?option='.HIKASHOP_COMPONENT.'&tmpl=component&ctrl=entry&task=newentry';
			$parents = $fieldsClass->getParents($this->extraFields['entry']);
			$code ='';
			if(!empty($parents)){
				$code = $fieldsClass->initJSToggle($parents,$null,'new_entry_id');
			}
			$js ="
			hikashop['entry_id'] = 1;
			function hikashopAddEntry(divId){
				div = document.getElementById(divId);
				if(div){
					hikashop['entry_id']=hikashop['entry_id']+1;
					var new_entry_id  = hikashop['entry_id'];
					window.Oby.xRequest('".$url."&id='+new_entry_id, { mode: 'get'}, function(result) { hikashopAddEntryHTML(result.responseText,div,new_entry_id); });
				}
			}

			function hikashopAddEntryHTML(result,div,new_entry_id){
				var newdiv = document.createElement('div');
				var divIdName = 'new_entry_div_'+new_entry_id;
				newdiv.setAttribute('id',divIdName);
				div.appendChild(newdiv);
				newdiv.innerHTML=result;
				".$code."
			}
			function hikashopRemoveEntryHTML(entry_id){
				var maindiv = document.getElementById('hikashop_entries_info');
				var divIdName = 'new_entry_div_'+entry_id;
				var child = document.getElementById(divIdName);
				maindiv.removeChild(child);
			}
			";
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration( "\n<!--\n".$js."\n//-->\n" );

		}
	}
	function newentry(){
		if(hikashop_level(2)){
			$jversion = preg_replace('#[^0-9\.]#i','',JVERSION);
			if(version_compare($jversion, '3.4.0', '>='))
				JHTML::_('behavior.formvalidator');
			else
				JHTML::_('behavior.formvalidation');

			$app = JFactory::getApplication();
			$fieldsClass = hikashop_get('class.field');
			$this->assignRef('fieldsClass',$fieldsClass);
			$null = null;
			$this->extraFields['entry'] = $fieldsClass->getFields('frontcomp',$null,'entry');
			$this->assignRef('extraFields',$this->extraFields);
			$this->assignRef('entry',$null);
			$id = hikaInput::get()->getInt('id');
			$fieldsClass->suffix='_'.$id;
			$this->assignRef('id',$id);

		}
	}

}
