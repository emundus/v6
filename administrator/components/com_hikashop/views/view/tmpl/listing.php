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
function hikashopRemoveCustom(id){
	if(confirm(\''.JText::_('HIKA_VALIDDELETEITEMS',true).'\')){
		document.getElementById(\'view_id\').value = id;
		submitform(\'remove\');
	}
	return false;
}';
$doc = JFactory::getDocument();
$doc->addScriptDeclaration($js);
?>
<div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('view'); ?>" method="post"  name="adminForm" id="adminForm">
<div class="hk-row-fluid">
	<div class="hkc-xs-4 hika_j4_search">
<?php
	echo $this->loadHkLayout('search', array());
?>
	</div>
	<div id="hikashop_listing_filters_id" class="hkc-xs-8 hikashop_listing_filters <?php echo $this->openfeatures_class; ?>">
<?php
	if(!empty($this->pluginViews)) {
		$values = array(
			JHTML::_('select.option', '', JText::_('ALL')),
			JHTML::_('select.option', HIKASHOP_COMPONENT, HIKASHOP_NAME)
		);
		$components = array();
		foreach($this->pluginViews as $view) {
			if(!isset($components[$view['component']])) {
				$values[] = JHTML::_('select.option', $view['component'], $view['name']);
				$components[$view['component']] = true;
			}
		}
		unset($components);
		echo JHTML::_('hikaselect.genericlist', $values, 'component', 'class="custom-select" onchange="document.adminForm.submit();return false;" size="1"', 'value', 'text', $this->pageInfo->filter->component);
	}
	echo JHTML::_('hikaselect.genericlist', $this->viewTypes, "viewType", 'class="custom-select" size="1" onchange="document.adminForm.submit();return false;"', 'value', 'text', @$this->pageInfo->filter->viewType);
	echo $this->templateType->display("template",$this->pageInfo->filter->template,$this->templateValues);
	echo $this->viewType->display("client_id",$this->pageInfo->filter->client_id);
?>
	</div>
</div>
<?php 
	echo $this->loadHkLayout('columns', array()); 
?>
	<table id="hikashop_view_listing" class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title">
					<?php echo JText::_('CLIENT'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('HIKA_TEMPLATE'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('HIKA_VIEW'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('HIKA_FILE'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('HIKASHOP_ACTIONS'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="6">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
				$k = 0;
				$i = 0;
				foreach($this->rows as $row) {
			?>
				<tr class="row<?php echo $k;?>">
					<td class="hk_center">
					<?php echo $this->pagination->getRowOffset($i);
					?>
					</td>
					<td>
						<?php
						if($row->client_id){
							echo JText::_('BACK_END');
						}else{
							echo JText::_('FRONT_END');
						}
						?>
					</td>
					<td>
						<?php echo $row->template; ?>
						<a href="<?php echo JRoute::_('index.php?option=com_templates&task=edit&cid[]='.strip_tags($row->template).'&client='.$row->client_id); ?>">
							<i class="fa fa-chevron-right"></i>
						</a><?php
						if($row->type_name != HIKASHOP_COMPONENT && !empty($row->component)) {
							echo ' - ' . $row->component;
						}
						?>
					</td>
					<td>
						<?php echo $row->view; ?>
					</td>
					<td>
						<?php if($this->manage){ ?>
							<a href="<?php echo hikashop_completeLink('view&task=edit&id='.str_replace('.','%2E',strip_tags($row->id)));?>">
						<?php } ?>
								<?php echo $row->file; ?>
						<?php if($this->manage){ ?>
							</a>
						<?php } ?>
					</td>
					<td class="hk_center">
					<?php if($row->overriden){ ?>
							<a class="hikabtn hikabtn-primary badge" href="<?php echo hikashop_completeLink('view&task=diff&id='.str_replace('.','%2E',strip_tags($row->id))); ?>">
								<i class="far fa-file-code"></i> <?php echo JText::_('SEE_MODIFICATIONS'); ?>
							</a>
						<?php if($this->delete){ ?>
							<a class="hikabtn hikabtn-error badge" href="<?php echo hikashop_completeLink('view&task=remove&cid='.$row->id); ?>" onclick="return hikashopRemoveCustom('<?php echo $row->id?>');">
								<i class="fas fa-times"></i> <?php echo JText::_('REMOVE_CUSTOMIZATION'); ?>
							</a>
						<?php } ?>
					<?php } ?>
					</td>
				</tr>
			<?php
					$k = 1-$k;
					$i++;
				}
			?>
		</tbody>
	</table>
	<input type="hidden" id="view_id" name="cid[]" value="" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
