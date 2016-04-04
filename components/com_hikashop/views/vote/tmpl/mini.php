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
$row =& $this->rows;
$config = hikashop_config();
if($row->vote_enabled != 1)
	return;

$voteAccess = $config->get('access_vote','public');
$user = (!is_null(hikashop_loadUser()))?hikashop_loadUser():false;
$hasBought = false;
if($voteAccess == 'buyed' && $user){
	$voteClass = hikashop_get('class.vote');
	$hasBought = $voteClass->hasBought($row->vote_ref_id, $user);
}
$canVote = $voteAccess == 'public' || ($voteAccess == 'registered' && $user) || ($voteAccess == 'buyed' && $hasBought);

$row->hikashop_vote_average_score = (float)hikashop_toFloat($row->hikashop_vote_average_score);
JRequest::setVar("rate_rounded",$row->hikashop_vote_average_score_rounded);
JRequest::setVar("nb_max_star",$row->hikashop_vote_nb_star);
$select_id = "select_id_".$row->vote_ref_id;
if(!empty($row->main_div_name)){
	$select_id .= "_".$row->main_div_name;
}else{
	$select_id .= "_hikashop_main_div_name";
}
if($row->hikashop_vote_total_vote == '0'){
	$tooltip = JText::_('HIKA_NO_VOTE');
}else{
	$user_rating = JText::_('HIKA_NO_VOTE');
	if(isset($this->user_vote->vote_rating))
		$user_rating = $this->user_vote->vote_rating;
	$tooltip = JText::sprintf('HIKA_VOTE_TOOLTIP',$row->hikashop_vote_average_score,$row->hikashop_vote_total_vote,$user_rating);
}
?>
<div class="hikashop_vote_stars">
<?php
if($config->get('enable_status_vote', 'vote') != 'both' &&  $canVote) {
?>
	<input type="hidden" name="hikashop_vote_rating" data-votetype="<?php echo $row->type_item; ?>" data-max="<?php echo $row->hikashop_vote_nb_star; ?>" data-ref="<?php echo $row->vote_ref_id;?>" data-rate="<?php echo $row->hikashop_vote_average_score_rounded; ?>" data-original-title="<?php echo $tooltip; ?>" id="<?php echo $select_id;?>" />
	<span id="hikashop_vote_status_<?php echo $row->vote_ref_id;?>" class="hikashop_vote_notification_mini"></span>
<?php
} else {
?>
	<div class="hk-rating" data-original-title="<?php echo $tooltip; ?>" data-toggle="hk-tooltip">
<?php
	for($i = 1; $i <= $row->hikashop_vote_average_score_rounded; $i++) {
		echo '<span class="hk-rate-star state-full"></span>';
	}
	for($i = $row->hikashop_vote_average_score_rounded; $i < $row->hikashop_vote_nb_star; $i++) {
		echo '<span class="hk-rate-star state-empty"></span>';
	}
?>
	</div>
<?php
}
?>
	<input type="hidden" class="hikashop_vote_rating" data-rate="<?php echo $row->hikashop_vote_average_score_rounded; ?>" />
</div>
