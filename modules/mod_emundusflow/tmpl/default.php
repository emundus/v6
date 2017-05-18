<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 

$deadline = new JDate($user->end_date);

$renew='';
$step_form = $forms<100?'':'completed';
$step_attachment = $attachments<100?'':'completed';
$step_paiement = @$paid==0?'':'completed';
$btn_send = '';
if ($forms>=100 && $attachments>=100 && $sent==0) {
  $btn_send = '
  <div class="ui vertical mini button">
    <div class="visible content">
    <a href="'.$confirm_form_url.'&usekey=fnum&rowid='.$user->fnum.'" title="'.JText::_('SEND_APPLICATION_FILE').'">
      <i class="mail outline icon"></i>
    </a>
  </div>';
}

?>
<div class="ui attached segment">
  <p><?php echo ($show_programme==1)?'<b>'.$user->campaign_name.'</b> '.@$renew:''; ?></p>
  <p align="right"><?php echo ($show_deadline==1)?JText::_('MOD_EMUNDUSFLOW_DEADLINE').' : <b>'.$deadline->format(JText::_('DATE_FORMAT_LC2')).'</b>':''; ?></p>
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
    <i class="large payment outline icon"></i>
    <div class="content">
      <div class="description"><?php echo  $paid>0?JText::_('APPLICATION_PAID'):JText::_('APPLICATION_NOT_PAID'); ?></div>
      <div class="description"><?php echo  ($paid==0 && $forms>=100 && $attachments>=100)?'<a href="'.$checkout_url.'" title="'.JText::_('ORDER_NOW').'">'.JText::_('ORDER_NOW').'</a>':''; ?></div>
    </div>
  </div>
<?php } ?>
  <div class="<?php echo $sent>0?'completed':''; ?> step">
    <i class="large time outline icon"></i>
    <div class="content">
      <div class="description"><span class="label label-<?php echo $current_application->class; ?>"> <?php echo @$current_application->value; ?></span> <?php echo $btn_send; ?></div>
    </div>
  </div>
</div>
