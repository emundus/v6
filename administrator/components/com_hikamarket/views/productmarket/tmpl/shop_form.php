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
if(!hikamarket::level(1) || $this->product_type == 'template')
	return;

$type = 'product';
if($this->product_type == 'variant')
	$type = 'variant';
echo $this->nameboxType->display(
	'data['.$type.'][product_vendor_id]',
	(int)$this->product_vendor_id,
	hikamarketNameboxType::NAMEBOX_SINGLE,
	'vendor',
	array(
		'delete' => true,
		'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>'
	)
);
