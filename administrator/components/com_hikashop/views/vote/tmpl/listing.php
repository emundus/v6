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
<form action="<?php echo hikashop_completeLink('vote'); ?>" method="post" name="adminForm" id="adminForm">
<div class="hk-row-fluid">
	<div class="hkc-md-5 hika_j4_search"><?php
		echo $this->loadHkLayout('search', array());
	?></div>
	<div class="hkc-md-7 hikashop_listing_filters">
	</div>
</div>

<?php
	$colspan = 11;
	$backend_listing_vote = hikaInput::get()->getVar('backend_listing_vote', 'both', 'default', 'string', 0);
?>
<?php 
	echo $this->loadHkLayout('columns', array()); 
?>
	<table id="hikashop_vote_listing" class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="hikashop.checkAll(this);" />
				</th>
				<th class="title titlebox">
					<?php echo JText::_('HIKA_EDIT'); ?>
				</th>
				<th class="title title_product_id">
					<?php echo JHTML::_('grid.sort', JText::_('HIKASHOP_ITEM'), 'a.vote_ref_id', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<?php
					$manyTypes = 0;
					if(defined('HIKAMARKET_COMPONENT')) {
						$manyTypes = 1;
					}
					if($manyTypes){ ?>
				<th class="title title_type">
					<?php  echo JHTML::_('grid.sort', JText::_('HIKA_TYPE'), 'a.vote_type', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<?php } ?>
				<?php if($this->pageInfo->enabled == 2 || $this->pageInfo->enabled == 3){?>
				<th class="title title_comment">
					<?php echo JHTML::_('grid.sort', JText::_('COMMENT'), 'a.vote_comment', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<?php
					}
					if($this->pageInfo->enabled == 1 || $this->pageInfo->enabled == 3){
				?>
				<th class="title title_vote">
					<?php echo JHTML::_('grid.sort', JText::_('RATING'), 'a.vote_rating', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<?php } ?>
				<th class="title title_username">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_USERNAME'), 'a.vote_pseudo', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
<?php
if($this->config->get('vote_ip', 1)) {
	$colspan++;
?>
				<th class="title title_ip">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_IP'), 'a.vote_ip', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
<?php
} ?>
				<th class="title title_email">
					<?php echo JHTML::_('grid.sort', JText::_('HIKA_EMAIL'), 'a.vote_email', $this->pageInfo->filter->order->dir,$this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title titledate">
				<?php echo JHTML::_('grid.sort', JText::_( 'DATE' ), 'a.vote_date',$this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JHTML::_('grid.sort',   JText::_('HIKA_PUBLISHED'), 'a.vote_published', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort',   JText::_( 'ID' ), 'a.vote_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="<?php echo $colspan; ?>">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
				$k = 0;
				$a = count($this->rows);
				if($a){
					for($i = 0;$i<$a;$i++){
						$row =& $this->rows[$i];

						$publishedid = 'vote_published-'.$row->vote_id;
						$username = isset($row->username)?$row->username:'0';
						$email = isset($row->email)?$row->email:'0';
						$item_name = @$row->item_name;

						if(($backend_listing_vote == 'vote'  && $row->vote_rating!='0') || $backend_listing_vote == 'both' ||  ($backend_listing_vote == 'comment'  && $row->vote_comment!='')){

					?>
						<tr class="<?php echo "row$k"; ?>">
							<td class="hk_center">
							<?php echo $this->pagination->getRowOffset($i); ?>
							</td>
							<td class="hk_center">
								<?php echo JHTML::_('grid.id', $i, $row->vote_id ); ?>
							</td>
							<td class="hk_center">
								<a href="<?php echo hikashop_completeLink('vote&task=edit&cid='.$row->vote_id); ?>"><i class="fas fa-pen"></i></a>
							</td>
							<td>
								<?php
									echo $item_name;
									if($this->pageInfo->manageProduct && $row->vote_type == 'product')
										echo ' <a href="'.hikashop_completeLink('option=com_hikashop&ctrl=product&task=edit&cid[]='.$row->vote_ref_id,false,true).'"><i class="fa fa-chevron-right"></i></a>';
								?>
							</td>
							<?php
								if($manyTypes){ 
							?>
							<td>
								<?php
									echo $row->vote_type;
								?>
							</td>
							<?php } ?>
							<?php if($this->pageInfo->enabled == 2 || $this->pageInfo->enabled == 3){?>
							<td>
								<?php
									if($row->vote_comment == ''){echo "empty";}
									elseif($this->manage){
										echo "<a href=".hikashop_completeLink('vote&task=edit&cid[]='.$row->vote_id,false,true).">"; echo JHTML::tooltip($row->vote_comment, JText::_('FULL_COMMENT'),'', $row->vote_comment_short); echo "</a>";
									}
									else{
										echo JHTML::tooltip($row->vote_comment, JText::_('FULL_COMMENT'),'', $row->vote_comment_short);
									}
								?>
								</a>
							</td>
							<?php
							}
							if($this->pageInfo->enabled == 1 || $this->pageInfo->enabled == 3){?>
							<td>
								<?php
									if($row->vote_rating == '0'){echo "empty";$row->vote_rating = "";}
									elseif($this->manage){echo "<a href=".hikashop_completeLink('vote&task=edit&cid[]='.$row->vote_id,false,true).">".$row->vote_rating."</a>";}
									else{ echo $row->vote_rating;}
								?>
							</td>
							<?php } ?>
							<td>
								<?php
								if(($row->vote_pseudo == '0' || $row->vote_pseudo == '')&& $username !='0' ){
									echo $username;
									if($this->pageInfo->manageUser)
										echo ' <a href="'.hikashop_completeLink('option=com_hikashop&ctrl=user&task=edit&cid[]='.$row->vote_user_id,false,true).'"><i class="fa fa-chevron-right"></i></a>';
								}
								else if($username == '0' && ($row->vote_pseudo == '0' || $row->vote_pseudo == '')){echo '<i>'.JText::_('NO_USERNAME_PROVIDED').'</i>';}
								else{
									echo $row->vote_pseudo;
								}
								?>
							</td>
<?php
if($this->config->get('vote_ip', 1)) {
?>
							<td>
								<?php echo $row->vote_ip; ?>
							</td>
<?php
}
?>
							<td>
								<?php
								if(($row->vote_email == '0' || $row->vote_email == '') && $email !='0' ){
									echo $email;
									if($this->pageInfo->manageUser)
										echo ' <a href="'.hikashop_completeLink('option=com_hikashop&ctrl=user&task=edit&cid[]='.$row->vote_user_id,false,true).'"><i class="fa fa-chevron-right"></i></a>';
								}
								else if($email == 0 && ($row->vote_email == '0' || $row->vote_email == '')){echo '<i>'.JText::_('NO_EMAIL_PROVIDED').'</i>';}
								else{
									echo $row->vote_email;
								} ?>
							</td>
							<td class="order">
								<?php  echo $date = date('d/m/Y h:m:s', $row->vote_date);  ?>
							</td>
							<td class="hk_center">
								<?php if($this->manage){ ?>
									<span id="<?php echo $publishedid?>" class="spanloading"><?php echo $this->toggleClass->toggle($publishedid,(int) $row->vote_published,'vote') ?></span>
								<?php }else{ echo $this->toggleClass->display('activate',$row->vote_published); } ?>
							</td>
							<td width="1%" class="hk_center">
								<?php echo $row->vote_id; ?>
							</td>
						</tr>
					<?php
							$k = 1-$k;
						}
					}
				}
			?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo hikaInput::get()->getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
