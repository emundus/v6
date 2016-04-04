<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.1
 * @author	hikashop.com
 * @copyright	(C) 2010-2016 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$sort = JRequest::getString('sort_comment','');
$row = & $this->rows;
$pagination = & $this->pagination;
if($row->comment_enabled != 1)
	return;

if(($row->hikashop_vote_con_req_list == 1 && hikashop_loadUser() != "") || $row->hikashop_vote_con_req_list == 0) {
?>

<div class="hikashop_listing_comment"><?php echo JText::_('HIKASHOP_LISTING_COMMENT');?>
<?php if($row->vote_comment_sort_frontend){ ?>
	<span style="float: right;" class="hikashop_sort_listing_comment">
		<select name="sort_comment" onchange="refreshCommentSort(this.value); return false;">
			<option <?php if($sort == 'date')echo "selected"; ?> value="date"><?php echo JText::_('HIKASHOP_COMMENT_ORDER_DATE');?></option>
			<option <?php if($sort == 'helpful')echo "selected"; ?> value="helpful"><?php echo JText::_('HIKASHOP_COMMENT_ORDER_HELPFUL');?></option>
		</select>
	</span>
<?php } ?>
</div>
<?php

	$i = 0;
	foreach($this->elts as $elt) {
		if(empty($elt->vote_comment))
			continue;
?>
<table class="hika_comment_listing" style="width:100%;">
	<tr>
		<td class="hika_comment_listing_name">
<?php if ($elt->vote_pseudo == '0') { ?>
			<span class="hika_vote_listing_username"><?php echo $elt->username; ?> </span>
<?php } else { ?>
			<span class="hika_vote_listing_username" ><?php echo $elt->vote_pseudo; ?></span>
<?php } ?>
		</td>
		<td class="hika_comment_listing_stars"><?php
		$nb_star_vote = $elt->vote_rating;
		JRequest::setVar("nb_star", $nb_star_vote);
		$nb_star_config = $row->vote_star_number;
		JRequest::setVar("nb_max_star", $nb_star_config);
		if($nb_star_vote != 0) {
			for($k=0; $k < $nb_star_vote; $k++ ){
				?><span class="hika_comment_listing_full_stars" ></span><?php
			}
			$nb_star_empty = $nb_star_config - $nb_star_vote;
			if($nb_star_empty != 0){
				for($j=0; $j < $nb_star_empty; $j++ ){
					?><span class="hika_comment_listing_empty_stars" ></span><?php
				}
			}
		}
		?></td>
		<td>
			<div class="hika_comment_listing_notification" id="<?php echo $elt->vote_id; ?>"><?php
		if($elt->total_vote_useful != 0){
			if($elt->vote_useful == 0) {
				$hika_useful = $elt->total_vote_useful / 2;
			} else if($elt->total_vote_useful == $elt->vote_useful) {
				$hika_useful = $elt->vote_useful;
			} else if($elt->total_vote_useful == -$elt->vote_useful) {
				$hika_useful = 0;
			} else {
				$hika_useful = ($elt->total_vote_useful + $elt->vote_useful) /2;
			}

			$hika_useless = $elt->total_vote_useful - $hika_useful;
			if($row->useful_style == 'helpful'){
				echo JText::sprintf('HIKA_FIND_IT_HELPFUL', $hika_useful, $elt->total_vote_useful);
			}
		} else {
			$hika_useless = 0;
			$hika_useful  = 0;
			if($row->useful_style == 'helpful' && $row->useful_rating == 1) {
				echo JText::_('HIKASHOP_NO_USEFUL');
			}
		}
			?></div>
		</td>
<?php
		if($row->useful_rating == 1) {
			if($row->hide == 0 && $elt->already_vote == 0 && $elt->vote_user_id != hikashop_loadUser() && $elt->vote_user_id != hikashop_getIP()){

				if($row->useful_style == 'thumbs') {
?>
		<td class="hika_comment_listing_useful_p"><?php
			echo $hika_useful;
		?></td>
<?php
				}
?>
		<td class="hika_comment_listing_useful" title="<?php echo JText::_('HIKA_USEFUL'); ?>" onclick="hikashop_vote_useful(<?php echo $elt->vote_id;?>,1);"></td>
<?php if($row->useful_style == 'thumbs'){?>
		<td class="hika_comment_listing_useful_p"><?php
			echo $hika_useless;
		?></td>
<?php } ?>
		<td class="hika_comment_listing_useless" title="Useless" onclick="hikashop_vote_useful(<?php echo $elt->vote_id;?>,2);"></td>
<?php
			} else if($row->useful_style == "thumbs") {
?>
		<td class="hika_comment_listing_useful_p"><?php
			echo $hika_useful;
		?></td>
		<td class="hika_comment_listing_useful locked"></td>
		<td class="hika_comment_listing_useless_p"><?php
			echo $hika_useless;
		?></td>
		<td class="hika_comment_listing_useless locked"></td>
<?php
			} else {
?>
		<td class="hika_comment_listing_useful_p hide"></td>
		<td class="hika_comment_listing_useful locked hide"></td>
		<td class="hika_comment_listing_useless_p hide"></td>
		<td class="hika_comment_listing_useless locked hide"></td>
<?php
			}
		}
?>
	</tr>
<?php if($row->show_comment_date) { ?>
	<tr>
		<td colspan="8"><?php
			$voteClass = hikashop_get('class.vote');
			$vote = $voteClass->get($elt->vote_id);
			echo hikashop_getDate($vote->vote_date);
		?></td>
	</tr>
<?php } ?>
	<tr>
		<td colspan="8">
			<div id="<?php echo $i++; ?>" class="hika_comment_listing_content"><?php echo $elt->vote_comment; ?></div>
		</td>
	</tr>
	<tr>
		<td colspan="8" class="hika_comment_listing_bottom">
<?php if(!empty ($elt->purchased)) { ?>
			<span class="hikashop_vote_listing_useful_bought"><?php echo JText::_('HIKASHOP_VOTE_BOUGHT_COMMENT'); ?></span>
<?php } ?>
		</td>
	</tr>
</table>
<?php

	}

	if(!count($this->elts)) {
?>
<table class="hika_comment_listing">
	<tr>
		<td class="hika_comment_listing_empty"><?php
			echo JText::_('HIKASHOP_NO_COMMENT_YET');
		?></td>
	</tr>
</table>
<?php
	} else {
		$this->pagination->form = '_hikashop_comment_form';
?>
<div class="pagination"><?php
	echo $this->pagination->getListFooter();
	echo $this->pagination->getResultsCounter();
?></div>
<?php
	}
}

if($row->vote_comment_sort_frontend) {
	$jconfig = JFactory::getConfig();
	$sef = (HIKASHOP_J30 ? $jconfig->get('sef') : $jconfig->getValue('config.sef'));

	$sortUrl = $sef ? '/sort_comment-' : '&sort_comment=';
?>
<script type="text/javascript">
function refreshCommentSort(value){
	var url = window.location.href;
	if(url.match(/sort_comment/g)){
		url = url.replace(/\/sort_comment.?[a-z]*/g,'').replace(/&sort_comment.?[a-z]*/g,'');
	}
	url = url+'<?php echo $sortUrl; ?>'+value;
	document.location.href = url;
}
</script>
<?php }
