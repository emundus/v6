<?php

require_once JPATH_ROOT . '/administrator/components/com_virtuemart/helpers/config.php';
require_once VMPATH_SITE . '/helpers/cart.php';
require_once VMPATH_ADMIN . '/helpers/calculationh.php';
require_once JPATH_ROOT . '/components/com_virtuemart/controllers/cart.php';

$vmConfig = VmConfig::loadConfig();

$cart = new VirtueMartControllerCart;
$virtuemart_product_id = $formModel->formData['fab_vm_test___product_raw'];
//$virtuemart_product_id = array($virtuemart_product_id) ? $virtuemart_product_id[0] : $virtuemart_product_id;
$myQuantity = array(
	'1'
);
$myCustomFields = array(
	'1' => array(
		'3' => array(
			'1' => "foo bar"
		)
	),
	'2' => array(
		'3' => array(
			'1' => "foo bar"
		)
	)
);

$app = JFactory::getApplication();
$app->input->set('virtuemart_product_id', $virtuemart_product_id);
$app->input->set('quantity', $myQuantity);
$app->input->set('customProductData', $myCustomFields);

//$cart->add();

$cart = VirtueMartCart::getCart();
if ($cart)
{
	$virtuemart_product_ids = vRequest::getInt('virtuemart_product_id');
	$error                  = false;
	$cart->add($virtuemart_product_ids, $error);
	if (!$error)
	{
		$msg  = vmText::_('COM_VIRTUEMART_PRODUCT_ADDED_SUCCESSFULLY');
		$type = '';
	}
	else
	{
		$msg  = vmText::_('COM_VIRTUEMART_PRODUCT_NOT_ADDED_SUCCESSFULLY');
		$type = 'error';
	}
}

