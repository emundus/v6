<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$value = $this->params->get('value', $this->pageInfo->search);
$map = $this->params->get('map', 'search');
$id = $this->params->get('id', $map);
$filter_btn = $this->params->get('filter_btn', '');
?>
<div class="hikamarket_search_block">
	<div class="hk-input-group">
		<input type="text" name="<?php echo $map ;?>" id="<?php echo $id; ?>" value="<?php echo $this->escape($value); ?>" class="hk-form-control" placeholder="<?php echo JText::_('HIKA_SEARCH'); ?>"/>
		<div class="hk-input-group-append">
			<button class="hikabtn" onclick="hikamarket.searchSubmit(this);"><i class="fas fa-search"></i></button>
			<button class="hikabtn" onclick="hikamarket.searchReset(this);"><i class="fas fa-times"></i></button>
		</div>
	</div>
<?php if(!empty($filter_btn)) { ?>
	<button class="hikabtn hikam_toggle" onclick="return hikamarket.searchFilters(this, '<?php echo $this->escape($filter_btn); ?>');"><i class="fas fa-filter"></i> <?php echo JText::_('HIKAM_TOGGLE_FILTERS'); ?></button>
<?php } ?>
</div>
