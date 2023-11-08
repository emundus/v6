<?php // no direct access
defined('_JEXEC') or die('Restricted access');

$deadline = !empty($admission) ? new JDate($user->fnums[$user->fnum]->admission_end_date) : new JDate($user->end_date);

$renew           = '';
$step_form       = $forms < 100 ? '' : 'completed';
$step_attachment = $attachments < 100 ? '' : 'completed';
if (!empty($cart) && !$cartorder) {
	$step_paiement = '';
}
else if ($paid && $order->order_status == 'pending') {
	$step_paiement = '';
}
else {
	$step_paiement = (@$paid == false) ? '' : 'completed';
}

$uri          =& JFactory::getURI();
$url          = explode('&', $uri->toString());
$details_view = array_search('view=details', $url);


$btn_send = '';
if ($forms >= 100 && $attachments >= 100 && $sent == 0) {
	$btn_send = '
  <div class="ui vertical mini button">
    <div class="visible content">
    <a href="' . $checkout_url . '" title="' . JText::_('SEND_APPLICATION_FILE') . '">
      <i class="mail outline icon"></i>
    </a>
  </div>';
}

?>
<div class="ui attached segment">
    <p><?= ($show_programme == 1) ? '<b>' . $user->campaign_name . '</b> ' . @$renew : ''; ?></p>
	<?= ($show_deadline == 1) ? '<p align="right">' . JText::_('MOD_EMUNDUSFLOW_DEADLINE') . ' : <b>' . $deadline->format(JText::_('DATE_FORMAT_LC2')) . '</b> ' . $offset . '</p>' : ''; ?>
</div>
<div class="ui tablet stackable bottom attached steps">
	<?php if ($show_back_button == 1): ?>
        <div class="step">
            <a href="<?= $home_link; ?>" title="<?= JText::_('RETURN'); ?>">
                <i class="large arrow left outline icon"></i> <?= JText::_('RETURN'); ?>
            </a>
        </div>
	<?php endif; ?>
	<?php if ($show_form_step == 1 && $form_list): ?>
        <div class="<?php echo ($view == "form") ? "active" : ""; ?> <?php echo $step_form; ?> step">
            <i class="large text file outline icon"></i>
            <div class="content">
                <div class="description"><?php echo JText::sprintf('FORM_FILLED', $forms); ?></div>
            </div>
        </div>
	<?php endif; ?>
	<?php if ($show_document_step == 1 && $attachment_list): ?>
        <div class="<?= ($view == "checklist") ? "active" : ""; ?> <?= $step_attachment; ?> step">
            <i class="large attach outline icon"></i>
            <div class="content">
                <div class="description"><?= JText::sprintf('ATTACHMENT_SENT', $attachments); ?></div>
            </div>
        </div>
	<?php endif ?>
	<?php if ($application_fee == 1 && $details_view === false): ?>

        <div class="<?php echo ($option == "com_hikashop") ? "active" : ""; ?> <?php echo $step_paiement; ?> step">
            <!-- ICON -->
			<?php if ($cart == true && !$paid && !empty($order) && !$orderCancelled): ?>
				<?php if ($order->order_payment_method == 'paybox') : ?>
                    <i class="large credit card alternative icon"></i>
				<?php else: ?>
                    <i class="large time outline icon"></i>
				<?php endif; ?>
			<?php elseif ($paid == false && !$orderCancelled && !empty($order)): ?>
				<?php if ($order->order_payment_method == 'paybox') : ?>
                    <i class="large credit card alternative icon"></i>
				<?php else: ?>
                    <i class="large time outline icon"></i>
				<?php endif; ?>
			<?php elseif ($paid == false && $orderCancelled): ?>
                <i class="large ban outline icon"></i>
			<?php elseif (isset($scholarship) && $scholarship) : ?>
                <i class="large student icon"></i>
			<?php else: ?>
				<?php if ($cart) : ?>
                    <i class="large add to cart icon"></i>
				<?php elseif ($paid && $order->order_status == 'pending') : ?>
                    <i class="large time outline icon"></i>
				<?php else: ?>
                    <i class="large add to cart icon"></i>
				<?php endif; ?>
			<?php endif; ?>

            <!-- TEXT -->
            <div class="content">
				<?php if (isset($scholarship) && $scholarship) : ?>
                    <div class="description"> <?php echo JText::_('HAS_SCHOLARSHIP'); ?> </div>
				<?php elseif (!empty($cart)): ?>
					<?php if ($order->order_status == 'pending'): ?>
                        <div class="description"> <?php echo ($cartorder) ? JText::_('AWAITING_PAYMENT') : JText::_('CART_BUILDING'); ?> </div>
					<?php else: ?>
                        <div class="description"> <?php echo ($cartorder) ? JText::_('AWAITING_PAYMENT') : JText::_('CART_BUILDING'); ?> </div>
					<?php endif; ?>
				<?php else: ?>
                    <div class="description">
						<?php if ($paid && $order->order_status == 'confirmed') : ?>
							<?php echo JText::_('APPLICATION_PAID'); ?>
						<?php elseif ($paid && $order->order_status == 'pending'): ?>
							<?php echo JText::_('APPLICATION_PAID_WAITING'); ?>
						<?php else: ?>
							<?php echo JText::_('APPLICATION_NOT_PAID'); ?>
						<?php endif; ?>
                    </div>
				<?php endif; ?>

                <!-- LINK -->
                <div class="description">
					<?php if ($paid == false && !empty($order) && ($order->order_payment_method == 'banktransfer' || $order->order_payment_method == 'check')) : ?>
						<?php echo JText::_('AWAITING_PAYMENT'); ?>
					<?php elseif ($paid == false && !empty($order)): ?>
						<?php echo '<a href="' . $checkout_url . '" title="' . JText::_('RETRY_PAYMENT') . '">' . JText::_('RETRY_PAYMENT') . '</a>'; ?>
					<?php elseif ($paid == false && $forms >= 100 && $attachments >= 100 && !$orderCancelled && !isset($scholarship) && !$cart): ?>
						<?php echo '<a href="' . $checkout_url . '" title="' . JText::_('CART_ORDER_NOW') . '">' . JText::_('CART_ORDER_NOW') . '</a>'; ?>
					<?php elseif ($paid == false && $forms >= 100 && $attachments >= 100 && $orderCancelled): ?>
						<?php echo '<a href="' . $checkout_url . '" title="' . JText::_('PAYMENT_DECLINED') . '">' . JText::_('PAYMENT_DECLINED') . '</a>'; ?>
					<?php endif ?>
                </div>
            </div>
        </div>
	<?php endif; ?>
	<?php if ($show_status == 1) { ?>
        <div class="<?php echo $sent > 0 ? 'completed' : ''; ?> step">
            <i class="large time outline icon"></i>
            <div class="content">
                <div class="description"><span
                            class="label label-<?php echo $current_application->class; ?>"> <?php echo @$current_application->value; ?></span>
                </div>
            </div>
        </div>
	<?php } ?>
</div>
<?php
if ($sent > 0) {
	echo '<style type="text/css"> .submit_form {display: none;} </style>';
}
?>
