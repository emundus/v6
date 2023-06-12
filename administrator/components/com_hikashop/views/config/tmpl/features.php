<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$menudata = array(
	'#features_main' => JText::_('MAIN'),
	'#features_vote' => JText::_('VOTE_AND_COMMENT'),
	'#features_affiliate' => JText::_('AFFILIATE'),
	'#features_sef' => JText::_('SEF_URL_OPTIONS'),
	'#features_filter' => JText::_('FILTER'),
	'#features_atom' => JText::_('ALL_FEED')
);
if(empty($this->affiliate_active))
	unset($menudata['#features_affiliate']);

echo $this->leftmenu(
	'features',
	$menudata
);
?>
<div id="page-features" class="rightconfig-container <?php if(HIKASHOP_BACK_RESPONSIVE) echo 'rightconfig-container-j30';?>">

<!-- FEATURES - MAIN -->
<div id="features_main" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('MAIN'); ?></div>
<table class="hk_config_table table" style="width:100%">

	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('catalogue');?>><?php echo JText::_('CATALOGUE_MODE'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[catalogue]','onchange="if(this.value==1) alert(\''.JText::_('CATALOGUE_MODE_WARNING',true).'\');"',$this->config->get('catalogue'));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('enable_multicart');?>><?php echo JText::_('ENABLE_MULTI_CART'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[enable_multicart]','',$this->config->get('enable_multicart',1));
		?></td>
	</tr>

	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('product_waitlist');?>><?php echo JText::_('ACTIVATE_WAITLIST'); ?></td>
		<td><?php
			if(hikashop_level(1)) {
				echo $this->waitlist->display('config[product_waitlist]', $this->config->get('product_waitlist', 0));
			} else {
				echo hikashop_getUpgradeLink('essential');
			}
		?></td>
	</tr>
