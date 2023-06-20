<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('view');?>" method="post"  name="adminForm" id="adminForm">

	<?php if($this->ftp){ ?>
	<fieldset title="<?php echo JText::_('DESCFTPTITLE'); ?>">
		<legend><?php echo JText::_('DESCFTPTITLE'); ?></legend>

		<?php echo JText::_('DESCFTP'); ?>

		<?php
			if(!empty($this->ftp) && method_exists($this->ftp, '__toString'))
				$msg = $this->ftp->__toString();
			else
				$msg = @$this->ftp->message;
		?>

		<?php if(!empty($msg)){ ?>
			<p>
			<?php
				echo JText::_( $msg );
			?></p>
		<?php } ?>

		<table class="adminform nospace">
		<tbody>
		<tr>
			<td width="120">
				<label for="username"><?php echo JText::_('HIKA_USERNAME'); ?>:</label>
			</td>
			<td>
				<input type="text" id="username" name="username" class="input_box" size="70" value="" />
			</td>
		</tr>
		<tr>
			<td width="120">
				<label for="password"><?php echo JText::_('HIKA_PASSWORD'); ?>:</label>
			</td>
			<td>
				<input type="password" id="password" name="password" class="input_box" size="70" value="" />
			</td>
		</tr>
		</tbody>
		</table>
	</fieldset>
	<?php } ?>

	<table id="hikashop_edit_view" class="adminform table">
	<tr>
		<th><?php
			if($this->element->type_name != HIKASHOP_COMPONENT && !empty($this->element->type_pretty_name)) {
				echo $this->element->type_pretty_name . ' - ';
			}
			$allowDuplicate = false;
			if(substr($this->element->filename,-4) == '.php') {
				if( $this->element->view == 'product' ) {
					if( substr($this->element->filename,0,8) == 'listing_' || substr($this->element->filename,0,5) == 'show_')
						$allowDuplicate = true;
				} else if( $this->element->view == 'category' && substr($this->element->filename,0,8) == 'listing_' ) {
					$allowDuplicate = true;
				}
			}

			if($allowDuplicate) {
				$name = explode('_', substr($this->element->filename, 0, -4), 2);
				echo $this->element->view .' / '.$name[0].'_<input type="text" name="duplicate" id="duplicate" value="'.$name[1].'"/>.php';
			} else {
				echo $this->element->view .' / '. $this->element->filename;
			}
		?></th>
	</tr>
<?php
			$display = '';
			if(!empty( $this->element->structure)) {
?>
	<tr>
		<td>
			<?php
				$this->setLayout('builder');
				echo $this->loadTemplate();
			?>
		</td>
	</tr>
<?php
			}
?>
	<tr <?php echo $display; ?>>
		<td>
			<?php echo $this->editor->displayCode('filecontent',$this->element->content); ?>
		</td>
	</tr>
	</table>

	<div class="clr"></div>

	<input type="hidden" name="id" value="<?php echo $this->element->id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT;?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getString('ctrl');?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
