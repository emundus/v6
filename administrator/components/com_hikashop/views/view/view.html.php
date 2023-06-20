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
class ViewViewView extends hikashopView{
	var $type = '';
	var $ctrl= 'view';
	var $nameListing = 'VIEWS';
	var $nameForm = 'VIEWS';
	var $icon = 'file-code';

	public function display($tpl = null) {
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function))
			$this->$function();
		parent::display($tpl);
	}

	public function getName() {
		return 'view';
	}

	public function listing(){
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->filter->client_id = $app->getUserStateFromRequest(HIKASHOP_COMPONENT.'.client_id', 'client_id', 2 , 'int');
		$pageInfo->filter->template = $app->getUserStateFromRequest(HIKASHOP_COMPONENT.'.template', 'template', '' , 'string');
		$pageInfo->filter->component = $app->getUserStateFromRequest(HIKASHOP_COMPONENT.'.component', 'component', '' , 'string');
		$pageInfo->filter->viewType = $app->getUserStateFromRequest(HIKASHOP_COMPONENT.'.viewType', 'viewType', '' , 'string');
		$pageInfo->limit->value = $app->getUserStateFromRequest($this->paramBase.'.limit', 'limit', $app->getCfg('list_limit'), 'int');
		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 500;
		if(hikaInput::get()->getVar('search')!=$app->getUserState($this->paramBase.".search")){
			$app->setUserState( $this->paramBase.'.limitstart',0);
			$pageInfo->limit->start = 0;
		}else{
			$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		}
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.'.search', 'search', '', 'string' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.'.filter_order', 'filter_order',	'a.user_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.'.filter_order_Dir', 'filter_order_Dir',	'desc',	'word' );

		$this->searchOptions = array('client_id'=> '', 'template'=> '', 'component'=> '', 'viewType'=> '');
		$this->openfeatures_class = "hidden-features";

		$views = array();
		switch($pageInfo->filter->client_id){
			case 0:
				$views[0] = HIKASHOP_FRONT.'views'.DS;
				break;
			case 1:
				$views[1] = HIKASHOP_BACK.'views'.DS;
				break;
			default:
				$views[0] = HIKASHOP_FRONT.'views'.DS;
				$views[1] = HIKASHOP_BACK.'views'.DS;
				break;
		}

		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$pluginViews = array();
		$app->triggerEvent('onViewsListingFilter', array(&$pluginViews, $pageInfo->filter->client_id));
		if(!empty($pluginViews)) {
			$i = 2;
			foreach($pluginViews as $pluginView) {
				$views[$i++] = $pluginView;
			}
		}
		$this->assignRef('pluginViews', $pluginViews);

		jimport('joomla.filesystem.folder');
		$templates = array();
		$templateValues = array();

		foreach($views as $client_id => $view){
			$component_name = '';
			$component = HIKASHOP_COMPONENT;
			$layout = false;
			if(is_array($view)) {
				$client_id = $view['client_id'];
				$component_name = $view['name'];
				$component = $view['component'];
				if(!empty($view['layout']))
					$layout = true;
				$view = $view['view'];
			}

			if(!empty($pageInfo->filter->component) && $pageInfo->filter->component != $component)
				continue;

			$folders = JFolder::folders($view);
			if(empty($folders) && !$layout)
				continue;
			if($layout)
				$folders = array('layouts');

			$clientTemplates = array();
			foreach($folders as $folder) {
				$check_folder = $view.$folder.DS.'tmpl';
				if($layout)
					$check_folder = $view;

				if(!JFolder::exists($check_folder))
					continue;

				$files = JFolder::files($check_folder);
				if(empty($files))
					continue;

				foreach($files as $file){
					if(substr($file,-4) != '.php')
						continue;

					$obj = new stdClass();
					$obj->path = $view.$folder.DS.'tmpl'.DS.$file;
					$obj->filename = $file;
					$obj->folder = $view.$folder.DS.'tmpl'.DS;
					$obj->client_id = $client_id;
					$obj->view = $folder;
					$obj->type = 'component';
					$obj->type_name = $component;
					$obj->file = substr($file,0,strlen($file)-4);
					$clientTemplates[]=$obj;
				}
			}

			if($client_id==0 && $component == HIKASHOP_COMPONENT){
				$plugins_folder = rtrim(JPATH_PLUGINS,DS).DS.'hikashoppayment';
				if(Jfolder::exists($plugins_folder)){
					$files = Jfolder::files($plugins_folder);
					foreach($files as $file){
						if(!preg_match('#^.*_(?!configuration).*\.php$#',$file))
							continue;

						$obj = new stdClass();
						$obj->path = $plugins_folder.DS.$file;
						$obj->filename = $file;
						$obj->folder = $plugins_folder;
						$obj->client_id = $client_id;
						$obj->type = 'plugin';
						$obj->view = '';
						$obj->type_name = 'hikashoppayment';
						$obj->file = substr($file,0,strlen($file)-4);
						$clientTemplates[]=$obj;
					}
				}
			}

			if(!empty($clientTemplates)){
				$client	= JApplicationHelper::getClientInfo($client_id);
				$tBaseDir = $client->path.DS.'templates';

				$query = 'SELECT * FROM '.hikashop_table('extensions',false).' WHERE type=\'template\' AND client_id='.(int)$client_id;
				$db = JFactory::getDBO();
				$db->setQuery($query);
				$joomlaTemplates = $db->loadObjectList();
				foreach($joomlaTemplates as $k => $v){
					$joomlaTemplates[$k]->assigned = $joomlaTemplates[$k]->protected;
					$joomlaTemplates[$k]->published = $joomlaTemplates[$k]->enabled;
					$joomlaTemplates[$k]->directory = $joomlaTemplates[$k]->element;
				}

				for($i = 0; $i < count($joomlaTemplates); $i++)  {
					if($joomlaTemplates[$i]->published || $joomlaTemplates[$i]->assigned){
						$templateValues[$joomlaTemplates[$i]->directory]=$joomlaTemplates[$i]->directory;

						if(!empty($pageInfo->filter->template) && $joomlaTemplates[$i]->directory!=$pageInfo->filter->template){
							continue;
						}

						$templateFolder = $tBaseDir.DS.$joomlaTemplates[$i]->directory.DS;
						foreach($clientTemplates as $template){
							$templatePerJoomlaTemplate = clone($template);
							$templatePerJoomlaTemplate->template = $joomlaTemplates[$i]->directory;
							$templatePerJoomlaTemplate->component = $component_name;
							$templatePerJoomlaTemplate->override = $templateFolder.'html'.DS.$template->type_name.DS;
							if($template->type=='component'){
								$templatePerJoomlaTemplate->override .= $template->view.DS;
							}
							$templatePerJoomlaTemplate->override .= $template->filename;
							$templatePerJoomlaTemplate->overriden=false;

							if(file_exists($templatePerJoomlaTemplate->override)){
								$templatePerJoomlaTemplate->overriden=true;
							}
							$templatePerJoomlaTemplate->id = $templatePerJoomlaTemplate->client_id.'|'.$templatePerJoomlaTemplate->template .'|'. $templatePerJoomlaTemplate->type.'|'. $templatePerJoomlaTemplate->type_name.'|'. $templatePerJoomlaTemplate->view.'|'.$templatePerJoomlaTemplate->file;
							$key = $templatePerJoomlaTemplate->client_id.'|'.$templatePerJoomlaTemplate->template .'|'.$templatePerJoomlaTemplate->type_name.'|'. $templatePerJoomlaTemplate->view.'|'.$templatePerJoomlaTemplate->file;

							if(!empty($pageInfo->filter->viewType) && $templatePerJoomlaTemplate->view!=$pageInfo->filter->viewType){
								continue;
							}

							$templates[$key]=$templatePerJoomlaTemplate;
						}

						if(JFolder::exists($templateFolder.'html'.DS.$component.DS)){
							$folders = JFolder::folders($templateFolder.'html'.DS.$component.DS);
							if(!empty($folders)){
								foreach($folders as $folder){

									$files = JFolder::files($templateFolder.'html'.DS.$component.DS.$folder);
									if(empty($files))
										continue;
									foreach($files as $file) {
										if(substr($file,-4)!='.php')
											continue;

										$filename = $templateFolder.'html'.DS.$component.DS.$folder.DS.$file;
										$found = false;
										foreach($templates as $tpl) {
											if($tpl->override == $filename) {
												$found = true;
												break;
											}
										}
										if(!$found) {
											$obj = new stdClass();
											$obj->path = $view.$folder.DS.'tmpl'.DS.$file;
											$obj->filename = $file;
											$obj->folder = $view.$folder.DS.'tmpl'.DS;
											$obj->client_id = $client_id;
											$obj->view = $folder;
											$obj->template = $joomlaTemplates[$i]->directory;
											$obj->type = 'component';
											$obj->type_name = $component;
											$obj->file = substr($file,0,strlen($file)-4);
											$obj->override = $filename;
											$obj->overriden = true;
											$obj->id = $obj->client_id.'|'.$obj->template.'|'.$obj->type.'|'.$obj->type_name.'|'.$obj->view.'|'.$obj->file;
											$key = $obj->client_id.'|'.$obj->template.'|'.$obj->view.'|'.$obj->file;
											$templates[$key]=$obj;
										}
									}
								}
							}
						}
					}
				}
			}
		}
		ksort($templates);
		$searchMap = array('filename','view','template');
		if(!empty($pageInfo->search)){

			$unset = array();
			foreach($templates as $k => $template){
				$found = false;
				foreach($searchMap as $field){
					if(strpos($template->$field,$pageInfo->search)!==false){
						$found=true;
					}
				}
				if(!$found){
					$unset[]=$k;
				}
			}
			if(!empty($unset)){
				foreach($unset as $u){
					unset($templates[$u]);
				}
			}
			$templates = hikashop_search($pageInfo->search,$templates,'id');
		}

		$viewTypes= array('0' => JHTML::_('select.option', 0, JText::_('ALL_VIEWS')));
		foreach($templates as $temp){
			if(!isset($viewTypes[strip_tags($temp->view)]) && !empty($temp->view)){
				$viewTypes[strip_tags($temp->view)] = JHTML::_('select.option', strip_tags($temp->view), strip_tags($temp->view));
			}
		}

		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = count($templates);
		if($pageInfo->limit->value == 500) $pageInfo->limit->value = 100;
		$this->assignRef('pageInfo',$pageInfo);
		$this->getPagination();

		$templates = array_slice($templates, $this->pagination->limitstart, $this->pagination->limit);
		$pageInfo->elements->page = count($templates);

		$this->assignRef('viewTypes',$viewTypes);
		$this->assignRef('rows',$templates);
		$this->assignRef('templateValues',$templateValues);
		$viewType = hikashop_get('type.view');
		$this->assignRef('viewType',$viewType);
		$templateType = hikashop_get('type.template');
		$this->assignRef('templateType',$templateType);
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_view_manage','all'));
		$this->assignRef('manage',$manage);
		$delete = hikashop_isAllowed($config->get('acl_view_delete','all'));
		$this->assignRef('delete',$delete);
		$this->toolbar = array(
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);

		jimport('joomla.client.helper');
		$ftp = JClientHelper::setCredentialsFromRequest('ftp');
		$this->assignRef('ftp',$ftp);
	}

	public function diff() {
		$id = hikaInput::get()->getString('id','');
		$viewClass = hikashop_get('class.view');
		$this->element = $viewClass->get($id);

		$this->element->src = hikaInput::get()->getVar('src');
		$cookie_key = str_replace('.', '_', $this->element->view.'_'.$this->element->file.'_src');
		if(empty($this->element->src)) {
			$this->element->src = hikaInput::get()->cookie->get($cookie_key);
		}
		if(!empty($this->element->src)) {
			setcookie($cookie_key, $this->element->src, time()+31556926, '/');
		}
		if(!JFile::exists($this->element->path) && !empty($this->element->folder) && !empty($this->element->filename)) {
			$origFiles = JFolder::files($this->element->folder);
			if(!empty($origFiles)) {	
				$this->element->possible_source_files = array();
				$override_parts = explode('_', $this->element->filename);
				foreach($origFiles as $origFile) {
					$parts = explode('_', $origFile);
					if($override_parts[0] == $parts[0]) {
						$this->element->possible_source_files[] = $origFile;
					}
				}
			}
		}


		$this->diffInc = hikashop_get('inc.diff');

		$this->toolbar = array(
			array('name' => 'link', 'icon'=>'edit','alt'=>JText::_('HIKA_EDIT'),'url'=>hikashop_completeLink('view&task=edit&id='.str_replace('.','%2E',strip_tags($this->element->id)))),
			'cancel',
		);

		hikashop_setTitle(JText::_('FILE_MODIFICATIONS'),$this->icon,$this->ctrl.'&task=diff&id='.$id);

	}

	public function form() {
		$id = hikaInput::get()->getString('id','');
		$viewClass = hikashop_get('class.view');
		$obj = $viewClass->get($id);

		if($obj) {
			jimport('joomla.filesystem.file');
			$obj->content = file_get_contents($obj->edit);
		}

		$viewClass->initStructure($obj);

		if(!empty($obj->content))
			$obj->content = htmlspecialchars($obj->content, ENT_COMPAT, 'UTF-8');

		$this->toolbar = array(
			array('name' => 'group', 'buttons' => array( 'apply', 'save')),
			'cancel',
			'|',
			array('name' => 'pophelp', 'target' => $this->ctrl.'-form')
		);

		if($obj->overriden) {
			array_unshift($this->toolbar, array('name' => 'link', 'icon'=>'file','alt'=>JText::_('SEE_MODIFICATIONS'),'url'=>hikashop_completeLink('view&task=diff&id='.str_replace('.','%2E',strip_tags($obj->id)))));
		}

		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task=edit&id='.$id);

		jimport('joomla.client.helper');
		$ftp = JClientHelper::setCredentialsFromRequest('ftp');
		$this->assignRef('ftp',$ftp);
		$this->assignRef('element',$obj);
		$editor = hikashop_get('helper.editor');
		$this->assignRef('editor',$editor);

	}
}
