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
class toggleMarketController extends hikashopBridgeController {
	public function __construct($config = array()) {
		parent::__construct($config);
		$this->registerDefaultTask('toggle');
		if(!headers_sent()) {
			header('Cache-Control: no-store, no-cache, must-revalidate');
			header('Cache-Control: post-check=0, pre-check=0', false);
			header('Pragma: no-cache');
		}
	}

	public function authorize($task) {
		return true;
	}

	public function toggle() {
		$completeTask = hikaInput::get()->getCmd('task');
		$task = substr($completeTask, 0, strrpos($completeTask, '-'));
		$elementPkey = substr($completeTask, strrpos($completeTask, '-') + 1);
		$value = hikaInput::get()->getCmd('value', '');
		$controllerName = hikaInput::get()->getWord('table', '');

		while(ob_get_level())
			@ob_end_clean();

		if(empty($controllerName)) {
			echo 'No controller';
			exit;
		}

		$controller = hikamarket::get('controller.'.$controllerName);

		if(empty($controller)) {
			echo 'No controller';
			exit;
		}

		if(!$controller->authorize('toggle')) {
			echo 'Forbidden task';
			exit;
		}

		$class = hikamarket::get('class.'.$controllerName);
		$id = false;
		if(method_exists($class, 'toggleId'))
			$id = $class->toggleId($task, $elementPkey);
		if($id === false || empty($id)) {
			echo 'Forbidden';
			exit;
		}
		$obj = new stdClass();
		$obj->$task = $value;
		$obj->$id = $elementPkey;

		$ret = $value;
		if(!$class->save($obj)) {
			$table = false;
			if(method_exists($class,'getTable')) {
				$table = $class->getTable();
				if(substr($table,0,1) != '#')
					$table = hikamarket::table($table);
			}
			if(empty($table)) {
				$table = hikamarket::table($controllerName);
			}
			$db	= JFactory::getDBO();
			$db->setQuery('SELECT '.$task.' FROM '.$table.' WHERE '.$id.' = '.$db->Quote($elementPkey), 0, 1);
			$ret = $db->loadResult();
		}

		$tmpl = hikaInput::get()->getString('tmpl', '');
		if($tmpl == 'raw') {
			echo '1';
			exit;
		}
		$toggleClass = hikamarket::get('helper.toggle');
		$extra = hikaInput::get()->get('extra', array(), 'array');
		if(!empty($extra)) {
			foreach($extra as $key => $val) {
				$extra[$key] = urldecode($val);
			}
		}
		echo $toggleClass->toggle(hikaInput::get()->getCmd('task', ''), $ret, $controllerName, $extra);
		exit;
	}

	public function delete() {
		while(ob_get_level())
			@ob_end_clean();

		$value2 = '';
		if(strpos(hikaInput::get()->getCmd('value'), '-') !== false)
			list($value1, $value2) = explode('-', hikaInput::get()->getCmd('value'));
		else
			$value1 = hikaInput::get()->getCmd('value');
		$table =  hikaInput::get()->getWord('table', '');
		$controller = hikamarket::get('controller.'.$table);
		if(empty($controller)) {
			echo 'No controller';
			exit;
		}

		if(!$controller->authorize('delete')) {
			echo 'Forbidden';
			exit;
		}

		$destClass = hikamarket::get('class.'.$table);
		$deleteToggle = $destClass->toggleDelete($value1, $value2);
		if(empty($deleteToggle)) {
			echo 'Forbidden';
			exit;
		}
		if($deleteToggle === true) {
			echo '1';
			exit;
		}

		$key2 = '';
		$v = reset($deleteToggle);
		if(is_array($v) && count($v) > 1)
			list($key1, $key2) = reset($deleteToggle);
		else
			$key1 = reset($deleteToggle);
		$table = key($deleteToggle);
		if(empty($key1) || empty($value1) || (!empty($key2) && empty($value2)) ) {
			echo 'No value';
			exit;
		}

		$db	= JFactory::getDBO();
		$query = 'DELETE FROM '.hikamarket::table($table).' WHERE '.$key1.' = '.$db->Quote($value1);
		if(!empty($key2))
			$query .= ' AND '.$key2.' = '.$db->Quote($value2);
		$db->setQuery($query);
		$db->execute();

		echo '1';
		exit;
	}

}
