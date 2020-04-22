<?php

// No direct access
defined('_JEXEC') or die('Restricted access');

/** J Table classes **/
class FabrikTableInvoice extends JTable
{
	public function __construct(&$db)
	{
		parent::__construct('fab_invoice', 'id', $db);
	}
}

class FabrikTableInvoiceItem extends JTable
{
	public function __construct(&$db)
	{
		parent::__construct('fab_invoice_item', 'id', $db);
	}
}

// Standard Joomla vars
$user = JFactory::getUser();
$app = JFactory::getApplication();
$input = $app->input;
$db = JFactory::getDbo();
$query = $db->getQuery(true);

// Get applicaton form data
$type = $input->get('festos_vendors___appType_vend', array(), 'array');
$type = JArrayHelper::getValue($type, 0, '');

$numBooths = $input->get('festos_vendors___numBooths', array(), 'array');
$numBooths = (int) JArrayHelper::getValue($numBooths, 0, 0);
$foodBooths = $input->get('festos_vendors___numBoothsFood', array(), 'array');
$foodBooths = (int) JArrayHelper::getValue($foodBooths, 0, 0);
$cornerBooth = $input->get('festos_vendors___cornerBoothRequested');
$vendorName = $input->get('festos_vendors___vendorName', '', 'string');
$newRenew = $input->get('festos_vendors___AppNewRenew');
$newRenew = (int) JArrayHelper::getValue($newRenew, 0);
$mainStreet = $input->get('festos_vendors___vendFPMAMainStreet', array(), 'array');
$mainStreet = JArrayHelper::getValue($mainStreet, 0, '');

// Get the payment costs for the application type
$query->select('*')->from('festos_applicanttypes')->where('id = ' . $type);
$db->setQuery($query);
$costs = $db->loadObject();
//echo "<pre>";print_r($costs);print_r($_POST);;

// Work out the invoice items and the total cost
$invoiceItems = array();
$cost = 0;
if ($numBooths > 0)
{

	// Late applicaiton fee
	$now = JFactory::getDate();
	$lateDate = JFactory::getDate('2013-02-16 00:00:00');
	if ($now->toUnix() > $lateDate->toUnix())
	{
		$cost += $costs->lateFee;
		$invoiceItems[] = array('cost' => $costs->lateFee, 'quantity' => 1, 'line_total' => $costs->lateFee, 'description' => 'Late Fee');
	}

	if ($costs->appFee != '0.00')
	{
		// "New - first time applicant for Artisans" have a 25 discount (new radio option has a value of 0)
		if (!($newRenew == 0 && $type == 1))
		{
			$cost += $costs->appFee;
			$invoiceItems[] = array('cost' => $costs->appFee, 'quantity' => 1, 'line_total' => $costs->appFee, 'description' => 'Application fee');
		}
	}
	$invoiceItems[] = array('cost' => $costs->boothFee, 'quantity' => 1, 'line_total' => $costs->boothFee, 'description' => 'Booth');
	$cost += $costs->boothFee;
	if ($numBooths > 1)
	{
		$additonalBooths = $numBooths - 1;
		$additonalCost = $costs->extraBoothFee * $additonalBooths;
		$cost += $additonalCost;
		$invoiceItems[] = array('cost' => $costs->extraBoothFee, 'quantity' => $additonalBooths, 'line_total' => $additonalCost, 'description' => 'Additonal Booths');
	}

	if ($cornerBooth)
	{
		$cost += $costs->cornerBoothFee;
		$invoiceItems[] = array('cost' => $costs->cornerBoothFee, 'quantity' => 1, 'line_total' => $costs->cornerBoothFee, 'description' => 'Corner Booth');
	}

	// Aritsan FPMA Members Only members requesting main street
	if ($type == 2 && $mainStreet)
	{
		$mainStreetCost = 500;
		$cost += $mainStreetCost;
		$invoiceItems[] = array('cost' => $mainStreetCost, 'quantity' => 1, 'line_total' => $mainStreetCost, 'description' => 'Main street');
	}

	// 2nd booth type for food vendors
	if ($foodBooths > 0 && $costs->booth2Fee != '0.00')
	{
		$cost += $costs->booth2Fee;
		$invoiceItems[] = array('cost' => $costs->booth2Fee, 'quantity' => 1, 'line_total' => $costs->booth2Fee, 'description' => '2nd Booth');
	}

	// Additional 2nd booth types for food vendors
	if ($foodBooths > 1 && $costs->extraBooth2Fee != '0.00')
	{
		$additonalBooths = $foodBooths - 1;
		$additonalCost = $costs->extraBooth2Fee * $additonalBooths;
		$cost += $additonalCost;
		$invoiceItems[] = array('cost' => $costs->extraBooth2Fee, 'quantity' => $additonalBooths, 'line_total' => $additonalCost, 'description' => 'Additonal 2nd Booth');
	}
}
/* // Switch for testing on paypaltest account
if ($user->get('id') == 217)
{
	$input->set('paypal_testmode', 1);
} */

// Build the default invoice data
$data = array();
$data['user_id'] = $user->get('id');
$data['vendor_name'] = $vendorName;
$data['paypal_payment_status'] = 'New';
$data['type'] = 'vendor application';
$data['total'] = $cost;


// Does the vendor have an invoice
$invoice = JTable::getInstance('Invoice', 'FabrikTable');
if (!$invoice->load(array('vendor_name' => $vendorName)))
{
	// No invoice - create it
	$invoice = JTable::getInstance('Invoice', 'FabrikTable');
	$invoice->save($data);
}
else
{
	// Update invoice
	$invoice->save($data);

	// Delete all previous invoice items
	$query->clear();
	$query->delete('fab_invoice_item')->where('invoice_id = ' . (int) $invoice->id);
	$db->setQuery($query);
	$db->execute();
}

// Create invoice items
foreach ($invoiceItems as $data)
{
	$data['id'] = 0;
	$data['invoice_id'] = $invoice->id;
	$invoiceItem = JTable::getInstance('InvoiceItem', 'FabrikTable');
	$invoiceItem->save($data);
}

$input->set('invoiceid', $invoice->id);

// Add the user into the "Canal Days Vendor" (id 16) group
$groups = $user->getAuthorisedGroups();
if (!in_array(16, $groups))
{
	$groups[] = 16;
	$user->groups = $groups;
	$user->save();
}