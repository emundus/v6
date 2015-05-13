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

$sponsorShips = $input->get('festos_sponsors___sponsorLevel', array(), 'array');



// Get the payment costs for the application type
$query->select('params')->from('#__fabrik_elements')->where('id = 310');
$db->setQuery($query);
$costs = json_decode($db->loadResult());
$costs = $costs->sub_options;
// echo "<pre>";print_r($costs);print_r($_POST);

// Work out the invoice items and the total cost
$invoiceItems = array();
$cost = 0;

foreach ($sponsorShips as $sponsor)
{
	$index = array_search($sponsor, $costs->sub_values);
	if ($index !== false)
	{
		$label =  $costs->sub_labels[$index];
		$cost += $sponsor;
		$invoiceItems[] = array('cost' => $sponsor, 'quantity' => 1, 'line_total' => $sponsor, 'description' => $label);
	}
}
// Build the default invoice data
$data = array();
$data['user_id'] = $user->get('id');
$data['sponsor_id'] = $input->get('festos_sponsors___id');
$data['paypal_payment_status'] = 'New';
$data['type'] = 'sponsor application';
$data['total'] = $cost;


// Does the sponsor have an invoice
$invoice = JTable::getInstance('Invoice', 'FabrikTable');
if (!$invoice->load(array('sponsor_id' => $vendorName)))
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