<?php if(hikashop_level(1)) { ?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('product_waitlist_sub_limit');?>><?php echo JText::_('WAITLIST_SUBSCRIBE_LIMIT'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[product_waitlist_sub_limit]" value="<?php echo (int)$this->config->get('product_waitlist_sub_limit', 20); ?>"/>
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('product_waitlist_send_limit');?>><?php echo JText::_('WAITLIST_SEND_LIMIT'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[product_waitlist_send_limit]" value="<?php echo (int)$this->config->get('product_waitlist_send_limit', 5); ?>"/>
		</td>
	</tr>
<?php } ?>

	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('show_compare');?>><?php echo JText::_('COMPARE_MODE'); ?></td>
		<td><?php
			if(hikashop_level(2)) {
				echo $this->compare->display('config[show_compare]', $this->config->get('show_compare'));
			} else {
				echo hikashop_getUpgradeLink('business');
			}
		?></td>
	</tr>
<?php if(hikashop_level(2) && $this->config->get('show_compare')) { ?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('compare_limit');?>><?php echo JText::_('COMPARE_LIMIT'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[compare_limit]" value="<?php echo $this->config->get('compare_limit','5'); ?>"/>
		</td>
	</tr>
	<?php if($this->config->get('enable_wishlist',1)) { ?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('compare_to_wishlist');?>><?php echo JText::_('COMPARE_TO_WISHLIST'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[compare_to_wishlist]', '', $this->config->get('compare_to_wishlist', 1));
		?></td>
	</tr>
	<?php } ?>
<?php } ?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('enable_wishlist');?>><?php echo JText::_('ENABLE_WISHLIST'); ?></td>
		<td><?php
			if(hikashop_level(1)) {
				echo JHTML::_('hikaselect.booleanlist', 'config[enable_wishlist]','',$this->config->get('enable_wishlist',1));
			} else {
				echo hikashop_getUpgradeLink('essential');
			}
		?></td>
	</tr>
<?php if(hikashop_level(1) && $this->config->get('enable_wishlist',1)) { ?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('hide_wishlist_guest');?>><?php echo JText::_('HIDE_WISHLIST_GUEST'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[hide_wishlist_guest]', '', $this->config->get('hide_wishlist_guest', 1));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('display_add_to_wishlist_for_free_products');?>><?php echo JText::_('DISPLAY_ADD_TO_WISHLIST_BUTTON_FOR_FREE_PRODUCT'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[display_add_to_wishlist_for_free_products]', '', $this->config->get('display_add_to_wishlist_for_free_products', 1));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('enable_multiwishlist');?>><?php echo JText::_('ENABLE_MULTI_WISHLIST'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[enable_multiwishlist]', '', $this->config->get('enable_multiwishlist', 1));
		?></td>
	</tr>
<?php } ?>
</table>
	</div></div>
</div>

<!-- FEATURES - VOTE -->
<?php
$comment_active = in_array($this->config->get('enable_status_vote', 0), array('comment', 'two', 'both'));
$vote_active = in_array($this->config->get('enable_status_vote', 0), array('vote', 'two', 'both'));
?>
<div id="features_vote" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('VOTE_AND_COMMENT'); ?></div>
<table class="hk_config_table table" style="width:100%">

	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('enable_status_vote');?>><?php echo JText::_('ENABLE_STATUS'); ?></td>
		<td><?php
	$arr = array(
		JHTML::_('select.option', 'nothing', JText::_('HIKA_VOTE_NOTHING') ),
		JHTML::_('select.option', 'vote', JText::_('HIKA_VOTE_ONLY') ),
		JHTML::_('select.option', 'comment', JText::_('HIKA_COMMENT_ONLY') ),
		JHTML::_('select.option', 'two', JText::_('HIKA_VOTE_OR_COMMENT') ),
		JHTML::_('select.option', 'both', JText::_('HIKA_VOTE_AND_COMMENT') )
	);
	echo JHTML::_('hikaselect.genericlist', $arr, "config[enable_status_vote]", 'class="custom-select" size="1"', 'value', 'text', $this->config->get('enable_status_vote', 0));
		?></td>
	</tr>
<?php if($vote_active || $comment_active){ ?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('access_vote');?>><?php echo JText::_('ACCESS_VOTE'); ?></td>
		<td><?php
	$arr = array(
		JHTML::_('select.option', 'public', JText::_('HIKA_VOTE_PUBLIC') ),
		JHTML::_('select.option', 'registered', JText::_('HIKA_VOTE_REGISTERED') ),
		JHTML::_('select.option', 'buyed', JText::_('HIKA_VOTE_BOUGHT') )
	);
	echo JHTML::_('hikaselect.genericlist', $arr, "config[access_vote]", 'class="custom-select" size="1"', 'value', 'text', $this->config->get('access_vote', 0));
		?></td>
	</tr>
<?php } ?>
<?php if($vote_active){ ?>
<?php	if($vote_active || $comment_active){ ?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('defparams_show_vote_product');?>><?php echo JText::_('DISPLAY_VOTE_OF_PRODUCTS');?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[default_params][show_vote_product]' , '', @$this->default_params['show_vote_product']);
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('defparams_show_vote');?>><?php echo JText::_('DISPLAY_VOTE_IN_CATEGORIES'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[default_params][show_vote]', '', @$this->default_params['show_vote']);
		?></td>
	</tr>
<?php } ?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('vote_star_number');?>><?php echo JText::_('STAR_NUMBER'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[vote_star_number]" value="<?php echo $this->config->get('vote_star_number', 5);?>" />
		</td>
	</tr>
<?php } ?>
<?php if($comment_active){ ?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('email_comment');?>><?php echo JText::_('EMAIL_COMMENT'); ?></td>
		<td>
			<?php echo JHTML::_('hikaselect.booleanlist', "config[email_comment]", '', $this->config->get('email_comment', 0)); ?>
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('published_comment');?>><?php echo JText::_('PUBLISHED_COMMENT'); ?></td>
		<td>
			<?php echo JHTML::_('hikaselect.booleanlist', "config[published_comment]", '', $this->config->get('published_comment', 1)); ?>
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('email_each_comment');?>><?php echo JText::_('EMAIL_NEW_COMMENT'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[email_each_comment]" value="<?php echo $this->config->get('email_each_comment');?>" />
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('comment_by_person_by_product');?>><?php echo JText::_('COMMENT_BY_PERSON_BY_PRODUCT'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[comment_by_person_by_product]" value="<?php echo $this->config->get('comment_by_person_by_product', 5);?>" />
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('number_comment_product');?>><?php echo JText::_('NUMBER_COMMENT_BY_PRODUCT'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[number_comment_product]" value="<?php echo $this->config->get('number_comment_product', 30); ?>" />
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('vote_comment_sort');?>><?php echo JText::_('VOTE_COMMENT_SORT'); ?></td>
		<td><?php
	$arr = array(
		JHTML::_('select.option', 'date', JText::_('DATE') ),
		JHTML::_('select.option', 'date_desc', JText::_('DATE_DESC') )
	);
	if($this->config->get('useful_rating', 1)){
		$arr[] = JHTML::_('select.option', 'helpful', JText::_('HELPFUL') );
	}
	echo JHTML::_('hikaselect.genericlist', $arr, "config[vote_comment_sort]", 'class="custom-select" size="1"', 'value', 'text', $this->config->get('vote_comment_sort', 0));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('vote_comment_sort_frontend');?>><?php echo JText::_('VOTE_COMMENT_SORT_FRONTEND'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', "config[vote_comment_sort_frontend]", '', $this->config->get('vote_comment_sort_frontend', 0));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('show_listing_comment');?>><?php echo JText::_('SHOW_LISTING_COMMENT'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', "config[show_listing_comment]", '', $this->config->get('show_listing_comment', 0));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('show_comment_date');?>><?php echo JText::_('SHOW_COMMENT_DATE'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', "config[show_comment_date]" , '', $this->config->get('show_comment_date', 0));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('useful_rating');?>><?php echo JText::_('USEFUL_RATING'); ?></td>
		<td>
			<?php echo JHTML::_('hikaselect.booleanlist', "config[useful_rating]" , '', $this->config->get('useful_rating', 1)); ?>
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('register_note_comment');?>><?php echo JText::_('REGISTER_NOTE_COMMENT'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', "config[register_note_comment]" , '', $this->config->get('register_note_comment', 0));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('vote_useful_style');?>><?php echo JText::_('VOTE_USEFUL_STYLE'); ?></td>
		<td><?php
	$arr = array(
		JHTML::_('select.option', 'helpful', JText::_('HIKA_VOTE_USEFUL_COUNT') ),
		JHTML::_('select.option', 'thumbs', JText::_('HIKA_VOTE_USEFUL_HAND') ),
	);
	echo JHTML::_('hikaselect.genericlist', $arr, "config[vote_useful_style]", 'class="custom-select" size="1"', 'value', 'text', $this->config->get('vote_useful_style', 0));
		?></td>
	</tr>
<?php
}
if($vote_active || $comment_active) {
?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('vote_ip');?>><?php echo JText::_('LOG_IP_ADDRESS'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[vote_ip]', '', $this->config->get('vote_ip', 1));
		?></td>
	</tr>
<?php
}
?>
</table>
	</div></div>
</div>


	<!-- AFFILIATE -->
<?php
if(!empty($this->affiliate_active)) {
	$this->setLayout('affiliate');
	echo $this->loadTemplate();
}
?>
<?php
?>
<!-- FEATURES - SEF -->
<div id="features_sef" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('SEF_URL_OPTIONS'); ?></div>
<table class="hk_config_table table" style="width:100%; margin-bottom:0;">

	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('simplified_breadcrumbs');?>><?php echo JText::_('SIMPLIFIED_BREADCRUMBS'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', "config[simplified_breadcrumbs]",'',$this->config->get('simplified_breadcrumbs',1));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('sef_remove_id');?>><?php echo JText::_('REMOVE_ID_IN_URLS'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', "config[sef_remove_id]",'',$this->config->get('sef_remove_id',0));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('force_canonical_urls');?>><?php echo JText::_('FORCE_CANONICAL_URLS_ON_LISTINGS'); ?></td>
		<td><?php
	$arr = array(
		JHTML::_('select.option', 0, JText::_('NO_GENERATE_URL') ),
		JHTML::_('select.option', 1, JText::_('USE_CANONICAL_URL_IF_SPECIFIED') ),
		JHTML::_('select.option', 2, JText::_('USE_CANONICAL_URL_AND_GENERATE_IT_IF_MISSING') ),
	);
	echo JHTML::_('hikaselect.genericlist', $arr, "config[force_canonical_urls]", 'class="custom-select" size="1"', 'value', 'text', $this->config->get('force_canonical_urls',1));
		?></td>
	</tr>
<?php
	if(!$this->config->get('activate_sef', 1)) { ?>
	<tr>
		<td class="hk_tbl_key"><?php echo JText::_('ACTIVATE_SMALLER_URL'); ?></td>
		<td><?php echo JHTML::_('hikaselect.booleanlist', 'config[activate_sef]', 'onclick="setSefVisible(this.value);"', $this->config->get('activate_sef', 1)); ?></td>
	</tr>
<?php
	}
?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('category_sef_name');?>><?php echo JText::_('CATEGORY_LISTING_SEF_NAME'); ?></td>
		<td>
			<input class="inputbox" type="text" id="cat_sef" name="config[category_sef_name]" value="<?php echo $this->config->get('category_sef_name', 'category'); ?>" onchange="checkSEF(this,document.getElementById('prod_sef').value,'<?php echo $this->config->get('category_sef_name', 'category'); ?>');">
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('product_sef_name');?>><?php echo JText::_('PRODUCT_SHOW_SEF_NAME'); ?></td>
		<td>
			<input class="inputbox" type="text" id="prod_sef" name="config[product_sef_name]" value="<?php echo $this->config->get('product_sef_name', 'product'); ?>" onchange="checkSEF(this,document.getElementById('cat_sef').value,'<?php echo $this->config->get('product_sef_name', 'category'); ?>');">
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('checkout_sef_name');?>><?php echo JText::_('CHECKOUT_SEF_NAME'); ?></td>
		<td>
			<input class="inputbox" type="text" id="prod_sef" name="config[checkout_sef_name]" value="<?php echo $this->config->get('checkout_sef_name', 'checkout'); ?>">
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('alias_auto_fill');?>><?php echo JText::_('ALIAS_AUTO_FILL'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', "config[alias_auto_fill]",'',$this->config->get('alias_auto_fill',1));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('auto_keywords_and_metadescription_filling');?>><?php echo JText::_('AUTO_KEYWORDS_AND_METADESCRIPTION_FILLING'); ?></td>
		<td><?php echo JHTML::_('hikaselect.booleanlist', "config[auto_keywords_and_metadescription_filling]", 'onchange="keywords_num_visible(this.value);"', $this->config->get('auto_keywords_and_metadescription_filling', 0)); ?></td>
	</tr>
</table>

<?php
$keywordOptions = ((int)$this->config->get('auto_keywords_and_metadescription_filling', 0) == 0) ? 'display:none' : '';
?>
<script type="text/javascript">
function keywords_num_visible(value) {
	var el = document.getElementById('auto_keywords_and_metadescription_filling_block');
	el.style.display = (value == 0) ? 'none' : '';
}
</script>
<table class="hk_config_table table" id="auto_keywords_and_metadescription_filling_block" style="width:100%;<?php echo $keywordOptions; ?>">
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('keywords_number');?>><?php echo JText::_('KEYWORDS_NUMBER'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[keywords_number]" value="<?php echo $this->config->get('keywords_number','0'); ?>"/>
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('max_size_of_metadescription');?>><?php echo JText::_('MAX_SIZE_OF_METADESCRIPTION'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[max_size_of_metadescription]" value="<?php echo $this->config->get('max_size_of_metadescription', '254'); ?>">
			<span>(Max 254 characters)</span>
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('keywords_exclusion_list');?>><?php echo JText::_('KEYWORDS_EXCLUSION_LIST'); ?></td>
		<td>
			<input class="inputbox" type="text" name="config[keywords_exclusion_list]" value="<?php echo $this->config->get('keywords_exclusion_list', 'what,when,why,with,this,then,the,these,those,thus,they'); ?>">
		</td>
	</tr>

</table>
	</div></div>
</div>

<!-- FEATURES - FILTER -->
<div id="features_filter" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('FILTER'); ?></div>
<table class="hk_config_table table" style="width:100%">

<?php if(hikashop_level(2)) { ?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('filter_column_number');?>><?php echo JText::_('NUMBER_OF_COLUMNS');?></td>
		<td>
			<input name="config[filter_column_number]" type="text" value="<?php echo $this->config->get('filter_column_number',2)?>" />
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('filter_limit');?>><?php echo JText::_('LIMIT');?></td>
		<td>
			<input name="config[filter_limit]" type="text" value="<?php echo $this->config->get('filter_limit')?>" />
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('filter_height');?>><?php echo JText::_('HEIGHT');?></td>
		<td>
			<input name="config[filter_height]" type="text" value="<?php echo $this->config->get('filter_height',100)?>" />
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('show_reset_button');?>><?php echo JText::_('SHOW_RESET_BUTTON'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[show_reset_button]' , '',@$this->config->get('show_reset_button',0));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('show_filter_button');?>><?php echo JText::_('SHOW_FILTER_BUTTON'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[show_filter_button]' , '',@$this->config->get('show_filter_button',1));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('display_fieldset');?>><?php echo JText::_('DISPLAY_FIELDSET'); ?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', 'config[display_fieldset]' , '',@$this->config->get('display_fieldset',1));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('filter_button_position');?>><?php echo JText::_('FILTER_BUTTON_POSITION'); ?></td>
		<td><?php
			echo $this->filterButtonType->display('config[filter_button_position]',$this->config->get('filter_button_position'));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('show_filters');?>><?php echo JText::_('DISPLAY_FILTERS_ON_PRODUCT_LISTING');?></td>
		<td><?php echo JHTML::_('hikaselect.booleanlist', 'config[show_filters]', '', $this->config->get('show_filters', 1)); ?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('collapsable_filter');?>><?php echo JText::_('COLLAPSABLE_FILTERS'); ?></td>
		<td><?php
	$arr = array(
		JHTML::_('select.option', '', JText::_('HIKASHOP_NO') ),
		JHTML::_('select.option', '1', JText::_('MOBILE_DEVICES') ),
		JHTML::_('select.option', 'always', JText::_('HIKA_ALWAYS') ),
	);
	echo JHTML::_('hikaselect.genericlist', $arr, "config[filter_collapsable]", 'class="custom-select" size="1"', 'value', 'text', $this->config->get('filter_collapsable', 1));
		?></td>
	</tr>
<?php } else { ?>
	<tr>
		<td class="hk_tbl_key"><?php echo JText::_('FILTER'); ?></td>
		<td><?php
			echo hikashop_getUpgradeLink('business');
		?></td>
	</tr>
<?php } ?>

</table>
	</div></div>
</div>

<!-- FEATURES - ATOM/RSS -->
<div id="features_atom" class="hikashop_backend_tile_edition">
	<div class="hikashop_tile_block"><div>
		<div class="hikashop_tile_title"><?php echo JText::_('ALL_FEED'); ?></div>
<table class="hk_config_table table" style="width:100%">

	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('hikarss_format');?>><?php echo JText::_('HIKA_TYPE'); ?></td>
		<td><?php
	$hikarss_format = array(
		JHTML::_('select.option', 'none', JText::_('NO_FEED')),
		JHTML::_('select.option', 'rss', JText::_('RSS_ONLY')),
		JHTML::_('select.option', 'atom', JText::_('ATOM_ONLY')),
		JHTML::_('select.option', 'both', JText::_('ALL_FEED'))
	);
	echo JHTML::_('hikaselect.genericlist', $hikarss_format, "config[hikarss_format]" , 'class="custom-select" size="1"', 'value', 'text', $this->config->get('hikarss_format', 'both'));
		?></td>
	</tr>
<?php if($this->config->get('hikarss_format', 'both') != 'none'){ ?>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('hikarss_name');?>><?php echo JText::_('HIKA_NAME'); ?></td>
		<td>
			<input type="text" size="40" name="config[hikarss_name]" value="<?php echo $this->config->get('hikarss_name',''); ?>"/>
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('hikarss_description');?>><?php echo JText::_('HIKA_DESCRIPTION'); ?></td>
		<td>
			<textarea cols="32" rows="5" name="config[hikarss_description]" ><?php echo $this->config->get('hikarss_description',''); ?></textarea>
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('hikarss_element');?>><?php echo JText::_('NUMBER_OF_ITEMS'); ?></td>
		<td>
			<input type="text" size="40" name="config[hikarss_element]" value="<?php echo $this->config->get('hikarss_element','10'); ?>"/>
		</td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('hikarss_order');?>><?php echo JText::_('ORDERING_FIELD'); ?></td>
		<td><?php
	$hikarss_order = array(
		JHTML::_('select.option', 'product_sale_start', JText::_('PRODUCT_SALE_START')),
		JHTML::_('select.option', 'product_id', 'ID'),
		JHTML::_('select.option', 'product_created', JText::_('ORDER_CREATED')),
		JHTML::_('select.option', 'product_modified', JText::_('HIKA_LAST_MODIFIED'))
	);
	echo JHTML::_('hikaselect.genericlist', $hikarss_order, "config[hikarss_order]" , 'class="custom-select" size="1"', 'value', 'text', $this->config->get('hikarss_order','product_id'));
		?></td>
	</tr>
	<tr>
		<td class="hk_tbl_key"<?php echo $this->docTip('hikarss_child');?>><?php echo JText::_('SHOW_SUB_CATEGORIES');?></td>
		<td><?php
			echo JHTML::_('hikaselect.booleanlist', "config[hikarss_child]", 'size="1"', $this->config->get('hikarss_child', 1));
		?></td>
	</tr>
<?php } ?>
</table>
	</div></div>
</div>

<script language="JavaScript" type="text/javascript">
function checkSEF(obj,other,default_val){
	if(obj.value == other){
		obj.value = default_val;
		alert('you can\'t have the same SEF name for product and category');
	}
}
</script>

</div>
