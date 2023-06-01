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


if (!isset($this->type))
	$this->type = '';
if(!empty($this->choose))
	$this->type = 'choose';
if (!isset($this->css_button))
	$this->css_button = '';
if (!isset($this->link_content))
	$this->link_content = '';
if (!isset($this->haveLink))
	$this->haveLink = 0;

$popup_mode = $this->params->get('product_popup_mode', 0);
$mainDivName = $this->params->get('main_div_name', '');

$link_content = $this->link_content;
$url_link = 'product&task=show&cid=' . (int)$this->row->product_id . '&name=' . $this->row->alias . $this->itemid . $this->category_pathway;
$link = hikashop_contentLink($url_link, $this->row);

if(is_numeric($popup_mode)) 
	$popup_mode = (int)$popup_mode;

$display_popup = $popup_mode;
if (($popup_mode === 3 )|| ($popup_mode === 'inherit') || ($popup_mode === '')) {
	$config = hikashop_config();
	$defaultParams = $config->get('default_params');
	$popup_mode = (int)@$defaultParams['product_popup_mode'];
}

switch($this->type) {
	case 'choose':
		if ($popup_mode == 2)
			$display_popup = 1;
		break;
	case 'detail':
		if ($popup_mode == 2)
			$display_popup = 0;
		break;
	case '':
	default:
		if ($popup_mode == 2 || !$this->haveLink)
			$display_popup = 0;
		break;
}

if ($display_popup) {
	 $popupHelper = hikashop_get('helper.popup');
	echo ' '.$popupHelper->display(
		$link_content,
		'HIKASHOP_PRODUCT_POPUP',
		$link.'?tmpl=component',
		$mainDivName.'_popup_product_'.$this->row->product_id,
		1075, 580, 'title="'.JText::_('EDIT_THE_OPTIONS_OF_THE_PRODUCT').'" class="'.$this->css_button.'"', '', 'link'
	);
}
else {
	if(($this->haveLink) || (!empty($this->type))) { ?>
		<a href="<?php echo $link;?>" class="<?php echo $this->css_button; ?>">
<?php 
	} ?>
		<?php echo $link_content; ?>
<?php 
	if(($this->haveLink) || (!empty($this->type))) { ?>
		</a>
<?php 
	}
}

$this->css_button = '';
$this->type = '';
