<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="iframedoc" id="iframedoc"></div>
<?php 
	$extra_class = "hika_j3_search";
	$extra_id = "id='hikashop_listing_filters_id'";
	$openfeatures_class = '';
	if (HIKASHOP_J40) {
		$extra_class = "hika_j4_search";
		$openfeatures_class = "hidden-features";
	}
?>
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=taxation" method="post"  name="adminForm" id="adminForm">
<div class="hk-row-fluid">
	<div class="hkc-md-4 <?php echo $extra_class; ?>">
<?php
	echo $this->loadHkLayout('search', array());
?>
	</div>
	<div <?php echo $extra_id; ?> class="hkc-md-8 hikashop_listing_filters <?php echo $openfeatures_class; ?>">
<?php
	 if ( !empty( $this->extrafilters)) {
		 foreach($this->extrafilters as $name => $filterObj) {
			 if(is_string($filterObj)){
				 echo $filterObj;
			 }elseif( isset( $filterObj->objSearch) && method_exists($filterObj->objSearch,'displayFilter')){
				 echo $filterObj->objSearch->displayFilter($name, $this->pageInfo->filter);
			 }else if ( isset( $filterObj->filter_html_search)){
				 echo $filterObj->filter_html_search;
			 }
		 }
	 }

	if ( !empty( $this->extrafilters)) {
		foreach($this->extrafilters as $name => $filterObj) {
			if(is_string($filterObj)){
				echo $filterObj;
			}elseif(isset( $filterObj->objDropdown) && method_exists($filterObj->objDropdown,'displayFilter')){
				echo $filterObj->objDropdown->displayFilter($name, $this->pageInfo->filter);
			}else if ( isset( $filterObj->filter_html_dropdown)){
				echo $filterObj->filter_html_dropdown;
			}
		}
	}
	if(!is_numeric($this->pageInfo->filter->filter_start) && !empty($this->pageInfo->filter->filter_start)) $this->pageInfo->filter->filter_start = strtotime($this->pageInfo->filter->filter_start);
	if(!is_numeric($this->pageInfo->filter->filter_end) && !empty($this->pageInfo->filter->filter_end)) $this->pageInfo->filter->filter_end = strtotime($this->pageInfo->filter->filter_end);
	echo JText::_('FROM').' ';
	echo JHTML::_('calendar', hikashop_getDate((@$this->pageInfo->filter->filter_start?@$this->pageInfo->filter->filter_start:''),'%d %B %Y'), 'filter_start','period_start',hikashop_getDateFormat('%d %B %Y'),array('size'=>'10',''));
	echo ' '.JText::_('TO').' ';
	echo JHTML::_('calendar', hikashop_getDate((@$this->pageInfo->filter->filter_end?@$this->pageInfo->filter->filter_end:''),'%d %B %Y'), 'filter_end','period_end',hikashop_getDateFormat('%d %B %Y'),array('size'=>'10',''));
	$this->category->multiple = true;
	echo $this->category->display("filter_status",$this->pageInfo->filter->filter_status,'',false);
?>
		<button class="btn btn-primary" onclick="this.form.submit();"><?php echo JText::_( 'FILTER' ); ?></button>
	</div>
</div>
<?php 
	echo $this->loadHkLayout('columns', array()); 
?>
	<table id="hikashop_tax_listing" class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('TAX_NAMEKEY'), 'a.tax_namekey', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('RATE'), 'a.tax_rate', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<?php
					$count_extrafields = 2;
					if(count($this->currencies)>1){
						foreach($this->currencies as $id => $currency){
							echo '<th class="hikashop_amount_currency_'.$id.'_title title">'.JText::sprintf('AMOUNT_X',$currency->currency_code).'</th>'."\r\n";
							echo '<th class="hikashop_tax_currency_'.$id.'_title title">'.JText::sprintf('TAXCLOUD_TAX',$currency->currency_code).'</th>'."\r\n";
						}
						$count_extrafields += count($this->currencies)*2;
					}
					echo '<th class="hikashop_amount_main_currency_title title">'.JText::_('TOTAL_AMOUNT').'</th>'."\r\n";
					echo '<th class="hikashop_tax_main_currency_title title">'.JText::_('TOTAL_TAX_AMOUNT').'</th>'."\r\n";

					if(!empty($this->extrafields)) {
						foreach($this->extrafields as $namekey => $extrafield) {
							echo '<th class="hikashop_tax_'.$namekey.'_title title">'.$extrafield->name.'</th>'."\r\n";
						}
						$count_extrafields += count($this->extrafields);
					}
				?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="<?php echo (4+$count_extrafields);?>">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
				$k = $i = 0;
				foreach($this->rows as $row){
					if (isset($row->tr_type)) {
						if($row->tr_type == 'title') {
							echo '<tr><td colspan="'. (4+$count_extrafields).'">'.$row->tax_namekey.'</td></tr>';
							continue;
						}
?>
				<tr class="<?php echo "row$k"; ?>">
					<td class="hk_center">
						<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td class="hk_center">
					</td>
					<td class="hk_center">
						<p><?php echo $row->tax_namekey; ?></p>
					</td>
			<?php		
				} else {
			?>
				<tr class="<?php echo "row$k"; ?>">
					<td class="hk_center">
					<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td class="hk_center">
						<?php echo JHTML::_('grid.id', $i, $row->tax_namekey ); ?>
					</td>
					<td>
						<a href="<?php echo hikashop_completeLink('tax&task=edit&tax_namekey='.urlencode($row->tax_namekey).'&return='.$this->return); ?>">
							<?php echo $row->tax_namekey; ?>
						</a>
					</td>
			<?php 
				} 
			?>		<td>
						<?php echo $row->tax_rate*100.0; ?>%
					</td>
					<?php
						if(count($this->currencies)>1){
							foreach($this->currencies as $id => $currency){
								echo '<td class="hikashop_amount_currency_'.$id.'_value">'.$this->currencyHelper->format($row->amounts[$id],$id).'</td>'."\r\n";
								echo '<td class="hikashop_tax_currency_'.$id.'_value">'.$this->currencyHelper->format($row->tax_amounts[$id],$id).'</td>'."\r\n";
							}
						}
						echo '<td class="hikashop_amount_main_currency_value">'.$this->currencyHelper->format($row->amount,$this->main_currency).'</td>'."\r\n";
						echo '<td class="hikashop_tax_main_currency_value">'.$this->currencyHelper->format($row->tax_amount,$this->main_currency).'</td>'."\r\n";
						if(!empty($this->extrafields)) {
							foreach($this->extrafields as $namekey => $extrafield) {
								$value = '';
								if( isset($extrafield->value)) {
									$n = $extrafield->value;
									$value = $row->$n;
								} else if(!empty($extrafield->obj)) {
									$n = $extrafield->obj;
									$value = $n->showfield($this, $namekey, $row);
								} else if( isset( $row->$namekey)) {
									$value = $row->$namekey;
								}
								echo '<td class="hikashop_tax_'.$namekey.'_value">'.$value.'</td>'."\r\n";
							}
						}
					?>
				</tr>
				<?php
					$k = 1-$k;
					$i++;
				}
			?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="return" value="<?php echo $this->return;?>" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
