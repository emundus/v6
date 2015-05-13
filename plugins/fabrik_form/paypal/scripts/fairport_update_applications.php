<?php
class fabrikPayPalIPN {


	/*
	 * Called at end of submission handling, just before the form plugin sets up the redirect to PayPal
	* Allows you to check / add / remove / modify any of the query string options being sent to PayPal
	* Also gives you a last chance to bail out and not do the redirect to PayPal, by returning false
	* (and probably using one of the J! API's to put up a notification of why!)
	*/
	function checkOpts(&$opts, $formModel) {
		return true;
	}

	function payment_status_Completed($listModel, $request, &$set_list, &$err_msg) {
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$input = $app->input;
		$custom = $input->get('custom', '', 'string');
		$bits = explode(":", $custom);
		$formid = $bits[0];
		$rowid = $bits[1];
		$type = $bits[2];
		FabrikWorker::log('fairport IPN complete', $custom);
		if ($type == 'vendor application')
		{
			$query->select('vendor_name')->from('fab_invoice')->where('id = ' . $rowid);
			$db->setQuery($query);
			if (!$db->execute())
			{
				FabrikWorker::log('fairport IPN complete', $db->getQuery());
			}
			$vendorName = $db->loadResult();
			FabrikWorker::log('fairport IPN complete', 'vendor name = ' . $vendorName);

			$query->clear();
			$query->update('festos_vendors')->set('statusPaid = 1')->where('vendorName = ' . $db->quote($vendorName));
			$db->setQuery($query);
			FabrikWorker::log('fairport IPN complete', $db->getQuery());
			if (!$db->execute())
			{
				FabrikWorker::log('fairport IPN complete: db error', $db->getErrorMsg());
			}
		}
		else if ($type == 'sponsor application')
		{
			$query->select('sponsor_id')->from('fab_invoice')->where('id = ' . $rowid);
			$db->setQuery($query);
			if (!$db->execute())
			{
				FabrikWorker::log('fairport IPN complete couldnt get sponsor id', $db->getQuery());
			}
			$sponsorId = $db->loadResult();
			FabrikWorker::log('fairport IPN complete', 'sponsor id = ' . $sponsorId);

			$query->clear();
			$query->update('festos_sponsors')->set('sponsorPaid = 1')->where('id = ' . (int) $sponsorId);
			$db->setQuery($query);
			FabrikWorker::log('fairport IPN complete', $db->getQuery());
			if (!$db->execute())
			{
				FabrikWorker::log('fairport IPN complete: db error', $db->getErrorMsg());
			}
		}
		return 'ok';
	}

	function payment_status_Pending($listModel, $request, &$set_list, &$err_msg) {
		$custom = $input->get('custom', '', 'string');
		FabrikWorker::log('fairport IPN pending', $custom);
	}

	function payment_status_Reversed($listModel, $request, &$set_list, &$err_msg) {
		return 'ok';
	}

	function payment_status_Cancelled_Reversal($listModel, $request, &$set_list, &$err_msg) {
		return 'ok';
	}

	function payment_status_Refunded($listModel, $request, &$set_list, &$err_msg) {
		return 'ok';
	}

	function txn_type_web_accept($listModel, $request, &$set_list, &$err_msg) {
		return 'ok';
	}

	function txn_type_subscr_signup($listModel, $request, &$set_list, &$err_msg) {
		return 'ok';
	}

	function txn_type_subscr_cancel($listModel, $request, &$set_list, &$err_msg) {
		return 'ok';
	}

	function txn_type_subscr_modify($listModel, $request, &$set_list, &$err_msg) {
		return 'ok';
	}

	function txn_type_subscr_payment($listModel, $request, &$set_list, &$err_msg) {
		return 'ok';
	}

	function txn_type_subscr_failed($listModel, $request, &$set_list, &$err_msg) {
		return 'ok';
	}

	function txn_type_subscr_eot($listModel, $request, &$set_list, &$err_msg) {
		return 'ok';
	}
}