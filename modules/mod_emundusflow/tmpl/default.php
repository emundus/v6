<?php // no direct access
defined('_JEXEC') or die('Restricted access');

$deadline = new JDate($user->end_date);

$renew='';
$step_form = $forms<100?'':'completed';
$step_attachment = $attachments<100?'':'completed';
$step_paiement = @$paid==0?'':'completed';
$btn_send = '';
if ($forms>=100 && $attachments>=100 && $sent == 0) {
  $btn_send = '
  <div class="ui vertical mini button">
    <div class="visible content">
    <a href="'.$checkout_url.'" title="'.JText::_('SEND_APPLICATION_FILE').'">
      <i class="mail outline icon"></i>
    </a>
  </div>';
}

?>
<div class="ui attached segment">
  <p><?php echo ($show_programme==1)?'<b>'.$user->campaign_name.'</b> '.@$renew:''; ?></p>
  <p align="right"><?php echo ($show_deadline==1)?JText::_('MOD_EMUNDUSFLOW_DEADLINE').' : <b>'.$deadline->format(JText::_('DATE_FORMAT_LC2')).'</b>':''; ?> <?php echo $offset ?></p>
</div>
<div class="ui tablet stackable bottom attached steps">
  <div class="step">
    <a href="/" title="<?php echo  JText::_('RETURN'); ?>">
      <i class="large arrow left outline icon"></i> <?php echo  JText::_('RETURN'); ?>
    </a>
  </div>
  <div class="<?php echo ($view=="form")?"active":""; ?> <?php echo $step_form; ?> step">
    <i class="large text file outline icon"></i>
    <div class="content">
      <div class="description"><?php echo JText::sprintf('FORM_FILLED', $forms); ?></div>
    </div>
  </div>
  <div class="<?php echo ($view=="checklist")?"active":""; ?> <?php echo $step_attachment; ?> step">
    <i class="large attach outline icon"></i>
    <div class="content">
      <div class="description"><?php echo JText::sprintf('ATTACHMENT_SENT', $attachments); ?></div>
    </div>
  </div>

<?php if ($application_fee == 1) { ?>
  <div class="<?php echo ($option=="com_hikashop")?"active":""; ?> <?php echo $step_paiement; ?> step">
    <?php if ($paid == 0 && count($sentOrder) > 0 && !$orderCancelled): ?>
      <?php if ($sentOrder->order_payment_method == 'paybox') :?>
        <i class="large credit card alternative icon"></i>
      <?php else: ?>
        <i class="large time outline icon"></i>
      <?php endif; ?>
    <?php elseif ($paid == 0 && $orderCancelled): ?>
      <i class="large ban outline icon"></i>
    <?php elseif (isset($scholarship) && $scholarship) :?>
      <i class="large student icon"></i>
    <?php else: ?>
      <i class="large add to cart icon"></i>
    <?php endif; ?>
    <div class="content">
      <?php if (!isset($sentOrder) || $sentOrder->order_payment_method == 'banktransfer' || $sentOrder->order_payment_method == 'check') :?>
        <?php if (isset($scholarship) && $scholarship) :?>
          <div class="description"> <?php echo JText::_('HAS_SCHOLARSHIP'); ?> </div>
        <?php else: ?>
          <div class="description"> <?php echo  ($paid>0)?JText::_('APPLICATION_PAID'):JText::_('APPLICATION_NOT_PAID'); ?> </div>
        <?php endif; ?>
      <?php else :?>
        <div class="description"> <?php echo  ($paid>0)?JText::_('APPLICATION_PAID'):JText::_('PAID_VIA_CARD'); ?> </div>
      <?php endif; ?>
      <div class="description"> <?php echo  ($paid==0 && count($sentOrder)>0 && ($sentOrder->order_payment_method == 'banktransfer' || $sentOrder->order_payment_method == 'check'))?JText::_('AWAITING_PAYMENT'):'' ?> </div>
      <div class="description">
        <?php echo  ($paid==0 && count($sentOrder)>0)?'<a href="'.$checkout_url.'" title="'.JText::_('RETRY_PAYMENT').'">'.JText::_('RETRY_PAYMENT').'</a>':''; ?>
        <?php echo  ($paid==0 && count($sentOrder)==0 && $forms>=100 && $attachments>=100 && !$orderCancelled && !isset($scholarship))?'<a href="'.$checkout_url.'" title="'.JText::_('ORDER_NOW').'">'.JText::_('ORDER_NOW').'</a>':''; ?>
        <?php echo  ($paid==0 && count($sentOrder)==0 && $forms>=100 && $attachments>=100 && $orderCancelled)?'<a href="'.$checkout_url.'" title="'.JText::_('PAYMENT_DECLINED').'">'.JText::_('PAYMENT_DECLINED').'</a>':''; ?>
      </div>
    </div>
  </div>
<?php } ?>
  <div class="<?php echo $sent>0?'completed':''; ?> step">
    <i class="large time outline icon"></i>
    <div class="content">
      <div class="description"><span class="label label-<?php echo $current_application->class; ?>"> <?php echo @$current_application->value; ?></span></div>
    </div>
  </div>
</div>
<?php
if ($sent>0) {
  echo '<style type="text/css"> .submit_form {display: none;} </style>';
}
?>