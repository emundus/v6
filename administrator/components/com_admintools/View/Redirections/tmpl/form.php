<?php
/**
 * @package   AdminTools
* Copyright (c)2010-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Akeeba\AdminTools\Admin\Helper\Select;

/** @var $this \Akeeba\AdminTools\Admin\View\Redirections\Html */

defined('_JEXEC') or die;
?>
<form action="index.php" method="post" name="adminForm" id="adminForm" class="akeeba-form--horizontal">
	<div class="akeeba-container--66-33">
		<div>
			<div class="akeeba-form-group">
				<label for="source">
					<?php echo JText::_('COM_ADMINTOOLS_LBL_REDIRECTION_SOURCE'); ?>
				</label>
				<input type="text" name="source" id="source" value="<?php echo $this->escape($this->item->source); ?>" />
			</div>

			<div class="akeeba-form-group">
				<label for="dest">
					<?php echo JText::_('COM_ADMINTOOLS_LBL_REDIRECTION_DEST'); ?>
				</label>
				<input type="text" name="dest" id="dest" value="<?php echo $this->escape($this->item->dest); ?>" />
				<p>
					<?php echo JText::_('COM_ADMINTOOLS_REDIRECTIONS_FIELD_DEST_DESC')?>
				</p>
			</div>

			<div class="akeeba-form-group">
				<label for="keepurlparams">
					<?php echo JText::_('COM_ADMINTOOLS_REDIRECTIONS_FIELD_KEEPURLPARAMS'); ?>
				</label>

				<?php echo Select::keepUrlParamsList('keepurlparams', null, $this->item->keepurlparams)?>

				<p>
					<?php echo JText::_('COM_ADMINTOOLS_REDIRECTIONS_FIELD_KEEPURLPARAMS_DESC')?>
				</p>
			</div>

			<div class="akeeba-form-group">
				<label for="dest">
					<?php echo JText::_('JPUBLISHED'); ?>
				</label>

				<?php echo \JHtml::_('FEFHelper.select.booleanswitch', 'published', $this->item->published)?>
				<p>
					<?php echo JText::_('COM_ADMINTOOLS_REDIRECTIONS_FIELD_PUBLISHED_DESC')?>
				</p>
			</div>
		</div>
	</div>

	<div class="akeeba-hidden-fields-container">
		<input type="hidden" name="option" value="com_admintools" />
		<input type="hidden" name="view" value="Redirection" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="id" id="id" value="<?php echo (int)$this->item->id; ?>" />
		<input type="hidden" name="<?php echo $this->container->platform->getToken(true); ?>" value="1" />
	</div>
</form>
