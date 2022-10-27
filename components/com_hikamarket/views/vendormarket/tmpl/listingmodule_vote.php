<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>	<div id="hikamarket_vendor_vote" class="hikamarket_vendor_vote">
<?php
	$voteParams = new HikaParameter();
	$voteParams->set('vote_type', 'vendor');
	$voteParams->set('vote_ref_id', $this->row->vendor_id);
	$js = '';
	echo hikamarket::getLayout('shop.vote', 'mini', $voteParams, $js);
?>
	</div>
