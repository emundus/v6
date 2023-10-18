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
class hikashopEditorHelper {
	var $width = '100%';
	var $height = '500';
	var $cols = 100;
	var $rows = 20;
	var $editor = null;
	var $name = '';
	var $content = '';
	var $id = 'jform_articletext';
	static $cpt = 0;
	static $initialized = array();

	function __construct() {
		$this->setEditor();
		$this->options = array('pagebreak');
		$config =& hikashop_config();
		$readmore = $config->get('readmore',0);
		if(!$readmore)
			$this->options[] = 'readmore';
	}

	function setDescription() {
		$this->width = 700;
		$this->height = 200;
		$this->cols = 80;
		$this->rows = 10;
	}

	function setContent($var) {
		if(HIKASHOP_J40)
			return 'Joomla.editors.instances["'.$this->id.'"].setValue("'.$var.'");';

		$function = 'try{'.$this->myEditor->setContent($this->name, $var).' }catch(err){alert(\'Error using the setContent function of the wysiwyg editor\')}';
		switch($this->editor) {
			case 'jce':
				return ' try{JContentEditor.setContent(\''.$this->name.'\', '.$var.'); }catch(err){try{WFEditor.setContent(\''.$this->name.'\', '.$var.')}catch(err){'.$function.'} }';
			case 'fckeditor':
				return ' try{FCKeditorAPI.GetInstance(\''.$this->name.'\').SetHTML('.$var.'); }catch(err){'.$function.'} ';
			case 'jckeditor':
				return ' try{oEditor.setData('.$var.');}catch(err){(!oEditor) ? CKEDITOR.instances.'.$this->name.'.setData('.$var.') : oEditor.insertHtml = '.$var.'}';
			case 'ckeditor':
				return ' try{CKEDITOR.instances.'.$this->name.'.setData('.$var.'); }catch(err){'.$function.'} ';
			case 'artofeditor':
				return ' try{CKEDITOR.instances.'.$this->name.'.setData('.$var.'); }catch(err){'.$function.'} ';
		}
		return $function;
	}

	function getContent() {
		if(HIKASHOP_J40)
			return 'Joomla.editors.instances["'.$this->id.'"].getValue();';
		return $this->myEditor->getContent($this->name);
	}

	function display() {
		$id = $this->id;
		if(self::$cpt >= 1 && $this->id == 'jform_articletext') {
			$id = $this->id . '_' . self::$cpt;
		}
		self::$cpt++;
		$editor = $this->myEditor->display($this->name, $this->content, $this->width, $this->height, $this->cols, $this->rows, $this->options, $id);
		if($this->editor == 'tinymce' && $this->height > 0) {
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration('
var hikaInitEditor_'.$id.' = function(){
	var el = document.getElementById("'.$id.'_ifr");
	if(!el) { setTimeout(hikaInitEditor_'.$id.', 250); return; }
	el.style.height = "'.$this->height.'px";
};
window.hikashop.ready(hikaInitEditor_'.$id.');
');
		}

		return $editor;
	}

	function jsCode() {
		if(HIKASHOP_J40)
			return 'Joomla.editors.instances["'.$this->id.'"].getValue();';
		$js = 'var unload = false; if(window.navigator.userAgent.indexOf("Edge") > -1) { unload = true; } ';
		if($this->editor=='tinymce'){
			$js .= 'if(!unload) { try{ tinyMCE.execCommand(\'mceToggleEditor\', false, \''.$this->id.'\'); unload = true; }catch(h){}} ';
		}
		$js .= 'if(!unload) { try{ ' .
				$this->myEditor->save($this->id) .
			' unload = true; }catch(e){} } if(!unload) { try {' .
				$this->myEditor->save($this->name) .
			' unload = true; } catch(f){} }';
		return $js;
	}

	function jsUnloadCode() {
		if(HIKASHOP_J40)
			return;
		switch($this->editor) {
			case 'tinymce':
				return 'try{ var n = ["'.str_replace(array('"','\\'),'', $this->name).'", "'.str_replace(array('"','\\'),'', $this->id).'"]; '.
						' if(document.getElementById(n[0])) { tinymce.remove("#"+n[0]); } '.
						' if(document.getElementById(n[1])) { tinymce.remove("#"+n[1]); } ' .
					' } catch(f){}';
		}
		return '';
	}

	function displayCode($name, $content, $options = array()) {
		$config =& hikashop_config();
		$code_editor = $config->get('code_editor','codemirror');

		if(!empty($code_editor) && $this->hasCodeEditor($code_editor))
			$this->setEditor($code_editor);
		elseif($this->hasCodeEditor('none'))
			$this->setEditor('none');
		else
			return "You need to activate either the CodeMirror or the None editor plugin via the Joomla plugins manager to be able to edit files.";

		$params = array();
		if(!empty($options))
			$params = $options;

		$id = $this->id;
		if(self::$cpt >= 1 && $this->id == 'jform_articletext') {
			$id = $this->id . '_' . self::$cpt;
		}
		self::$cpt++;
		return $this->myEditor->display($name, $content, $this->width, $this->height, $this->cols, $this->rows, false, $id, null, null, $params);
	}

	function setEditor($editor = '') {
		if(empty($editor)) {
			$config =& hikashop_config();
			$this->editor = $config->get('editor',null);
			if(empty($this->editor))
				$this->editor = null;
		} else {
			$this->editor = $editor;
		}
		if(HIKASHOP_J30) {
			if(empty($this->editor)) {
				$jconfig = JFactory::getConfig();
				$this->editor = $jconfig->get( 'editor' );
				$user_editor = JFactory::getUser()->getParam("editor");
				if(!empty($user_editor) && $user_editor != 'JEditor')
					$this->editor = $user_editor;
			}
			jimport( 'joomla.html.editor' );
			$this->myEditor = \JEditor::getInstance($this->editor);
		} else
			$this->myEditor = JFactory::getEditor($this->editor);
		if(!HIKASHOP_J30 || empty(self::$initialized[$this->editor]))
			$this->myEditor->initialise();
		self::$initialized[$this->editor] = true;
	}

	function hasCodeEditor($name = 'codemirror') {
		static $has = array();
		if(isset($has[$name]))
			return $has[$name];

		$db = JFactory::getDBO();
		$query = 'SELECT element FROM '.hikashop_table('extensions',false).' WHERE element='.$db->Quote($name).' AND folder=\'editors\' AND enabled=1 AND type=\'plugin\'';
		$db->setQuery($query);
		$editor = $db->loadResult();

		$has[$name] = !empty($editor);
		return $has[$name];
	}
}
