<?php 

/*
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access'); 
JRequest::checkToken( 'get' ) or die( 'Invalid Token' );

// Load plugin language
$lang = JFactory::getLanguage();
$lang->load('plg_system_securitycheckpro');


function booleanlist( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  '0', JText::_( 'COM_SECURITYCHECKPRO_NO' ) ),
		JHTML::_('select.option',  '1', JText::_( 'COM_SECURITYCHECKPRO_YES' ) )
	);
	return JHTML::_('select.genericlist',  $arr, $name, $attribs, 'value', 'text', (int) $selected, $id );
}

JHTML::_( 'behavior.framework', true );

// Add style declaration
$media_url = "media/com_securitycheckpro/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);

$bootstrap_css = "media/com_securitycheckpro/stylesheets/bootstrap.min.css";
JHTML::stylesheet($bootstrap_css);

$opa_icons = "media/com_securitycheckpro/stylesheets/opa-icons.css";
JHTML::stylesheet($opa_icons);

// Load Javascript
$document = JFactory::getDocument();
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheckpro/javascript/jquery.js');
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheckpro/javascript/charisma.js');
// Char libraries
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheckpro/javascript/excanvas.js');
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheckpro/javascript/jquery.flot.min.js');
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheckpro/javascript/jquery.flot.pie.min.js');
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheckpro/javascript/jquery.flot.stack.js');
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheckpro/javascript/jquery.flot.resize.min.js');
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheckpro/javascript/bootstrap-tab.js');

JHtml::_('formbehavior.chosen', 'select');
JHtml::_('jquery.framework');
?>

<script type="text/javascript" language="javascript">

	jQuery(document).ready(function() {
		jQuery("#email_max_number").keypress(function(e) {
            var verified = (e.which == 8 || e.which == undefined || e.which == 0) ? null : String.fromCharCode(e.which).match(/[^0-9]/);
            if (verified) {e.preventDefault();}
		});		
	});	
</script>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&view=firewallemail&'. JSession::getFormToken() .'=1');?>" method="post" name="adminForm" id="adminForm">

<div class="securitycheck-bootstrap">

<div class="row-fluid">
<div class="box span12">
	<div class="box-header well" data-original-title>
		<i class="icon-list-alt"></i><?php echo ' ' . JText::_('PLG_SECURITYCHECKPRO_EMAIL_NOTIFICATIONS_LABEL'); ?>
		<div class="box-icon">
			<a href="#" class="btn btn-minimize btn-round"><i class="icon-chevron-up"></i></a>
		</div>
	</div>
	<div class="box-content">
		
		<div class="well span4 top-block">
			<legend><?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_PARAMETERS') ?></legend>
				<div class="control-group">
					<label for="email_active" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_ACTIVE_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_ACTIVE_LABEL'); ?></label>
					<div class="controls">
						<?php echo booleanlist('email_active', array(), $this->email_active) ?>
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_ACTIVE_DESCRIPTION') ?></small></p></blockquote>
				</div>	
				
				<div class="control-group">
					<label for="email_subject" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_SUBJECT_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_SUBJECT_LABEL'); ?></label>
					<div class="controls">
						<input type="text" size="30" name="email_subject" value="<?php echo $this->email_subject ?>" title="" />		
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_SUBJECT_DESCRIPTION') ?></small></p></blockquote>
				</div>
					
				<div class="control-group">
					<label for="email_body" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_BODY_DESCRIPTION') ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_BODY_LABEL'); ?></label>
					<div class="controls">
						<textarea cols="35" rows="3" name="email_body" ><?php echo $this->email_body ?></textarea>
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_BODY_DESCRIPTION') ?></small></p></blockquote>
				</div>			
		</div>
		
		<div class="well span4 top-block">
			<legend><?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_PARAMETERS') ?></legend>
				<div class="control-group">
					<label for="email_to_label" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_TO_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_TO_LABEL'); ?></label>
					<div class="controls">
						<input type="text" size="30" id="email_to" name="email_to" value="<?php echo $this->email_to ?>" title="" />		
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_TO_DESCRIPTION') ?></small></p></blockquote>
				</div>
					
				<div class="control-group">
					<label for="email_from_domain" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_FROM_DOMAIN_DESCRIPTION') ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_FROM_DOMAIN_LABEL'); ?></label>
					<div class="controls">
						<input type="text" size="30" id="email_from_domain" name="email_from_domain" value="<?php echo $this->email_from_domain ?>" title="" />		
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_FROM_DOMAIN_DESCRIPTION') ?></small></p></blockquote>
				</div>	

				<div class="control-group">
					<label for="email_from_name" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_FROM_NAME_DESCRIPTION') ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_FROM_NAME_LABEL'); ?></label>
					<div class="controls">
						<input type="text" size="30" name="email_from_name" value="<?php echo $this->email_from_name ?>" title="" />		
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_FROM_NAME_DESCRIPTION') ?></small></p></blockquote>
				</div>			
		</div>
		
		<div class="well span4 top-block">
			<legend><?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_PARAMETERS') ?></legend>
				<div class="control-group">
					<label for="email_add_applied_rule" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_ADD_APPLIED_RULE_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_ADD_APPLIED_RULE_LABEL'); ?></label>
					<div class="controls">
						<?php echo booleanlist('email_add_applied_rule', array(), $this->email_add_applied_rule) ?>
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_ADD_APPLIED_RULE_DESCRIPTION') ?></small></p></blockquote>
				</div>
					
				<div class="control-group">
					<label for="email_max_number" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_MAX_NUMBER_DESCRIPTION') ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_MAX_NUMBER_LABEL'); ?></label>
					<div class="controls">
						<input type="text" size="3" maxlength="3" id="email_max_number" name="email_max_number" value="<?php echo $this->email_max_number ?>" title="" />		
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_EMAIL_MAX_NUMBER_DESCRIPTION') ?></small></p></blockquote>
				</div>					
		</div>
		
	</div>
</div>
</div>

</div>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="firewallemail" />
</form>