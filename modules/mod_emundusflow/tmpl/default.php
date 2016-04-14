<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 


$renew='';
$step_form = $forms<100?'':'completed';
$step_attachment = $attachments<100?'':'completed';
$step_paiement = @$paid==0?'':'completed';;
$step_sent = '';
if ($sent>0) {
	$step_sent = 'completed';
	$complete =  JText::_('APPLICATION_SENT');
	if($applicant_can_renew)
		$renew =  ' <a href="index.php?option=com_emundus&view=renew_application"><i class="large repeat icon"></i>'.JText::_('RENEW_APPLICATION').'</a>';
} else {
	$complete =  '<a href="'.$confirm_form_url.'&usekey=fnum&rowid='.$user->fnum.'" title="'.JText::_('APPLICATION_NOT_SENT').'">'.JText::_('APPLICATION_NOT_SENT').'</a>';
}
?>
<div class="ui attached segment">
  <p><?php echo ($show_programme==1)?$user->campaign_name.' '.@$renew:''; ?></p>
</div>
<div class="ui tablet stackable bottom attached steps">
  <div class="<?php echo ($view=="form")?"active":""; ?> <?php echo $step_form; ?> step">
  	<i class="large text file outline icon"></i>
  	<div class="content">
    	<div class="description"><?php echo  $forms.'% '.JText::_('FORM_FILLED'); ?></div>
    </div>
  </div>
  <div class="<?php echo ($view=="checklist")?"active":""; ?> <?php echo $step_attachment; ?> step">
  	<i class="large attach outline icon"></i>
    <div class="content">
      <div class="description"><?php echo  $attachments.'% '.JText::_('ATTACHMENT_SENT'); ?></div>
    </div>
  </div>

<?php if ($application_fee == 1) { ?>
  <div class="<?php echo ($option=="com_hikashop")?"active":""; ?> <?php echo $step_paiement; ?> step">
  	<i class="large payment outline icon"></i>
    <div class="content">
      <div class="description"><?php echo  $paid>0?JText::_('APPLICATION_PAID'):JText::_('APPLICATION_NOT_PAID'); ?></div>
      <div class="description"><?php echo  $paid>0?'':'<a href="index.php?option=com_hikashop&ctrl=product&task=updatecart&quantity=1&checkout=1&product_id=1" title="'.JText::_('ORDER_NOW').'">'.JText::_('ORDER_NOW').'</a>'; ?></div>
    </div>
  </div>
<?php } ?>
  <div class="<?php echo $step_sent; ?> step">
  	<i class="large time outline icon"></i>
  	<div class="content">
    	<div class="description"><?php echo $complete; ?></div>
    	<div class="description"><span class="label label-<?php echo $current_application->class; ?>"> <?php echo $current_application->value; ?></span></div>
    </div>
  </div>
</div>
