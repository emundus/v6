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
class hikashopEmailtemplateType {
	protected $values = null;

	public function load($mail_name = null) {
		$this->values = array(
			JHTML::_('select.option', '', JText::_('HIKA_NONE'))
		);

		if(empty($mail_name))
			return $this->values;

		jimport('joomla.filesystem.folder');

		$regexcore = '^([-_A-Za-z0-9]*)\.html\.php$';
		$allTemplateCoreFiles = JFolder::files(HIKASHOP_MEDIA.DS.'mail'.DS.'template', $regexcore);
		foreach($allTemplateCoreFiles as $oneFile) {
			preg_match('#'.$regexcore.'#i', $oneFile, $results);
			$n = $results[1];
			$this->values[$n] = JHTML::_('select.option', $n, $n);
		}

		$regexmodified = '^([-_A-Za-z0-9]*)\.html.modified.php$';
		$allTemplateModifiedFiles = JFolder::files(HIKASHOP_MEDIA.DS.'mail'.DS.'template', $regexmodified);
		foreach($allTemplateModifiedFiles as $oneFile) {
			preg_match('#'.$regexmodified.'#i', $oneFile, $results);
			$n = $results[1];
			$override = isset($this->values[$n]);
			$this->values[$n] = JHTML::_('select.option', $n, $n . ($override ? ' *' : ''));
		}

		$external_template_files = array();
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onMailTemplateListing', array(&$external_template_files, $mail_name));
		if(empty($external_template_files))
			return $this->values;
		foreach($external_template_files as $k => $f) {
			$this->values[$k] = JHTML::_('select.option', $k, $k);
		}

		return $this->values;
	}

	public function display($map, $value, $mail_name = false) {
		if($mail_name === false)
			$mail_name = hikaInput::get()->getCmd('mail_name', false);
		if($mail_name === false)
			$mail_name = $this->mail_name;

		if(empty($mail_name))
			return '';

		$this->load($mail_name);

		$html = JHTML::_('select.genericlist', $this->values, $map, 'class="custom-select" size="1"', 'value', 'text', $value, 'template');

		$popupHelper = hikashop_get('helper.popup');
		$html .= $popupHelper->display(
			'<i class="fas fa-pen" aria-hidden="true"></i>',
			'TEMPLATE',
			'\''.'index.php?option=com_hikashop&amp;tmpl=component&amp;ctrl=email&amp;task=emailtemplate&amp;file=\'+document.getElementById(\'template\').value+\'&amp;email_name='.$mail_name.'\'',
			'hikashop_edit_template',
			760,480, 'class="btn btn-primary" title="'.JText::_('HIKA_EDIT').'"', '', 'link',true
		);

		$html .= $popupHelper->display(
			'<i class="fa fa-plus" aria-hidden="true"></i>',
			'TEMPLATE',
			hikashop_completeLink('email&task=emailtemplate&email_name='.$mail_name, true),
			'hikashop_new_template',
			760,480, 'class="btn btn-primary" title="'.JText::_('HIKA_NEW').'"', '', 'link'
		);

		return $html;
	}
}
