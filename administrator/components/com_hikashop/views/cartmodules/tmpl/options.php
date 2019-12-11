<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.2.2
 * @author	hikashop.com
 * @copyright	(C) 2010-2019 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if(!isset($this->element['layout_type']))
	$this->element['layout_type'] = 'inherit';
?>
<div id="hikashop_main_content" class="hikashop_main_content hk-container-fluid item-cartmodule-interface">
	<!-- module edition -->
	<div id="hikashop_module_backend_page_edition">
<?php
echo $this->loadTemplate('main');
echo $this->loadTemplate('price');
echo $this->loadTemplate('display_restriction');
?>
	</div>
</div>
<?php
$js = "
window.hikashop.ready(function() {
	window.hikashop.dlTitle('hikashop_main_content');
	hkjQuery('#options #hikashop_main_content').prev('.control-group').hide();
});
";
$doc = JFactory::getDocument();
$doc->addScriptDeclaration($js);
