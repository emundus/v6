<?php
defined('JPATH_BASE') or die;

$params = $displayData['params'];
?>
<?php if ($params->get('show_icons')) : ?>
		<?php echo JHtml::_('image', 'system/emailButton.png', JText::_('JGLOBAL_EMAIL'), null, true); ?>		
<?php endif; ?>
	<?php echo JText::_('JGLOBAL_EMAIL'); ?>