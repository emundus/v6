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
if(hikaInput::get()->getWord('tmpl','')=='component') {
	header( 'Cache-Control: no-store, no-cache, must-revalidate' );
	header( 'Cache-Control: post-check=0, pre-check=0', false );
	header( 'Pragma: no-cache' );
}
?>
<div class="hikashop_entry_info">
<?php
	$this->setLayout('form_fields');
	echo $this->loadTemplate();
?>
</div>
<?php
if(hikaInput::get()->getWord('tmpl', '') == 'component') {
	exit;
}
