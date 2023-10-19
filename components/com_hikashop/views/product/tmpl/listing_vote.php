<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><span class="hikashop_product_vote"><?php
	$js = '';
	$this->params->set('vote_type', 'product');
	$this->params->set('product_id', (int)$this->row->product_id);
	$this->params->set('average_score', $this->row->product_average_score);
	$this->params->set('total_vote', (int)$this->row->product_total_vote);
	$this->params->set('listing_product', true);
	echo hikashop_getLayout('vote', 'mini', $this->params, $js);
?></span>
