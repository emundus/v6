<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$row =& $this->rows;
$config = hikashop_config();
if($row->vote_enabled != 1)
	return;

$voteAccess = $config->get('access_vote','public');
$user = hikashop_loadUser();
$hasBought = false;
if($voteAccess == 'buyed' && !empty($user)){
	$voteClass = hikashop_get('class.vote');
	$hasBought = $voteClass->hasBought($row->vote_ref_id, $user);
}
$canVote = $voteAccess == 'public' || ($voteAccess == 'registered' && !empty($user)) || ($voteAccess == 'buyed' && $hasBought);

if(!$canVote && $row->hikashop_vote_total_vote == '0'){
?>
<div class="hikashop_vote_stars">
	<div class="hk-rating-empty-label">
		<?php echo JText::_('HIKA_NO_VOTE'); ?>
	</div>
	<div class="hk-rating hk-rating-empty" data-original-title="<?php echo JText::_('HIKA_NO_VOTE'); ?>" data-toggle="hk-tooltip">
<?php
	for($i = 1; $i <= $row->hikashop_vote_nb_star; $i++) {
		echo '<span class="hk-rate-star state-empty"></span>';
	}
?>
	</div>
</div>
<?php
	return;
}

$row->hikashop_vote_average_score = (float)hikashop_toFloat($row->hikashop_vote_average_score);
if($row->hikashop_vote_nb_star < $row->hikashop_vote_average_score) {
	$row->hikashop_vote_average_score = $row->hikashop_vote_nb_star;
}
hikaInput::get()->set("rate_rounded",$row->hikashop_vote_average_score_rounded);
hikaInput::get()->set("nb_max_star",$row->hikashop_vote_nb_star);
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
	$tooltip = "<span class='hikashop_vote_tooltip hikashop_vote_tooltip_average'><span class='hikashop_vote_tooltip_label'>".JText::_('VOTE_AVERAGE').": </span><span class='hikashop_vote_tooltip_value'>".$row->hikashop_vote_average_score."</span></span>";
	$tooltip .= "<br/><span class='hikashop_vote_tooltip hikashop_vote_tooltip_total'><span class='hikashop_vote_tooltip_label'>".JText::_('HIKA_VOTE_TOTAL').": </span><span class='hikashop_vote_tooltip_value'>".$row->hikashop_vote_total_vote."</span></span>";
	$tooltip .= "<br/><span class='hikashop_vote_tooltip hikashop_vote_tooltip_customer_vote'><span class='hikashop_vote_tooltip_label'>".JText::_('HIKA_VOTE_CUSTOMER_VOTE').": </span><span class='hikashop_vote_tooltip_value'>".$user_rating."</span></span>";
}
?>
<div class="hikashop_vote_stars">
<?php
if($config->get('enable_status_vote', 'vote') != 'both' && $canVote) {
	if ($row->hikashop_vote_total_vote > 0 && !$this->params->get('listing_product')) {
?>
	<div style="display: none;">
		<div itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
			<span itemprop="ratingValue"><?php echo $row->hikashop_vote_average_score; ?></span>
			<span itemprop="bestRating"><?php echo $row->hikashop_vote_nb_star; ?></span>
			<span itemprop="ratingCount"><?php echo $row->hikashop_vote_total_vote; ?></span>
			<span itemprop="itemReviewed">Product</span>
		</div>
	</div>
<?php
	}
?>
	<input type="hidden" name="hikashop_vote_rating" data-votetype="<?php echo $row->type_item; ?>" data-max="<?php echo $row->hikashop_vote_nb_star; ?>" data-ref="<?php echo $row->vote_ref_id;?>" data-rate="<?php echo $row->hikashop_vote_average_score_rounded; ?>" data-original-title="<?php echo $tooltip; ?>" id="<?php echo $select_id;?>" />
	<span id="hikashop_vote_status_<?php echo $row->vote_ref_id;?>" class="hikashop_vote_notification_mini"></span>
<?php
} else {
	if ($row->hikashop_vote_total_vote > 0 && !$this->params->get('listing_product')) {
?>
	<div style="display: none;">
		<div itemprop="aggregateRating" itemscope itemtype="https://schema.org/AggregateRating">
			<span itemprop="ratingValue"><?php echo $row->hikashop_vote_average_score; ?></span>
			<span itemprop="bestRating"><?php echo $row->hikashop_vote_nb_star; ?></span>
			<span itemprop="ratingCount"><?php echo $row->hikashop_vote_total_vote; ?></span>
			<span itemprop="itemReviewed">Product</span>
		</div>
	</div>
<?php } ?>
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
