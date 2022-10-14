<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><h1><?php echo JText::_('HIKAM_MANGOPAY_TITLE'); ?></h1>
<form action="<?php echo hikamarket::completeLink('mangopay');?>" method="post" name="hikamarket_form" id="hikamarket_mangopay_bank_form">
	<dl class="mangopay_bank_addition dl-horizontal">
		<dt><?php echo JText::_('MANGOPAY_BANK_TYPE'); ?></dt>
		<dd><?php
			$values = array(
				'IBAN' => JHTML::_('select.option', 'IBAN', JText::_('MANGOPAY_BANK_TYPE_IBAN')),
				'GB' => JHTML::_('select.option', 'GB', JText::_('MANGOPAY_BANK_TYPE_GB')),
				'US' => JHTML::_('select.option', 'US', JText::_('MANGOPAY_BANK_TYPE_US')),
				'CA' => JHTML::_('select.option', 'CA', JText::_('MANGOPAY_BANK_TYPE_CA')),
				'OTHER' => JHTML::_('select.option', 'OTHER', JText::_('MANGOPAY_BANK_TYPE_OTHER')),
			);
			echo JHTML::_('select.genericlist', $values, 'mangobank[type]', ' onchange="window.localPage.setBankType(this);"', 'value', 'text', 'IBAN');
		?></dd>

		<dt><?php echo JText::_('MANGOPAY_BANK_OWNER_NAME'); ?></dt>
		<dd>
			<input type="text" name="mangobank[ownername]" value=""/>
		</dd>

		<dt><?php echo JText::_('MANGOPAY_BANK_OWNER_ADDRESS'); ?></dt>
		<dd>
			<input type="text" name="mangobank[owneraddress]" value=""/>
		</dd>

	</dl>

	<dl id="mangopay_bank_iban" class="mangopay_bank_addition dl-horizontal">
		<dt><?php echo JText::_('MANGOPAY_BANK_IBAN'); ?></dt>
		<dd>
			<input type="text" name="mangobank[iban][iban]" value=""/>
		</dd>
		<dt><?php echo JText::_('MANGOPAY_BANK_BIC'); ?></dt>
		<dd>
			<input type="text" name="mangobank[iban][bic]" value=""/>
		</dd>
	</dl>

	<dl id="mangopay_bank_gb" class="mangopay_bank_addition dl-horizontal" style="display:none;">
		<dt><?php echo JText::_('MANGOPAY_BANK_ACCOUNTNUMBER'); ?></dt>
		<dd>
			<input type="text" name="mangobank[gb][accountnumber]" value=""/>
		</dd>
		<dt><?php echo JText::_('MANGOPAY_BANK_SORTCODE'); ?></dt>
		<dd>
			<input type="text" name="mangobank[gb][sortcode]" value=""/>
		</dd>
	</dl>

	<dl id="mangopay_bank_us" class="mangopay_bank_addition dl-horizontal" style="display:none;">
		<dt><?php echo JText::_('MANGOPAY_BANK_ACCOUNTNUMBER'); ?></dt>
		<dd>
			<input type="text" name="mangobank[us][accountnumber]" value=""/>
		</dd>
		<dt><?php echo JText::_('MANGOPAY_BANK_ABA'); ?></dt>
		<dd>
			<input type="text" name="mangobank[us][aba]" value=""/>
		</dd>
	</dl>

	<dl id="mangopay_bank_ca" class="mangopay_bank_addition dl-horizontal" style="display:none;">
		<dt><?php echo JText::_('MANGOPAY_BANK_BANKNAME'); ?></dt>
		<dd>
			<input type="text" name="mangobank[ca][bankname]" value=""/>
		</dd>
		<dt><?php echo JText::_('MANGOPAY_BANK_INSTITUTIONNUMBER'); ?></dt>
		<dd>
			<input type="text" name="mangobank[ca][institutionnumber]" value=""/>
		</dd>
		<dt><?php echo JText::_('MANGOPAY_BANK_BRANCHCODE'); ?></dt>
		<dd>
			<input type="text" name="mangobank[ca][branchcode]" value=""/>
		</dd>
		<dt><?php echo JText::_('MANGOPAY_BANK_ACCOUNTNUMBER'); ?></dt>
		<dd>
			<input type="text" name="mangobank[ca][accountnumber]" value=""/>
		</dd>
	</dl>

	<dl id="mangopay_bank_other" class="mangopay_bank_addition dl-horizontal" style="display:none;">
		<dt><?php echo JText::_('MANGOPAY_BANK_COUNTRY'); ?></dt>
		<dd>
			<input type="text" name="mangobank[other][country]" value=""/>
		</dd>
		<dt><?php echo JText::_('MANGOPAY_BANK_BIC'); ?></dt>
		<dd>
			<input type="text" name="mangobank[other][bic]" value=""/>
		</dd>
		<dt><?php echo JText::_('MANGOPAY_BANK_ACCOUNTNUMBER'); ?></dt>
		<dd>
			<input type="text" name="mangobank[other][accountnumber]" value=""/>
		</dd>
	</dl>

	<div>
		<input class="btn btn-primary" value="<?php echo JText::_('MANGOPAY_SAVE_BANKACCOUNT'); ?>" type="submit" onclick="return window.hikamarket.submitform('addbank','hikamarket_mangopay_bank_form');"/>
		<div style="float:right">
			<a class="btn btn-info" href="<?php echo hikamarket::completeLink('mangopay'); ?>"><?php echo JText::_('HIKA_CANCEL'); ?></a>
		</div>
	</div>

	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>"/>
	<input type="hidden" name="task" value="bank"/>
	<input type="hidden" name="ctrl" value="mangopay"/>
	<?php echo JHTML::_('form.token'); ?>
</form>
<script type="text/javascript">
if(!window.localPage)
	window.localPage = {};
window.localPage.bankType = 'iban';
window.localPage.setBankType = function(el) {
	var d = document, e = null;
	e = d.getElementById('mangopay_bank_' + window.localPage.bankType);
	if(e) e.style.display = 'none';

	window.localPage.bankType = el.value.toLowerCase();

	e = d.getElementById('mangopay_bank_' + window.localPage.bankType);
	if(e) e.style.display = '';
};
</script>
