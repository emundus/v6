<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('jquery.framework');

if (EventbookingHelper::isJoomla4())
{
	HTMLHelper::_('script', 'system/showon.js', array('version' => 'auto', 'relative' => true));

	$tabApiPrefix = 'uitab.';
}
else
{
	HTMLHelper::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true));

	$tabApiPrefix = 'bootstrap.';
}

$bootstrapHelper = EventbookingHelperBootstrap::getInstance();
$rowFluidClass   = $bootstrapHelper->getClassMapping('row-fluid');
$span7Class      = $bootstrapHelper->getClassMapping('span7');
$span5Class      = $bootstrapHelper->getClassMapping('span5');
?>
<form action="index.php?option=com_eventbooking&view=plugin" method="post" name="adminForm" id="adminForm" class="adminform form form-horizontal">
<div class="<?php echo $rowFluidClass; ?>">
<div class="<?php echo $span7Class; ?>">
	<fieldset class="form-horizontal options-form">
		<legend><?php echo Text::_('EB_PLUGIN_DETAIL'); ?></legend>
				<div class="control-group">
					<div class="control-label">
						<?php echo  Text::_('EB_NAME'); ?>
					</div>
					<div class="controls">
						<?php echo $this->item->name ; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo  Text::_('EB_TITLE'); ?>
					</div>
					<div class="controls">
						<input class="form-control" type="text" name="title" id="title" size="40" maxlength="250" value="<?php echo $this->item->title;?>" />
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('EB_AUTHOR'); ?>
					</div>
					<div class="controls">
						<input class="form-control" type="text" name="author" id="author" size="40" maxlength="250" value="<?php echo $this->item->author;?>" />
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('Creation date'); ?>
					</div>
					<div class="controls">
						<?php echo $this->item->creation_date; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('Copyright') ; ?>
					</div>
					<div class="controls">
						<?php echo $this->item->copyright; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('License'); ?>
					</div>
					<div class="controls">
						<?php echo $this->item->license; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('Author email'); ?>
					</div>
					<div class="controls">
						<?php echo $this->item->author_email; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('Author URL'); ?>
					</div>
					<div class="controls">
						<?php echo $this->item->author_url; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('Version'); ?>
					</div>
					<div class="controls">
						<?php echo $this->item->version; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('Description'); ?>
					</div>
					<div class="controls">
						<?php echo $this->item->description; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo  Text::_('EB_ACCESS'); ?>
					</div>
					<div class="controls">
						<?php echo $this->lists['access']; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo Text::_('Published'); ?>
					</div>
					<div class="controls">
						<?php
							echo $this->lists['published'];
						?>
					</div>
				</div>
	</fieldset>
</div>
<div class="<?php echo $span5Class; ?>">
	<fieldset class="form-horizontal options-form">
		<legend><?php echo Text::_('Plugins Parameter'); ?></legend>
		<?php
        $fieldSets = $this->form->getFieldsets();

        if (count($fieldSets) >= 2)
        {
            echo HTMLHelper::_($tabApiPrefix . 'startTabSet', 'payment-plugin-params', ['active' => 'basic']);

            foreach ($fieldSets as $fieldSet)
            {
                echo HTMLHelper::_($tabApiPrefix . 'addTab', 'payment-plugin-params', $fieldSet->name, $fieldSet->label);

                foreach ($this->form->getFieldset($fieldSet->name) as $field)
                {
                    echo $field->renderField();
                }

                echo HTMLHelper::_($tabApiPrefix . 'endTab');
            }

            echo HTMLHelper::_($tabApiPrefix . 'endTabSet');
        }
        else
        {
            foreach ($this->form->getFieldset('basic') as $field)
            {
                echo $field->renderField();
            }
        }
		?>
	</fieldset>
</div>
</div>
<div class="clearfix"></div>
	<?php echo HTMLHelper::_( 'form.token' ); ?>
	<input type="hidden" name="option" value="com_eventbooking" />
    <input type="hidden" name="id" value="<?php echo (int) $this->item->id; ?>"/>
	<input type="hidden" name="task" value="" />
</form>