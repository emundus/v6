<?php
/**
 * @package	HikaShop for Joomla!
 * @version	3.4.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2018 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_email_preview">
<?php
echo $this->tabs->startPane( 'mail_tab');
if (!empty($this->element->email_log_body)) {
	echo $this->tabs->startPanel(JText::_('HTML_VERSION'),'html_version');
	$pattern  = '/(src=")(?!https?:\/\/)/i';
	$replacement = '$1';
 	$this->element->email_log_body = preg_replace($pattern,$replacement.HIKASHOP_LIVE,@$this->element->email_log_body);
 	echo @$this->element->email_log_body;
 	echo $this->tabs->endPanel();
}
if (!empty($this->element->email_log_altbody)) {
	echo $this->tabs->startPanel(JText::_( 'TEXT_VERSION' ), 'text_version');
	echo nl2br(@$this->element->email_log_altbody);
	echo $this->tabs->endPanel();
}
echo $this->tabs->endPane();
?>
</div>
