<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<div>
	<form action="<?php echo hikamarket::completeLink('plugins'); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
<?php
	if(!empty($this->plugin->pluginView)) {
		$this->setLayout($this->plugin->pluginView);
		echo $this->loadTemplate();
	} else if(empty($this->plugin->noForm)) {
		$type = $this->plugin_type;
		$upType = strtoupper($type);
		$plugin_name = $type . '_name';
		$plugin_name_input = $plugin_name . '_input';
		$plugin_images = $type . '_images';
?>
<input type="hidden" name="subtask" value="<?php echo hikaInput::get()->getCmd('subtask','');?>"/>
<div class="hk-row-fluid">
	<div class="hkc-6">
			<fieldset class="adminform">
				<legend><?php echo JText::_('MAIN_INFORMATION'); ?></legend>
				<table class="admintable">
<?php if($this->multiple_plugin) { ?>
					<tr>
						<td class="key"><label for="data[<?php echo $type;?>][<?php echo $type;?>_name]"><?php echo JText::_('HIKA_NAME');?></label></td>
						<td>
							<input type="text" name="data[<?php echo $type;?>][<?php echo $type;?>_name]" value="<?php $n = $type.'_name'; echo @$this->element->$n; ?>"/>
						</td>
					</tr>
					<tr>
						<td class="key"><label for="data[<?php echo $type;?>][<?php echo $type;?>_published]"><?php echo JText::_('HIKA_PUBLISHED');?></label></td>
						<td>
							<?php $n = $type.'_published'; echo JHTML::_('hikaselect.booleanlist', 'data['.$type.']['.$type.'_published]' , '', @$this->element->$n); ?>
						</td>
					</tr>
<?php } else { ?>
					<tr>
						<td class="key"><label for="data[<?php echo $type;?>][<?php echo $type;?>_name]"><?php echo JText::_('HIKA_NAME');?></label></td>
						<td><?php echo $this->plugin_name; ?><input type="hidden" name="data[<?php echo $type;?>][<?php echo $type; ?>_published]" value="1"/>
						</td>
					</tr>
<?php } ?>
				</table>
			</fieldset>
	</div>
	<div class="hkc-6">
			<fieldset>
				<legend><?php echo JText::_('PLUGIN_PARAMETERS'); ?></legend>
<?php echo $this->content; ?>
			</fieldset>
	</div>
</div>
		<input type="hidden" name="data[<?php echo $type;?>][<?php echo $type;?>_id]" value="<?php $n = $type.'_id'; echo @$this->element->$n;?>"/>
		<input type="hidden" name="data[<?php echo $type;?>][<?php echo $type;?>_type]" value="<?php echo $this->name;?>"/>
		<input type="hidden" name="task" value="save"/>
<?php
	} else {
		echo $this->content;
	}
?>
		<input type="hidden" name="name" value="<?php echo $this->name;?>"/>
		<input type="hidden" name="ctrl" value="plugins" />
		<input type="hidden" name="plugin_type" value="<?php echo $this->plugin_type;?>" />
		<input type="hidden" name="option" value="<?php echo HIKAMARKET_COMPONENT; ?>" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
</div>
