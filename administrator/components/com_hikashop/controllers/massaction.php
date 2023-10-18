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
class MassactionController extends hikashopController{
	var $type='massaction';
	var $pkey = 'massaction_id';
	var $table = 'massaction';

	function __construct(){
		parent::__construct();
		$this->display[]='countresults';
		$this->modify[]='process';
		$this->modify_views[]='displayassociate';
		$this->modify_views[]='results';
		$this->modify_views[]='editcell';
		$this->modify[]='savecell';
		$this->modify[]='copy';
		$this->modify[]='batch_process';
		$this->modify_views[]='cancel_edit';
		$this->display[]='export';
	}

	function editcell(){
		hikaInput::get()->set( 'layout', 'editcell' );
		return parent::display();
	}

	function export(){
		hikaInput::get()->set( 'layout', 'export' );
		return parent::display();
	}

	function cancel_edit(){
		hikaInput::get()->set( 'layout', 'cell' );
		return parent::display();
	}

	function savecell(){
		$massactionClass = hikashop_get('class.massaction');

		if(isset($_POST['hikashop'])){
			$hikashop = hikaInput::get()->getVar( 'hikashop', '' );

			$data = $hikashop['data'];
			$table = $hikashop['table'];
			$column = $hikashop['column'];
			$type = $hikashop['type'];
			if(isset($hikashop['values']) && isset($_POST['data']['values'])){
				foreach($hikashop['values'] as $key=>$value){
					$values[$key]=$value;
				}
				foreach($_POST['data']['values'] as $key=>$value){
					$values[$key]=$value;
				}
			}else if(isset($hikashop['values'])){
				$values = $hikashop['values'];
			}else if(isset($_POST['data']['values'])){
				$values = $_POST['data']['values'];
			}

			if(isset($hikashop['dataid'])){
				$data_id = $hikashop['dataid'];
				$ids = array();
				if(is_array($hikashop['ids'])){
					$ids = $hikashop['ids'];
				}else{
					$ids[] = $hikashop['ids'];
				}
				foreach($ids as $id){
					if(isset($values[$id])){
						$massactionClass->editionSquare($data,$data_id,$table,$column,$values[$id],$id,$type);
					}
				}

			}else{
				foreach($hikashop['ids'] as $data_id=>$ids){
					foreach($ids as $id){
						$massactionClass->editionSquare($data,$data_id,$table,$column,$values['column'],$id,$type);
					}
				}
			}

		}
		hikaInput::get()->set( 'layout', 'cell' );
		return parent::display();
	}

	function process(){
		if(!empty($_POST)){
			$this->store();
		}

		$massactionClass = hikashop_get('class.massaction');
		$massaction = $massactionClass->get(hikaInput::get()->getInt('cid'));
		$elements = array();
		ob_start();
		$massactionClass->process($massaction,$elements);
		$html = ob_get_clean();
		$_POST['html_results']=$html;

		if(!empty($massactionClass->report)){
			if(hikaInput::get()->getCmd('tmpl') == 'component'){
				echo hikashop_display($massactionClass->report,'info');
				$js = "setTimeout('redirect()',2000); function redirect(){window.top.location.href = 'index.php?option=com_hikashop&ctrl=massaction'; }";
				$doc = JFactory::getDocument();
				$doc->addScriptDeclaration( $js );
				return;
			}else{
				$app = JFactory::getApplication();
				foreach($massactionClass->report as $oneReport){
					$app->enqueueMessage($oneReport);
				}
			}
		}
		return $this->edit();
	}

	public function batch_process(){
		$class = hikashop_get('class.massaction');
		$class->batch();
	}

	function copy(){
		$actions = hikaInput::get()->get('cid', array(), 'array');
		$result = true;
		if(!empty($actions)){
			$massactionClass = hikashop_get('class.massaction');
			foreach($actions as $action){
				$data = $massactionClass->get($action);
				if($data){
					unset($data->massaction_id);
					if(!$massactionClass->save($data)){
						$result=false;
					}
				}
			}
		}
		if($result){
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ), 'success');
		}
		return $this->listing();
	}

	function countresults(){
		$massActionClass = hikashop_get('class.massaction'); //load the hikaQuery class
		$num = hikaInput::get()->getInt('num');
		$table = hikaInput::get()->getWord('table');
		$filters = hikaInput::get()->getRaw('filter');

		if(empty($filters[$table]['type'][$num]))
			exit;

		$query = new HikaShopQuery();
		$query->select = 'hk_'.$table.'.*';
		$query->from = '#__hikashop_'.$table.' as hk_'.$table;

		$currentType = $filters[$table]['type'][$num];
		if(empty($filters[$table][$num][$currentType]))
			exit;

		$currentFilterData = $filters[$table][$num][$currentType];

		try {
			JPluginHelper::importPlugin('hikashop');
			$app = JFactory::getApplication();
			$messages = $app->triggerEvent('onCount'.ucfirst($table).'MassFilter'.$currentType, array(&$query, $currentFilterData, $num));
		}catch(Exception $e) {
			hikashop_display($e->getMessage(), 'error');
			exit;
		}
		echo implode(' | ', $messages);
		exit;
	}

	function results(){
		hikaInput::get()->set( 'layout', 'results' );
		return parent::display();
	}

	function displayassociate(){
		$path = hikaInput::get()->getVar('csv_path');
		$num = hikaInput::get()->getVar('current_filter');
		$cid = hikaInput::get()->getVar('cid','');

		if(!JPath::check($path)) {
			echo JText::_('FILE_NOT_FOUND');
			return false;
		}

		if(!empty($cid)){

			$massactionClass = hikashop_get('class.massaction');
			$params = $massactionClass->get($cid);
		}

		if(!empty($params->massaction_filters)){
			if(!is_array($params->massaction_filters))
				$filters = hikashop_unserialize($params->massaction_filters);
			else
				$filters = $params->massaction_filters;
		}else{
			$filters = array();
		}


		$element = array();
		$element['path'] = $path;
		if(isset($filters[0]->data['change'])){
			$changes = $filters[0]->data['change'];
			$element['change'] = $changes;
		}

		$massactionClass = hikashop_get('class.massaction');
		$data = $massactionClass->getFromFile($element, true);

		switch($data->error){
			case 'not_found':
				echo JText::_('FILE_NOT_FOUND');
				break;
			case 'fail_open':
				echo JText::_('HIKA_CANNOT_OPEN');
				break;
			case 'empty':
				echo JText::_('HIKA_EMPTY_FILE');
				break;
			case 'wrong_columns':
				if(isset($data->wrongColumns)){
					echo '<fieldset><legend>'.JText::_( 'SELECT_CORRESPONDING_COLUMNS' ).'</legend>';
					foreach($data->wrongColumns as $wrongColumn){
						$changeColumn = $wrongColumn.': ';
						$changeColumn .= '<select class="chzn-done" id="productfilter'.$num.'csvImport_pathType" name="filter[product]['.$num.'][csvImport][change]['.$wrongColumn.']">';
						$changeColumn .= '<option value="delete">'.JText::_('REMOVE').'</option>';
						foreach($data->validColumns as $validColumn){
							if(isset($changes[$wrongColumn]) && $changes[$wrongColumn] == $validColumn){
								$selected = ' selected="selected" ';
							}else{
								$selected = '';
							}
							$changeColumn .= '<option value="'.$validColumn.'" '.$selected.'>'.$validColumn.'</option>';
						}
						$changeColumn .= '</select><br/>';
						echo $changeColumn;
					}
					echo '</fieldset>';
				}
				break;
			default:
				echo JText::_('HIKA_VALID_FILE');
				break;
		}
	}
}
