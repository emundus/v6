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
<form action="<?php echo hikamarket::completeLink('mangopay');?>" method="post" name="hikamarket_form" id="hikamarket_mangopay_document_form" enctype="multipart/form-data">
	<dl class="mangopay_documents dl-horizontal">
		<dt><?php
			echo JText::_('MANGOPAY_DOCUMENT_TYPE');
		?></dt>
		<dd><?php
			$values = array(
				'IDENTITY_PROOF' => JHTML::_('select.option', 'IDENTITY_PROOF', JText::_('MANGOPAY_DOC_IDENTITY_PROOF')),
				'REGISTRATION_PROOF' => JHTML::_('select.option', 'REGISTRATION_PROOF', JText::_('MANGOPAY_DOC_REGISTRATION_PROOF')),
				'ARTICLES_OF_ASSOCIATION' => JHTML::_('select.option', 'ARTICLES_OF_ASSOCIATION', JText::_('MANGOPAY_DOC_ARTICLES_OF_ASSOCIATION')),
				'SHAREHOLDER_DECLARATION' => JHTML::_('select.option', 'SHAREHOLDER_DECLARATION', JText::_('MANGOPAY_DOC_SHAREHOLDER_DECLARATION')),
			);
			echo JHTML::_('select.genericlist', $values, 'mangodoc[type]', '', 'value', 'text', '');
		?></dd>

		<dt><?php
			echo JText::_('MANGOPAY_DOCUMENT_FILE');
		?></dt>
		<dd>
			<input type="file" name="mangodoc_page"/>
		</dd>
	</dl>

	<div>
		<input class="btn btn-primary" value="<?php echo JText::_('MANGOPAY_ADD_DOCUMENT'); ?>" type="submit" onclick="return window.hikamarket.submitform('adddocument','hikamarket_mangopay_document_form');"/>
		<div style="float:right">
			<a class="btn btn-info" href="<?php echo hikamarket::completeLink('mangopay'); ?>"><?php echo JText::_('HIKA_CANCEL'); ?></a>
		</div>
	</div>
	<div style="clear:both"></div>

	<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>"/>
	<input type="hidden" name="task" value="bank"/>
	<input type="hidden" name="ctrl" value="mangopay"/>
	<?php echo JHTML::_('form.token'); ?>
</form>
