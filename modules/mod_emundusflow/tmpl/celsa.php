<?php // no direct access
defined('_JEXEC') or die('Restricted access');

$db = JFactory::getDBO();

$query = 'SELECT jesc.admission_first_end_date
FROM #__emundus_setup_campaigns jesc 
LEFT JOIN #__emundus_campaign_candidature jecc ON jecc.campaign_id = jesc.id 
WHERE jecc.fnum = ' . $db->quote($user->fnum);

$db->setQuery($query);

$admission_end_date = $db->loadResult();

// check if user has tiers temps
$has_third_time = false;

$db    = JFactory::getDBO();
$query = "SELECT id_tag 
FROM #__emundus_tag_assoc 
WHERE fnum like '" . $user->fnum . "' AND user_id like '" . $user->id . "' AND id_tag like '32'";

$db->setQuery($query);
$result = $db->loadResult();

if (!empty($result)) {
	$admission_end_date = $user->fnums[$user->fnum]->admission_end_date;
}

$deadline = !empty($admission) ? new JDate($admission_end_date) : new JDate($user->end_date);

$renew           = '';
$step_form       = $forms < 100 ? '' : 'completed';
$step_attachment = $attachments < 100 ? '' : 'completed';
$step_paiement   = (@$paid == false) ? '' : 'completed';
$btn_send        = '';
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
	<?php endif; ?>
	<?php if ($application_fee == 1 && $show_hikashop == 1): ?>
        <div class="<?= ($option == "com_hikashop") ? "active" : ""; ?> <?= $step_paiement; ?> step">
			<?php if ($paid == false && !empty($sentOrder) && !$orderCancelled): ?>
				<?php if ($sentOrder->order_payment_method == 'paybox') : ?>
                    <i class="large credit card alternative icon"></i>
				<?php else: ?>
                    <i class="large time outline icon"></i>
				<?php endif; ?>
			<?php elseif ($paid == false && $orderCancelled): ?>
                <i class="large ban outline icon"></i>
			<?php elseif (isset($scholarship) && $scholarship) : ?>
                <i class="large student icon"></i>
			<?php else: ?>
                <i class="large add to cart icon"></i>
			<?php endif; ?>
            <div class="content">
				<?php if (!isset($sentOrder) || $sentOrder->order_payment_method == 'banktransfer' || $sentOrder->order_payment_method == 'check') : ?>
					<?php if (isset($scholarship) && $scholarship) : ?>
                        <div class="description"> <?php echo JText::_('HAS_SCHOLARSHIP'); ?> </div>
					<?php else: ?>
                        <div class="description"> <?php echo ($paid) ? JText::_('APPLICATION_PAID') : JText::_('APPLICATION_NOT_PAID'); ?> </div>
					<?php endif; ?>
				<?php else : ?>
                    <div class="description"> <?php echo ($paid) ? JText::_('APPLICATION_PAID') : JText::_('PAID_VIA_CARD'); ?> </div>
				<?php endif; ?>
                <div class="description"> <?php echo ($paid == false && !empty($sentOrder) && ($sentOrder->order_payment_method == 'banktransfer' || $sentOrder->order_payment_method == 'check')) ? JText::_('AWAITING_PAYMENT') : '' ?> </div>
                <div class="description">
					<?php echo ($paid == false && !empty($sentOrder)) ? '<a href="' . $checkout_url . '" title="' . JText::_('RETRY_PAYMENT') . '">' . JText::_('RETRY_PAYMENT') . '</a>' : ''; ?>
					<?php echo ($paid == false && !empty($sentOrder) == 0 && $forms >= 100 && $attachments >= 100 && !$orderCancelled && !isset($scholarship) && (!$is_dead_line_passed || !$deadline)) ? '<a href="' . $checkout_url . '" title="' . JText::_('ORDER_NOW') . '">' . JText::_('ORDER_NOW') . '</a>' : ''; ?>
					<?php echo ($paid == false && !empty($sentOrder) == 0 && $forms >= 100 && $attachments >= 100 && $orderCancelled) ? '<a href="' . $checkout_url . '" title="' . JText::_('PAYMENT_DECLINED') . '">' . JText::_('PAYMENT_DECLINED') . '</a>' : ''; ?>
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
