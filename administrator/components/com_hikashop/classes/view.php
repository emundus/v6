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
class hikashopViewClass extends hikashopClass {

	public function saveForm() {
		$id = hikaInput::get()->getString('id');
		$element = $this->get($id);

		if(!$element) return false;

		$duplicate = trim(hikaInput::get()->post->getCmd('duplicate', ''));
		if(!empty($duplicate) && substr($duplicate,0,1) != '.' && substr($duplicate,0,1) != '/' && substr($duplicate,0,1) != '\\' ) {
			$name = explode('_', $element->filename, 2);
			$override = substr($element->override, 0, - strlen($name[1])) . $duplicate.'.php';

			if($element->override != $override) {
				if(file_exists($override)) {
					$app = JFactory::getApplication();
					$app->enqueueMessage(JText::_('CANT_DUPLICATE_ON_EXISTING_FILE'));
					return false;
				}

				$element->override = $override;
			}
		}

		$element->content = hikaInput::get()->post->getRaw('filecontent', '');
		$result = $this->save($element);
		return $result;
	}

	public function save(&$element) {
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');

		if(!JPath::check($element->override))
			return false;

		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeViewUpdate', array( &$element, &$do ));

		if(!$do)
			return false;

		jimport('joomla.filesystem.file');
		$result = JFile::write($element->override, $element->content);

		if(!$result) {
			if(!$ftp['enabled'] && !JPath::setPermissions($element->override, '0755')) {
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::sprintf('FILE_NOT_WRITABLE',$element->override), 'error');
				return false;
			}

			$result = JFile::write($element->override, $element->content);

			if(!$ftp['enabled']) {
				JPath::setPermissions($element->override, '0555');
			}
		}

		if($result)
			$app->triggerEvent('onAfterViewUpdate', array( &$element ));

