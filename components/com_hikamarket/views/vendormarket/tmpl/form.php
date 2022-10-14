<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
	if(empty($this->form_type))
		$this->form_type = 'vendor';

	if($this->form_type == 'vendorregister') {
?>
<form id="hikamarket_registration_form" name="hikamarket_registration_form" method="post" action="<?php echo hikamarket::completeLink('vendor&task=register'.$this->url_itemid); ?>" enctype="multipart/form-data" onsubmit="if(window.localPage && window.localPage.checkForm){ return window.localPage.checkForm(this); }">
	<div class="hikamarket_vendor_registration_page">
		<h1><?php echo JText::_('HIKA_VENDOR_REGISTRATION');?></h1>
<?php
	$this->setLayout('registration');
	echo $this->loadTemplate();
?>
<input type="hidden" name="task" value="register"/>
<input type="hidden" name="ctrl" value="vendor"/>
<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>"/>
	</div>
</form>
<?php
	} else {
?>
<form id="hikamarket_vendor_form" name="hikamarket_vendor_form" method="post" action="<?php echo hikamarket::completeLink('vendor&task=form'.$this->url_itemid); ?>" enctype="multipart/form-data">
	<div class="hikamarket_vendor_edit_page">
		<h1><?php echo JText::_('HIKAM_VENDOR_EDIT');?></h1>
<?php
	if(hikamarket::acl('vendor/edit')) {
		$this->setLayout('registration');
		echo $this->loadTemplate();
	}

	if(!empty($this->element->extraData->middle))
		echo implode("\r\n", $this->element->extraData->middle);

	if(hikamarket::acl('vendor/edit/users')) {
		$this->setLayout('users');
		echo $this->loadTemplate();
	}

	if(!empty($this->element->extraData->bottom))
		echo implode("\r\n", $this->element->extraData->bottom);
?>
<input type="hidden" name="vendor_id" value="<?php echo $this->element->vendor_id; ?>"/>
<input type="hidden" name="task" value="save"/>
<input type="hidden" name="ctrl" value="vendor"/>
<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>"/>
	</div>
</form>
<?php
	}
?>
