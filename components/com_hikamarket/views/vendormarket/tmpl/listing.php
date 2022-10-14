<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
ob_start();

$title = 'show_page_heading';
$titleType = 'h1';

if($this->module) {
	$title = 'showtitle';
	$titleType = 'h2';
}

if($this->params->get($title) && hikaInput::get()->getInt('hikamarket_front_end_main', 0)) {
	if($this->module) {
		$heading = $this->params->get('title');
	} else {
		$heading = $this->params->get('page_title');
		if($this->params->get('page_heading')) {
			$heading = $this->params->get('page_heading');
		}
	}
	echo '<' . $titleType . '>' . $heading . '</' . $titleType . '>';
}

$layout_type = $this->params->get('layout_type');
if(empty($layout_type))
	$layout_type = 'div';

$this->setLayout('listingcontainer_' . $layout_type);
$html = $this->loadTemplate();
if(!empty($html))
	echo '<div class="hikamarket_vendor_listing">'.$html.'</div>';

if(!$this->module) {
	if(!empty($this->modules)) {
		$html = '';
		jimport('joomla.application.module.helper');
		foreach($this->modules as $module) {
			$html .= JModuleHelper::renderModule($module);
		}
		if(!empty($html))
			echo '<div class="hikamarket_submodules" style="clear:both">'.$html.'</div>';
	}
}
$html = ob_get_clean();
if(!empty($html)) {
?>
	<div id="<?php echo $this->params->get('main_div_name');?>" class="hikamarket_vendors_listing_main"><?php

	$pagination = $this->config->get('pagination','bottom');
	if(in_array($pagination, array('top', 'both')) && $this->params->get('show_limit') && $this->pageInfo->elements->total) {
		$this->pagination->form = '_top';
?>
		<form action="<?php echo hikamarket::currentURL(); ?>" method="post" name="adminForm_<?php echo $this->params->get('main_div_name').$this->category_selected;?>_top">
			<div class="hikamarket_listing_pagination hikamarket_listing_pagination_top hikamarket_vendors_pagination hikamarket_vendors_pagination_top">
				<?php echo $this->pagination->getListFooter($this->params->get('limit')); ?>
				<span class="hikamarket_results_counter"><?php echo $this->pagination->getResultsCounter(); ?></span>
			</div>
			<input type="hidden" name="filter_order_<?php echo $this->params->get('main_div_name'); ?>" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
			<input type="hidden" name="filter_order_Dir_<?php echo $this->params->get('main_div_name'); ?>" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
			<?php echo JHTML::_('form.token'); ?>
		</form>
<?php
	}

	echo $html;

	if(in_array($pagination, array('bottom', 'both')) && $this->params->get('show_limit') && $this->pageInfo->elements->total) {
		$this->pagination->form = '_bottom';
?>
		<form action="<?php echo hikamarket::currentURL(); ?>" method="post" name="adminForm_<?php echo $this->params->get('main_div_name'); ?>_bottom">
			<div class="hikamarket_listing_pagination hikamarket_listing_pagination_botton hikamarket_vendors_pagination hikamarket_vendors_pagination_bottom">
				<?php echo $this->pagination->getListFooter($this->params->get('limit')); ?>
				<span class="hikamarket_results_counter"><?php echo $this->pagination->getResultsCounter(); ?></span>
			</div>
			<input type="hidden" name="filter_order_<?php echo $this->params->get('main_div_name'); ?>" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
			<input type="hidden" name="filter_order_Dir_<?php echo $this->params->get('main_div_name'); ?>" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
			<?php echo JHTML::_('form.token'); ?>
		</form>
<?php
	}

	?></div>
<?php }
