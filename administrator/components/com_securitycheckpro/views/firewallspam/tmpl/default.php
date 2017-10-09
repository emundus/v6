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
$lang->load('com_securitycheckpro');


function booleanlist( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  '0', JText::_( 'COM_SECURITYCHECKPRO_NO' ) ),
		JHTML::_('select.option',  '1', JText::_( 'COM_SECURITYCHECKPRO_YES' ) )
	);
	return JHTML::_('select.genericlist',  $arr, $name, $attribs, 'value', 'text', (int) $selected, $id );
}

function email_actions( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  '0', JText::_( 'COM_SECURITYCHECKPRO_EMAIL_BOTH_INCORRECT' ) ),
		JHTML::_('select.option',  '1', JText::_( 'COM_SECURITYCHECKPRO_EMAIL_ONLY_FRONTEND' ) ),
		JHTML::_('select.option',  '2', JText::_( 'COM_SECURITYCHECKPRO_EMAIL_ONLY_BACKEND' ) )
	);
	return JHTML::_('select.genericlist',  $arr, $name, $attribs, 'value', 'text', (int) $selected, $id );
}

function spammer_action( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  '0', JText::_( 'COM_SECURITYCHECKPRO_DO_NOTHING' ) ),
		JHTML::_('select.option',  '1', JText::_( 'COM_SECURITYCHECKPRO_ADD_IP_TO_DYNAMIC_BLACKLIST' ) )
	);
	return JHTML::_('select.genericlist',  $arr, $name, $attribs, 'value', 'text', (int) $selected, $id );
}

JHTML::_( 'behavior.framework', true );
JHtml::_('behavior.multiselect');

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
		jQuery("#spammer_limit").keypress(function(e) {
            var verified = (e.which == 8 || e.which == undefined || e.which == 0) ? null : String.fromCharCode(e.which).match(/[^0-9]/);
            if (verified) {e.preventDefault();}
		});
	});
		
</script>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&view=firewallsessionprotection&'. JSession::getFormToken() .'=1');?>" method="post" name="adminForm" id="adminForm">

<div class="securitycheck-bootstrap">

<?php if ( $this->plugin_installed ) { ?>
<div class="row-fluid">
<div class="box span12">
	<div class="box-header well" data-original-title>
		<i class="icon-fire"></i><?php echo ' ' . JText::_('PLG_SECURITYCHECKPRO_SPAM_PROTECTION_LABEL'); ?>
		<div class="box-icon">
			<a href="#" class="btn btn-minimize btn-round"><i class="icon-chevron-up"></i></a>
		</div>
	</div>
	<div class="box-content">
		
		<div class="well span4 top-block">
			<legend><?php echo JText::_('COM_SECURITYCHECKPRO_CHECK_USERS') ?></legend>
				<div class="control-group">
					<label for="check_if_user_is_spammer" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_CHECK_IF_USER_IS_SPAMMER_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_CHECK_IF_USER_IS_SPAMMER_LABEL'); ?></label>
					<div class="controls">
						<?php echo booleanlist('check_if_user_is_spammer', array(), $this->check_if_user_is_spammer) ?>
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_CHECK_IF_USER_IS_SPAMMER_DESCRIPTION') ?></small></p></blockquote>
				</div>	
				
				<div class="control-group">
					<label for="spammer_action" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_SPAMMER_ACTION_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_SPAMMER_ACTION_LABEL'); ?></label>
					<div class="controls">
						<?php echo spammer_action('spammer_action', array(), $this->spammer_action) ?>
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SPAMMER_ACTION_DESCRIPTION') ?></small></p></blockquote>
				</div>
				
				<div class="control-group">
					<label for="spammer_write_log" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_SPAMMER_WRITE_LOG_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_SPAMMER_WRITE_LOG_LABEL'); ?></label>
					<div class="controls">
						<?php echo booleanlist('spammer_write_log', array(), $this->spammer_write_log) ?>
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SPAMMER_WRITE_LOG_DESCRIPTION') ?></small></p></blockquote>
				</div>
				
				<div class="control-group">
					<label for="spammer_what_to_check" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_SESSION_PROTECTION_GROUPS_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_SESSION_PROTECTION_GROUPS_LABEL'); ?></label>
					<div class="controls">
						<?php
						$options[] = JHTML::_('select.option', 0, JText::_('PLG_SECURITYCHECKPRO_EMAIL'));							
						$options[] = JHTML::_('select.option', 1, JText::_('PLG_SECURITYCHECKPRO_IP'));
						$options[] = JHTML::_('select.option', 2, JText::_('PLG_SECURITYCHECKPRO_USERNAME'));
						if ( !is_array($this->spammer_what_to_check) ) {							
							$this->spammer_what_to_check = array('Email','IP','Username');
						}						
						echo JHTML::_('select.genericlist', $options, 'spammer_what_to_check[]', 'class="inputbox" multiple="multiple"', 'text', 'text',  $this->spammer_what_to_check);												
						?>					
					</div>
					
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SESSION_PROTECTION_GROUPS_DESCRIPTION') ?></small></p></blockquote>
				</div>
				
				<div class="control-group">
					<label for="spammer_limit" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_SPAMMER_LIMIT_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_SPAMMER_LIMIT_LABEL'); ?></label>
					<div class="controls">
						<input type="text" size="3" maxlength="3" id="spammer_limit" name="spammer_limit" value="<?php echo $this->spammer_limit ?>" title="" />	
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SPAMMER_LIMIT_DESCRIPTION') ?></small></p></blockquote>
				</div>
		</div>	
	
		

</div>

</div>

<?php } else { ?>
	<div class="alert alert-warning centrado">
		<?php echo JText::_('COM_SECURITYCHECK_SPAM_PROTECTION_NOT_INSTALLED'); ?>	
	</div>
	<div class="alert alert-info centrado">
		<?php echo JText::_('COM_SECURITYCHECK_WHY_IS_NOT_INCLUDED'); ?>	
	</div>
<?php }  ?>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="firewallspam" />
</form>
