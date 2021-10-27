<?php

/**
 * @package     External_Login
 * @subpackage  Component
 * @author      Christophe Demko <chdemko@gmail.com>
 * @author      Ioannis Barounis <contact@johnbarounis.com>
 * @author      Alexandre Gandois <alexandre.gandois@etudiant.univ-lr.fr>
 * @copyright   Copyright (C) 2008-2018 Christophe Demko, Ioannis Barounis, Alexandre Gandois. All rights reserved.
 * @license     GNU General Public License, version 2. http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://www.chdemko.com
 */

// No direct access to this file
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');

$fieldSets = $this->form->getFieldsets();

?>
<form
	action="<?php echo JRoute::_('index.php?option=com_externallogin&task=server.upload'); ?>"
	method="post"
	name="adminForm"
	id="upload-form"
	enctype="multipart/form-data"
>
<?php foreach ($fieldSets as $name => $fieldSet):?>
	<fieldset class="panelform">
		<legend><?php echo JText::sprintf($fieldSet->label, $this->item->title); ?></legend>
	<?php if (isset($fieldSet->description) && $desc = trim(JText::_($fieldSet->description))):?>
		<p class="tip"><?php echo $desc; ?></p>
	<?php endif; ?>
		<ul class="adminformlist">
	<?php foreach ($this->form->getFieldset($name) as $field):?>
		<?php if ($field->hidden):?>
			<?php echo $field->input; ?>
		<?php else:?>
			<li><?php echo $field->label . $field->input; ?></li>
		<?php endif; ?>
	<?php endforeach; ?>
		</ul>
		<button type="button" onclick="this.form.submit();"><?php echo JText::_('COM_EXTERNALLOGIN_BUTTON_UPLOAD'); ?></button>
		<button type="button" onclick="window.parent.SqueezeBox.close();"><?php echo JText::_('COM_EXTERNALLOGIN_BUTTON_CANCEL'); ?></button>
		<?php echo JHtml::_('form.token'); ?>
	</fieldset>
<?php endforeach; ?>

