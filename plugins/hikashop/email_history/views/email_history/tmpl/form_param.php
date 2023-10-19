<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="hikashop_email_preview">
	<ul class="hika_tabs" rel="tabs:hikashop_email_history_tab_">
		<li class="active"><a href="#html_version" rel="tab:html_version" onclick="return window.hikashop.switchTab(this);"><?php echo JText::_('HTML_VERSION'); ?></a></li>
		<li><a href="#text_version" rel="tab:text_version" onclick="return window.hikashop.switchTab(this);"><?php echo JText::_('TEXT_VERSION'); ?></a></li>
	</ul>
	<div id="hikashop_email_history_tab_html_version">
<?php
if (!empty($this->element->email_log_body)) {
	$pattern  = '/(src="|url\(\')(?!https?:\/\/)/i';
	$replacement = '$1';
 	$this->element->email_log_body = preg_replace($pattern,$replacement.HIKASHOP_LIVE,@$this->element->email_log_body);
 	echo @$this->element->email_log_body;
}
?>
	</div>
	<div id="hikashop_email_history_tab_text_version" style="display:none;background-color:white;">
<?php
if (!empty($this->element->email_log_altbody))
	echo nl2br(@$this->element->email_log_altbody);
?>
	</div>
</div>