		return $result;
	}

	public function delete(&$id) {
		$element = $this->get(reset($id));
		if(!$element)
			return false;

		jimport('joomla.filesystem.file');
		if(!JFile::exists($element->override))
			return true;

		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');
		if (!$ftp['enabled'] && !JPath::setPermissions($element->override, '0755')) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('FILE_NOT_WRITABLE',$element->override), 'error');
			return false;
		}

		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeViewDelete', array(&$element));

		if(!$do)
			return false;

		$result = JFile::delete($element->override);
		if($result)
			$app->triggerEvent('onAfterViewDelete', array(&$element));
		return $result;
	}

	public function get($id, $default = null) {
		$parts = explode('|',$id);
		if(count($parts)!=6){
			return false;
		}
		$obj = new stdClass();
		$obj->id = $id;
		$obj->client_id = (int)$parts[0];
		$obj->template = $parts[1];
		$obj->type = $parts[2];
		$obj->type_name = $parts[3];
		$obj->view = $parts[4];
		$obj->filename = $parts[5];

		if(substr($obj->filename, -4) != '.php')
			$obj->filename .= '.php';

		if($obj->type == 'plugin') {
			$obj->folder = rtrim(JPATH_PLUGINS,DS).DS.$obj->type_name.DS;
		} else {
			$layout_mode = false;
			if($obj->type_name == HIKASHOP_COMPONENT) {
				switch($obj->client_id){
					case 0:
						$view = HIKASHOP_FRONT.'views'.DS;
						break;
					case 1:
						$view = HIKASHOP_BACK.'views'.DS;
						break;
					default:
						return false;
				}
			} else {
				$view = '';
				JPluginHelper::importPlugin('hikashop');
				$app = JFactory::getApplication();
				$pluginViews = array();
				$app->triggerEvent('onViewsListingFilter', array(&$pluginViews, $obj->client_id));
				if(!empty($pluginViews)) {
					foreach($pluginViews as $pluginView) {
						if($pluginView['client_id'] == $obj->client_id && $pluginView['component'] == $obj->type_name) {
							$view = $pluginView['view'];
							if(!empty($pluginView['layout']))
								$layout_mode = true;
							$obj->type_pretty_name = $pluginView['name'];
							break;
						}
					}
				}
				if(empty($view)) {
					return false;
				}
			}
			$obj->folder = $view.$obj->view.DS.'tmpl'.DS;
			if($layout_mode)
				$obj->folder = $view;
		}
		$obj->path = $obj->folder.$obj->filename;

		if(!JPath::check($obj->path))
			return false;

		$obj->file = substr($obj->filename,0,strlen($obj->filename)-4);
		$client	= JApplicationHelper::getClientInfo($obj->client_id);
		$tBaseDir = $client->path.DS.'templates';
		$templateFolder = $tBaseDir.DS.$obj->template.DS;
		$obj->override = $templateFolder.'html'.DS.$obj->type_name.DS;
		if($obj->type=='component') {
			$obj->override .= $obj->view.DS;
		}
		$obj->override .= $obj->filename;
		$obj->overriden = false;
		if(file_exists($obj->override)) {
			$obj->overriden = true;
			$obj->edit = $obj->override;
		} else {
			$obj->edit = $obj->path;
		}
		return $obj;
	}

	public function initStructure(&$file) {
		if(empty($file->content)) {
			return false;
		}

		$structure = array();

		$length = mb_strlen($file->content);

		$in_block = false;

		$current_block = new stdClass();
		$current_block->code = '';
		$current_block->type = 'normal';


		for($i = 0; $i < $length; $i++) {
			switch($file->content[$i]) {
				case '<':
					if($file->content[$i+1] == '!' && $file->content[$i+2] == '-' && $file->content[$i+3] == '-' ) {
						if(!$in_block) {
							$structure[] = $current_block;
							$current_block = new stdClass();
							$current_block->code = '';
							$current_block->type = 'block';
							$current_block->name = '';
							$in_block = true;

							for($j = $i+4; $j < $length; $j++) {
								switch($file->content[$j]) {
									case '-':
										if($file->content[$j+1] == '-' && $file->content[$j+2] == '>') {
											$i = $j+2;
											$current_block->name = trim($current_block->name);
											break 2;
										}
									default:
										$current_block->name .= $file->content[$j];
										break;
								}
							}
							if($current_block->name == 'END GRID') {
								$current_block->type = 'grid_end';
								$in_block = false;
								$structure[] = $current_block;
								$current_block = new stdClass();
								$current_block->code = '';
								$current_block->type = 'normal';
								$current_block->name = '';
								$in_block = false;
							}elseif(preg_match('#POSITION ([0-9]+)#', $current_block->name, $matches)) {
								$current_block->type = 'empty';
								$current_block->id = $matches[1];
								$structure[] = $current_block;
								$current_block = new stdClass();
								$current_block->code = '';
								$current_block->type = 'normal';
								$current_block->name = '';
								$in_block = false;
							}
							break;
						} else {
							$length_name = mb_strlen('EO '.$current_block->name);
							$end_name = mb_substr($file->content, $i+5, $length_name);
							if($end_name == 'EO '.$current_block->name) {
								$structure[] = $current_block;
								$current_block = new stdClass();
								$current_block->code = '';
								$current_block->type = 'normal';
								$i = $i + 5 + $length_name + 4; // length of <!-- + space + EO + space + name + space + -->
								$in_block = false;
							}
						}
					}
				default:
					$current_block->code .= $file->content[$i];
					break;
			}
		}

		if($in_block) {
			return false;
		}

		$structure[] = $current_block;

		$group_structure = array();

		$in_group = false;

		$current_group = new stdClass();
		$current_group->width = 'full';
		$current_group->blocks = array();

		foreach($structure as $block) {
			if(preg_match_all('#hkc-[^-]+-([0-9]+)#iU', $block->code, $matches)) {
				$group_structure[] = $current_group;
				$current_group = new stdClass();
				$current_group->width = end($matches[1]);
				$current_group->blocks = array();
			} elseif($block->type=='normal') {
				$empty = trim($block->code,"\r\t\n ");
				if(!empty($empty)) {
					$block->type = 'separator';
				}
			}
			if($block->type == 'grid_end') {
				$group_structure[] = $current_group;
				$current_group = new stdClass();
				$current_group->width = 'full';
				$current_group->blocks = array();
				continue;
			}
			if(!empty($block->id))
				$current_group->id = $block->id;

			$current_group->blocks[] = $block;
		}
		$group_structure[] = $current_group;

		$file->structure = $group_structure;
		return true;
	}
}
