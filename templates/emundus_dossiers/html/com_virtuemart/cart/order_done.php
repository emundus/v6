<?php
defined ('_JEXEC') or die('Restricted access');
JHtml::_ ('behavior.formvalidation');

$app = JFactory::getApplication();
$template_path = JPATH_BASE . "/templates/" . $app->getTemplate().'/params.ini';
$ini_array = parse_ini_file($template_path);
$shoppingbtnclass = $ini_array['shoppingbtnclass'];

echo '<div class="vm-wrap vm-order-done payment_shipment_content ttr_prochec_table_background">';

if (vRequest::getBool('display_title',true)) {
	echo '<h3 class="ttr_prodsigninheading">'.vmText::_('COM_VIRTUEMART_CART_ORDERDONE_THANK_YOU').'</h3>';
}

$this->html = vRequest::get('html', vmText::_('COM_VIRTUEMART_ORDER_PROCESSED') );
echo $this->html;

if (vRequest::getBool('display_loginform',true)) {
	$cuser = JFactory::getUser();
	if (!$cuser->guest) echo shopFunctionsF::getLoginForm();
}
echo '</div>';
?>
<script>
jQuery('a.vm-button-correct').addClass('<?php echo $shoppingbtnclass ; ?>').removeClass('vm-button-correct');
</script>
