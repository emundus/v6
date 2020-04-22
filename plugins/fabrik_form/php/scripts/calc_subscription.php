<?php defined('_JEXEC') or die; ?>

<?php
// Initialize
$user =     JFactory::getUser();
$app =      JFactory::getApplication();
$document = JFactory::getDocument();
$db =       FabrikWorker::getDbo();
$jinput =   $app->input;

// Get member's data
$query = $db->getQuery( true );
$query->select( 'id, country_invoice_id, vat')->from( $query->quoteName('members'))->where( $query->quoteName('user_id') . ' = ' . (int)$user->get('id'));
$db->setQuery( $query );
try {
    $member = $db->loadObject();
} catch (Exception $e) {

}

if( empty( $member->country_invoice_id )) { // Redirect to pro registration modify if the country is not set properly
    $msg = FText::_( 'INCANDESCENT_PROBLEM_WITH_REGISTRATION');;
    $url = 'index.php?option=com_fabrik&view=form&formid=61&Itemid=161';
    $app->enqueueMessage($msg);
    $app->redirect($url);
}

// Get subscription fee
$level = $jinput->get('___item_id', array(), 'array');
$query = $db->getQuery( true );
$query->select( 'fee')->from( $query->quoteName('items'))->where( $query->quoteName('id') . ' = ' . (int)$level[0]);
$db->setQuery( $query );
try {
    $netAmount = $db->loadResult();
} catch (Exception $e) {

}

// Get extension(s) fee
// First multimedia extensions
$multimediaExt = $jinput->get('___extension_id', array(), 'array');
if( !empty( $multimediaExt )) {
    $query = $db->getQuery( true );
    $query->select( 'fee')->from( $query->quoteName('items'))->where( $query->quoteName('id') . ' = ' . (int)$multimediaExt[0]);
    $db->setQuery( $query );
    try {
	$netAmount += $db->loadResult();
    } catch (Exception $e) {

    }
}

// Then other extensions
$otherExt = $jinput->get('___other_extension_id', array(), 'array');
$otherExtIds = implode( ',', $otherExt );
if( !empty( $otherExt )) {
    $query = $db->getQuery( true );
    $query->select( 'fee')->from( $query->quoteName('items'))->where( $query->quoteName('id') . ' IN (' . $otherExtIds . ')');
    $db->setQuery( $query );
    try {
	$otherExtFees = $db->loadObjectList();
    } catch (Exception $e) {

    }
    foreach( $otherExtFees as $fee ) {
	$netAmount += $fee->fee;
    }
}

// Calculate VAT
$vat = 0;
$rate = 1;
$ue = array( 40,56,100,196,203,208,233,246,250,276,300,348,372,380,428,440,442,470,528,616,620,642,703,705,724,752,826 );

if( $member->vat != 'NA' && !empty( $member->vat )) { // User has VAT
    if( $member->country_invoice_id == 56 ) { // User lives in Belgium
	$vat = number_format( $netAmount* 0.21, 2);
	$rate = 2;
    }
}

if( $member->vat == 'NA' || empty( $member->vat )) { // User has no VAT
    if( in_array( $member->country_invoice_id, $ue )) { // User lives in the UE
	$vat = number_format( $netAmount* 0.21, 2);
	$rate = 2;
    }
}

$subscription = new stdClass;
$subscription->memberId = $member->id;
$subscription->itemId = (int)$level[0];

$extension = array_merge( array_filter(array_merge( $multimediaExt, $otherExt )));
$subscription->extension = json_encode( $extension );

$subscription->paymentMethodId = $jinput->get('___payment_method_id', array(), 'array');
$subscription->paymentMethodId = $subscription->paymentMethodId[0];
$subscription->discount = $jinput->get( '___discount', '', 'string' );

// Generate a communication for the payment via bank transfert
$com = '';
if( $subscription->paymentMethodId[0] == 1 ) {
    $seed = time() + $user->get('id');
    $control = bcmod( $seed, 97 );
    $control = ( $control == "0" ) ? "97" : $control;
    if( $control < 10 ) {
        $control = "0" . $control;
    }
    $count = 10 - strlen( $seed );
        for ( $i=0; $i < $count; $i++ )
        {
            $seed = "0" . $seed;
        }

    $com = $seed . $control;
}
empty( $com ) ? $subscription->com = '' : $subscription->com = substr( $com, 0, 3 ) . "/" . substr( $com, 3, 4 ) . "/" . substr( $com, 7, 5 );

// Compute validity
if( $jinput->getInt( '___subscription_type' ) == 1 ) { // New subscription
    $validity = new DateTime( null, new DateTimeZone('Europe/Berlin'));
} elseif( $jinput->getInt( '___subscription_type' ) == 0 ) { // Renewal - must take into account the remaining days of the current subscription
    $query = $db->getQuery( true );
    $query->select( $query->quoteName('valid_untill'))->from( $query->quoteName('purchases'))->where( 'user_id = ' . $user->get('id') . ' AND active = 1' );
    $db->setQuery( $query );
    try {
	$validity = $db->loadResult();
    } catch (Exception $e) {

    }

    $validity = new DateTime( $validity, new DateTimeZone('Europe/Berlin'));
}
$validity->add( new DateInterval('P1Y'));
$subscription->valid = $validity->format( 'Y-m-d H:i:s' );

$subscription->net = $netAmount;
$subscription->vat = $vat;
$subscription->rate = $rate;
$subscription->total = number_format( $netAmount + $vat, 2 );

// Compute discount if necessary
if( $subscription->discount ) {
    require_once( 'discount.php' );
    $subscription = calcDiscount( $subscription );
}

// Set the appropriate article for redirection
switch( $level[0] ) {
    case '1' :
	if( $subscription->paymentMethodId[0] == 1 ) {
	    $subscription->content = 11;
	    $subscription->menuitem = 169;
	} else {
	    $subscription->content = 16;
	    $subscription->menuitem = 202;
	}
	break;
    case '2' :
	if( $subscription->paymentMethodId[0] == 1 ) {
	    $subscription->content = 14;
	    $subscription->menuitem = 200;
	} else {
	    $subscription->content = 17;
	    $subscription->menuitem = 203;
	}
	break;
    case '3' :
	if( $subscription->paymentMethodId[0] == 1 ) {
	    $subscription->content = 15;
	    $subscription->menuitem = 201;
	} else {
	    $subscription->content = 18;
	    $subscription->menuitem = 204;
	}
	break;
}

$subscription->purchaseType = 1;

$session = JFactory::getSession();
$session->set( 'subs', $subscription );

$url = 'index.php?option=com_fabrik&view=form&formid=9&Itemid=276';
$app->redirect( $url);