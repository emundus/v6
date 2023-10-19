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
if(!isset($this->element['layout_type']))
	$this->element['layout_type'] = 'inherit';
if (HIKASHOP_J40) {
?>
<style>
	legend,
	fieldset#fieldset-basic small.form-text.text-muted,
	section#attrib-products small.form-text.text-muted {
		display: none;
	}
	fieldset#fieldset-hk_options,
	section#attrib-hk_options {
		border: none;
		padding: 0px;
		margin: 0px;
	}
	section#attrib-basic,
	section#attrib-products {
		padding: 0px;
	}
	div#hikashop_main_content {
		border: 1px solid #b2bfcd;
		border-width: 1px 0px 0px 0px;
	}
	small.form-text.text-muted {
		display: none;
	}
	div#hikashop_main_content select.custom-select {
		width: 260px;
	}
	@media screen and (max-width: 630px) {
		div.hikaradios .btn-group {
			max-height: 45px;
		}
		dl.hika_options.hikashop_mini_cart dd.hikashop_option_value label {
			font-size: 0.7em;
		}
	}
	@media only screen and (max-width: 960px) {
		dl.hika_options > dd {
			margin-left: 160px;
		}
		dl.hika_options > dt {
			width: 150px;
			text-align: left;
			margin-left: 5px;
		}
	}
</style>
<?php
}

?>
<div id="hikashop_main_content" class="hikashop_main_content hk-container-fluid item-cartmodule-interface hika_j<?php echo (int)HIKASHOP_JVERSION; ?>">
	<!-- module edition -->
	<div id="hikashop_module_backend_page_edition" class="hk-row-fluid">
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
if(HIKASHOP_J40) {
	$js .= "
window.hikashop.ready(function() {
	var mainDiv = document.getElementById('hikashop_main_content');
	if(mainDiv) {
		mainDiv.parentNode.classList.remove('column-count-md-2');
		mainDiv.parentNode.classList.remove('column-count-lg-3');
	}
});
";
}
$doc = JFactory::getDocument();
$doc->addScriptDeclaration($js);
