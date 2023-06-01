<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php $js ='
function hikashopRemoveCustom(type, id){
	if(confirm(\''.JText::_('HIKA_VALIDDELETEITEMS',true).'\')){
		document.getElementById(\'email_id\').value = id;
		document.getElementById(\'email_type\').value = type;
		submitform(\'remove\');
	}
	return false;
}';
$doc = JFactory::getDocument();
$doc->addScriptDeclaration($js);
?>
<div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('email'); ?>" method="post"  name="adminForm" id="adminForm">
<?php if(!empty($this->ftp)) { ?>
	<fieldset title="<?php echo JText::_('DESCFTPTITLE'); ?>">
		<legend><?php echo JText::_('DESCFTPTITLE'); ?></legend>
		<?php echo JText::_('DESCFTP'); ?>
<?php if(JError::isError($this->ftp)){ ?>
		<p><?php
			if(method_exists($this->ftp, '__toString'))
				$msg = $this->ftp->__toString();
			else
				$msg = @$this->ftp->message;
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
	<table id="hikashop_email_listing" class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title">
					<?php echo JText::_('HIKA_EMAIL'); ?>
				</th>
<?php if(hikashop_level(2)) { ?>
				<th class="title titletoggle">
					<?php echo JText::_('REMOVE_CUSTOMIZATION_HTML'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('REMOVE_CUSTOMIZATION_TEXT'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('REMOVE_CUSTOMIZATION_PRELOAD'); ?>
				</th>
<?php } ?>
				<th class="title titletoggle">
					<?php echo JText::_('HIKA_PUBLISHED'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="7">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
<?php
	$k = 0;
	foreach($this->rows as $i => &$row) {
?>
			<tr class="row<?php echo $k; ?>">
				<td class="hk_center">
					<?php echo $this->pagination->getRowOffset($i); ?>
				</td>
				<td>
<?php if($this->manage && hikashop_level(2)) { ?>
					<a href="<?php echo hikashop_completeLink('email&task=edit&mail_name='.$row->file);?>">
<?php }
		if(!empty($row->name))
			echo $row->name;
		else {
			$key = strtoupper($row->file);
			$trans = JText::_($key.'_EMAIL');
			if($trans == $key.'_EMAIL') {
				$trans = JText::_($key);
			}
			echo $trans;
		}

		if($this->manage && hikashop_level(2)) { ?>
					</a>
<?php } ?>
				</td>
<?php if(hikashop_level(2)) { ?>
				<td class="hk_center">
<?php if($row->overriden_html){ ?>
<?php if($this->delete){ ?>
					<a title="<?php echo JText::_('REMOVE_CUSTOMIZATION_HTML'); ?>" href="<?php echo hikashop_completeLink('email&task=remove&type=html&mail_name='.$row->file); ?>" onclick="return hikashopRemoveCustom('html','<?php echo $row->file?>');">
<?php } ?>
						<i class="fas fa-trash"></i>
<?php if($this->delete){ ?>
					</a>
<?php } ?>
<?php } ?>
				</td>
				<td class="hk_center">
<?php if($row->overriden_text){ ?>
<?php if($this->delete){ ?>
					<a title="<?php echo JText::_('REMOVE_CUSTOMIZATION_TEXT'); ?>" href="<?php echo hikashop_completeLink('email&task=remove&type=text&mail_name='.$row->file); ?>" onclick="return hikashopRemoveCustom('text','<?php echo $row->file?>');">
<?php } ?>
						<i class="fas fa-trash"></i>
<?php if($this->delete){ ?>
					</a>
<?php } ?>
<?php } ?>
				</td>
				<td class="hk_center">
<?php if($row->overriden_preload){ ?>
<?php if($this->delete){ ?>
					<a title="<?php echo JText::_('REMOVE_CUSTOMIZATION_PRELOAD'); ?>" href="<?php echo hikashop_completeLink('email&task=remove&type=preload&mail_name='.$row->file); ?>" onclick="return hikashopRemoveCustom('preload','<?php echo $row->file?>');">
<?php } ?>
						<i class="fas fa-trash"></i>
<?php if($this->delete){ ?>
					</a>
<?php } ?>
<?php } ?>
				</td>
<?php } ?>
				<td class="hk_center">
<?php if($this->manage) {
		$publishedid = 'config_value-'.$row->file.'.published'; ?>
					<span id="<?php echo $publishedid ?>" class="loading"><?php echo $this->toggleClass->toggle($publishedid, (int)$row->published,'config') ?></span>
<?php }else{ echo $this->toggleClass->display('activate', (int)@$row->published); } ?>
				</td>
			</tr>
<?php
		$k = 1-$k;
	}
	unset($row);
?>
		</tbody>
	</table>
	<input type="hidden" id="email_id" name="mail_name" value="" />
	<input type="hidden" id="email_type" name="type" value="" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
