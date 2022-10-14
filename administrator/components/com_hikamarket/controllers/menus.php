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
class menusMarketController extends hikamarketController {
	protected $type = 'menus';

	protected $rights = array(
		'display' => array('display', 'show', 'listing', 'cancel'),
		'add' => array(),
		'edit' => array('edit', 'toggle', 'add_module'),
		'modify' => array('save', 'apply'),
		'delete' => array('remove')
	);

	public function __construct($config = array())	{
		parent::__construct($config);
		$this->registerDefaultTask('listing');
	}

	public function add_module() {
		$id = hikamarket::getCID('id');
		$menuClass = hikamarket::get('class.menus');
		$menu->attachAssocModule($id);
		$this->edit();
	}

	public function store() {
		$app = JFactory::getApplication();
		if(hikamarket::isAdmin())
			return $this->adminStore();
		return false;
	}

	public function remove() {
		$app = JFactory::getApplication();
		if(hikamarket::isAdmin())
			return $this->adminRemove();
		return false;
	}

	public function edit() {
		$id = hikamarket::getCID('id');
		$app = JFactory::getApplication();

		if(HIKASHOP_J30) {
			if(!empty($id))
				$app->redirect( JRoute::_('index.php?option=com_menus&view=item&layout=edit&id='.$id, false) );
			else
				$app->redirect( JRoute::_('index.php?option=com_menus', false) );
		}

		if(hikaInput::get()->getInt('fromjoomla') && !empty($id)) {
			$context = 'com_menus.edit.item';
			$values = (array)$app->getUserState($context . '.id');
			$index = array_search((int)$id, $values, true);
			if(is_int($index)) {
				unset($values[$index]);
				$app->setUserState($context . '.id', $values);
			}
		}
		return parent::edit();
	}
}
