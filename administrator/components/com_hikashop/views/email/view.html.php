<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.2.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2017 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class EmailViewEmail extends hikashopView
{
	var $type = '';
	var $ctrl= 'email';
	var $nameListing = 'EMAILS';
	var $nameForm = 'EMAILS';
	var $icon = 'inbox';

	public function display($tpl = null) {
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function))
			$this->$function();
		return parent::display($tpl);
	}


	public function listing() {
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$config =& hikashop_config();

		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );

		$pageInfo->limit->value = $app->getUserStateFromRequest($this->paramBase.'.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($this->paramBase.'.limitstart', 'limitstart', 0, 'int');
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.user_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );

		jimport('joomla.filesystem.file');
		$mail_folder = rtrim( str_replace( '{root}', JPATH_ROOT, $config->get('mail_folder',HIKASHOP_MEDIA.'mail'.DS)), '/\\').DS;

		$mailClass = hikashop_get('class.mail');
		$files = $mailClass->getFiles();

		$emails = array();
		foreach($files as $file){
			$folder = $mail_folder;
			$filename = $file;

			$email = new stdClass();

			if(is_array($file)) {
				$folder = $file['folder'];
				if(!empty($file['name']))
					$email->name = $file['name'];
				$filename = $file['filename'];
				$file = $file['file'];
			}

			$email->file = $file;
			$email->published = $config->get($file.'.published');
			$emails[] = $email;
		}

		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = count($emails);

		$emails = array_slice($emails, $pageInfo->limit->start, $pageInfo->limit->value);
		$pageInfo->elements->page = count($emails);

		$this->assignRef('rows',$emails);
		$this->assignRef('pageInfo',$pageInfo);
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$this->getPagination();

		$this->toolbar = array(
			array('name' => 'pophelp', 'target' => $this->ctrl.'-listing'),
			'dashboard'
		);

		$manage = true; $delete = false;
		$this->assignRef('manage',$manage);
		$this->assignRef('delete',$delete);

		$toggle = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass', $toggle);
	}

}
