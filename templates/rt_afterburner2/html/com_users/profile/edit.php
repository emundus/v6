<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.noframes');
//load user_profile plugin language
//$lang = JFactory::getLanguage();
//$lang->load( 'plg_user_profile', JPATH_ADMINISTRATOR );

$template = JFactory::getApplication()->getTemplate();
$lang->load('tpl_'.$template, JPATH_THEMES.DS.$template);
$this->form->loadFile( dirname(__FILE__) . DS . "profile.xml");
?>
<div class="profile-edit<?php echo $this->pageclass_sfx?>">
<?php if ($this->params->get('show_page_heading')) : ?>
	<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
<?php endif; ?>

<form id="member-profile" action="<?php echo JRoute::_('index.php?option=com_users&task=profile.save'); ?>" method="post" class="form-validate" enctype="multipart/form-data">
<?php foreach ($this->form->getFieldsets() as $group => $fieldset):// Iterate through the form fieldsets and display each one.?>
	<?php $fields = $this->form->getFieldset($group);?>
	<?php if (count($fields)):?>
	<fieldset>
		<?php if (isset($fieldset->label)):// If the fieldset has a label set, display it as the legend.?>
		<legend><?php echo JText::_($fieldset->label); ?></legend>
		<?php endif;?>
		<dl>
		<?php  foreach ($fields as $field): //print_r($field);// Iterate through the fields in the set and display them.?>
			<?php if ($field->hidden):// If the field is hidden, just display the input.?>
				<?php echo $field->input;?>
			<?php else:?>
				<dt>
					<?php echo $field->label; ?>
					<?php if (!$field->required && $field->type!='Spacer' && $field->name!='jform[username]'): ?>
						<span class="optional"><?php echo JText::_('COM_USERS_OPTIONAL'); ?></span>
					<?php endif; ?>
				</dt>
				<dd><?php echo $field->input; ?></dd>
			<?php endif;?>
		<?php endforeach;?>
		</dl>
	</fieldset>
	<?php endif;?>
<?php endforeach;?>

		<div>
			<button type="submit" class="validate"><span><?php echo JText::_('JSUBMIT'); ?></span></button>
			<?php echo JText::_('COM_USERS_OR'); ?>
			<a href="<?php echo JRoute::_(''); ?>" title="<?php echo JText::_('JCANCEL'); ?>"><?php echo JText::_('JCANCEL'); ?></a>

			<input type="hidden" name="option" value="com_users" />
			<input type="hidden" name="task" value="profile.save" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>

<script>
function check_field(){
    <?php $i=0; foreach($fields as $field){?>
		firstname = document.getElementById("jform_emundus_profile_firstname");
		lastname = document.getElementById("jform_emundus_profile_lastname");
		field = document.getElementsByName("<?php echo $field->name; ?>");
		if (field[0] != undefined) {
			if (field[0].value == "" && "<?php echo $browser; ?>" != "IE")
				field[0].setStyles({backgroundColor: '#D0C2BD'});
			field[0].onblur = function() {
				if ("<?php echo $browser; ?>" != "IE")
					this.setStyles({backgroundColor: '#fff'}); 
				$("jform_name").value = firstname.value + ' ' + lastname.value;
			}
			if ("<?php echo $browser; ?>" != "IE") {
				field[0].onchange = function(){this.setStyles({backgroundColor: '#fff'});}
				field[0].onkeyup = function(){this.setStyles({backgroundColor: '#fff'});}
			}
		}
	<?php }?>
}
check_field();

</script>