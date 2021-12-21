<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="hikamarket_vendor_module" id="hikamarket_vendor_locationsearch">
	<form action="<?php echo $url; ?>"<?php echo $script; ?> method="POST">
		<div>
			<input name="location_search" type="text" value="<?php echo $location_search; ?>" id="hikamarket_vendor_locationsearch_input" placeholder="<?php echo @$params->get('placeholder'); ?>" />
<?php if(!empty($search_button)) { ?>
			<input type="submit" value="<?php echo JText::_($search_button); ?>" />
<?php } ?>
		</div>
		<div id="hikamarket_vendor_locationsearch_error" style="display:none;">
			<span><?php echo JText::_('PLEASE_INDICATE_YOUR_LOCATION'); ?></span>
		</div>
	</form>
<?php if($block_empty_search) { ?>
<script type="text/javascript">
if(!window.localPage) window.localPage = {};
window.localPage.locationsearchSubmit = function(form) {
	var d = document, el = d.getElementById('hikamarket_vendor_locationsearch_input');
	if(!el)
		return true;
	var v = el.value.replace(/^\s*|\s*$/g, '');
	if(v != '')
		return true;

	el.className = 'hikamarket_location_search_error';
	var err_el = d.getElementById('hikamarket_vendor_locationsearch_error');
	if(err_el)
		err_el.style.display = '';
	return false;
};
</script>
<?php } ?>
</div>
