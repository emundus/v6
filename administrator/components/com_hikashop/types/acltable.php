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
class hikashopAcltableType {
	public function __construct() {
		$db = JFactory::getDBO();
		$db->setQuery('SELECT a.*, a.title as text, a.id as value  FROM #__usergroups AS a ORDER BY a.lft ASC');
		$this->groups = $db->loadObjectList('id');
		foreach($this->groups as $id => $group){
			if(isset($this->groups[$group->parent_id])){
				$this->groups[$id]->level = intval(@$this->groups[$group->parent_id]->level) + 1;
				$this->groups[$id]->text = str_repeat('- - ',$this->groups[$id]->level).$this->groups[$id]->text;
			}
		}

		$this->choice = array(
			JHTML::_('select.option','all',JText::_('HIKA_ALL')),
			JHTML::_('select.option','special',JText::_('HIKA_CUSTOM')),
		);

		$this->config =& hikashop_config();
		$js = "function updateACLTable(cat, action) {
			choice = document['adminForm']['acl_'+cat];
			choiceValue = 'special';
			for (var i=0; i < choice.length; i++){
				 if (choice[i].checked){
					 choiceValue = choice[i].value;
				}
			}
			if(choiceValue == 'all'){
				document.getElementById('div_acl_'+cat).style.display = 'none';
			}else{
				document.getElementById('div_acl_'+cat).style.display = 'block';
				finalValue = '';
				for(i=0;i<allGroups.length;i++){
					var myvar = document.getElementById('acl_'+cat+'_'+allGroups[i]+'_'+action);
					if(myvar && myvar.checked){
							 finalValue += myvar.value+',';
					}
				}
				document.getElementById('acl_'+cat+'_'+action).value = finalValue;
			}
		}
		function updateGroup(cat,groupid,actions){
			for(i=0;i<actions.length;i++){
				var myvar = document.getElementById('acl_'+cat+'_'+groupid+'_'+actions[i]);
				if(!myvar) return;
				myvar.checked = 1 - myvar.checked;
				updateACLTable(cat,actions[i]);
			}
		}
		function updateAction(cat,action){
			for(i=0;i<allGroups.length;i++){
				var myvar = document.getElementById('acl_'+cat+'_'+allGroups[i]+'_'+action);
				if(myvar) myvar.checked = 1 - myvar.checked;
			}
			updateACLTable(cat,action);
		}
		var allGroups = new Array(";
		foreach($this->groups as $oneGroup){
			$js .= "'".$oneGroup->value."',";
		}
		$js = rtrim($js,',').");";
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
	}

	function display($category,$actions){
		$oneAction = reset($actions);
		$app = JFactory::getApplication();
		if((hikashop_isClient('administrator') && !HIKASHOP_BACK_RESPONSIVE) || (!hikashop_isClient('administrator') && !HIKASHOP_RESPONSIVE)) {
			$acltable = '<table class="acltable"><thead><tr><th></th>';
		}else{
			$acltable = '<table class="table table-striped table-hover"><thead><tr><th></th>';
		}
		foreach($actions as $action){
			$trans = JText::_('HIKA_'.strtoupper($action));
			if($trans == 'HIKA_'.strtoupper($action)) $trans = JText::_(strtoupper($action));
			$acltable .= '<th style="cursor:pointer" onclick="updateAction(\''.$category.'\',\''.$action.'\')">'.$trans.'<input type="hidden" name="config[acl_'.$category.'_'.$action.']" id="acl_'.$category.'_'.$action.'" value="'.$this->config->get('acl_'.$category.'_'.$action,'all').'"/></th>';
		}
		$acltable .= '</tr></thead><tbody>';
		$custom = false;
		foreach($this->groups as $oneGroup){
			$acltable .= '<tr class="aclline"><td valign="top" class="groupname" style="cursor:pointer" onclick="updateGroup(\''.$category.'\',\''.$oneGroup->value.'\',new Array(\''.implode("','",$actions).'\'))">'.$oneGroup->text.'</td>';
			foreach($actions as $action){
				$acltable .= '<td class="checkfield">';
				$value = $this->config->get('acl_'.$category.'_'.$action,'all');

				if(hikashop_isAllowed($value,$oneGroup->value,'group')){
					$checked = 'checked="checked"';
				}else{
					$custom = true;
					$checked = '';
				}
				$acltable .= '<input type="checkbox" id="acl_'.$category.'_'.$oneGroup->value.'_'.$action.'" onclick="updateACLTable(\''.$category.'\',\''.$action.'\');" value="'.$oneGroup->value.'" '.$checked.' />';

				$acltable .= '</td>';
			}
			$acltable .= '</tr>';
		}
		$acltable .= '</tbody></table>';
		$openDiv = JHTML::_('hikaselect.radiolist',   $this->choice, "acl_$category", 'onclick="updateACLTable(\''.$category.'\',\''.$oneAction.'\');"', 'value', 'text',($custom ? 'special' : 'all'));
		$openDiv .= '<input type="hidden" name="aclcat[]" value="'.$category.'"/><div id="div_acl_'.$category.'"'.($custom ? ' style="display:block"' : ' style="display:none"').'>';
		$return = $openDiv.$acltable.'</div>';
		return $return;
	}
}
