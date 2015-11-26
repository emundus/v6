<?php // no direct access
defined('_JEXEC') or die('Restricted access'); 

$document = JFactory::getDocument();
$document->addStyleSheet( JURI::base()."media/com_emundus/lib/semantic/packaged/css/semantic.min.css" );
if($show_programme) {
?>
	<h3> <?php echo $user->campaign_name; ?> <?php echo @$renew; ?></h3>
<?php 
}
$renew='';
if ($sent>0) {
	// Apply again
	/*$query='SELECT count(id) as cpt FROM #__emundus_setup_campaigns 
			WHERE id NOT IN (
			select campaign_id FROM #__emundus_campaign_candidature WHERE applicant_id='.$user->id.' AND fnum like '.$db->Quote($user->fnum).'
			)';
	$db->setQuery($query);
	$cpt = $db->loadResult();*/
	$complete =  '<i class="large ok sign icon"></i>'.JText::_('APPLICATION_SENT');
	if($applicant_can_renew)
		$renew =  ' <a href="index.php?option=com_emundus&view=renew_application"><i class="large repeat icon"></i>'.JText::_('RENEW_APPLICATION').'</a>';
} else {
	$complete =  '<i class="large time icon"></i><a href="'.$confirm_form_url.'&usekey=fnum&rowid='.$user->fnum.'" title="'.JText::_('APPLICATION_NOT_SENT').'">'.JText::_('APPLICATION_NOT_SENT').'</a>';
}
?>
<div class="ui small steps">
  <div class="ui <?php echo $forms<100?"disabled":"active"; ?> step">
    <?php echo  '<i class="large text file outline icon"></i> '.$forms.'% '.JText::_('FORM_FILLED'); ?>
  </div>
  <div class="ui <?php echo $attachments<100?"disabled":"active"; ?> step">
    <?php echo  '<i class="large attachment icon"></i> '.$attachments.'% '.JText::_('ATTACHMENT_SENT'); ?>
  </div>
  <div class="ui <?php echo $sent<=0?"disabled red":"active"; ?> step">
    <?php echo $complete; ?>
    <span class="label label-<?php echo $current_application->class; ?>"> <?php echo $current_application->value; ?></span>
  </div>
  </div>
</div>