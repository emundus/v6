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

function action( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  '0', JText::_( 'COM_SECURITYCHECKPRO_DO_NOTHING' ) ),
		JHTML::_('select.option',  '1', JText::_( 'COM_SECURITYCHECKPRO_ADD_IP_TO_DYNAMIC_BLACKLIST' ) ),
		JHTML::_('select.option',  '2', JText::_( 'COM_SECURITYCHECKPRO_ADD_IP_TO_BLACKLIST' ) )
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
?>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&view=firewallinspector&'. JSession::getFormToken() .'=1');?>" method="post" name="adminForm" id="adminForm">

<?php if ($this->url_inspector_enabled == 0) { ?>

<div class="alert alert-warning centrado">
	<h4><?php echo JText::_( 'COM_SECURITYCHECKPRO_URL_INPECTOR_DISABLED' ); ?></h4>
	<button class="btn btn-success" onclick="Joomla.submitbutton('enable_url_inspector')" href="#">
		<i class="icon-ok icon-white"> </i>
			<?php echo JText::_('COM_SECURITYCHECKPRO_ENABLE'); ?>
	</button>			
</div>
<?php } ?>

<div class="securitycheck-bootstrap">

<div class="row-fluid">
<div class="box span12">
	<div class="box-header well" data-original-title>
		<i class="icon-list-alt"></i><?php echo ' ' . JText::_('COM_SECURITYCHECKPRO_CPANEL_URL_INSPECTOR_TEXT'); ?>
		<div class="box-icon">
			<a href="#" class="btn btn-minimize btn-round"><i class="icon-chevron-up"></i></a>
		</div>
	</div>
	<div class="box-content">
	
		<div class="well span4 top-block">
			<legend><?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_PARAMETERS') ?></legend>
				<div class="control-group">
					<label for="write_log_inspector" class="control-label" title="<?php echo JText::_('COM_SECURITYCHECKPRO_URL_INSPECTOR_WRITE_LOG_DESCRIPTION'); ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_URL_INSPECTOR_WRITE_LOG_LABEL'); ?></label>
					<div class="controls">
						<?php echo booleanlist('write_log_inspector', array(), $this->write_log_inspector) ?>
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_URL_INSPECTOR_WRITE_LOG_DESCRIPTION') ?></small></p></blockquote>
				</div>								
				<div class="control-group">
					<label for="action_inspector" class="control-label" title="<?php echo JText::_('COM_SECURITYCHECKPRO_URL_INSPECTOR_ACTION_DESCRIPTION'); ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_UPLOADSCANNER_ACTIONS_LABEL'); ?></label>
					<div class="controls">
						<?php echo action('action_inspector', array(), $this->action_inspector) ?>
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_URL_INSPECTOR_ACTION_DESCRIPTION') ?></small></p></blockquote>
				</div>
				<div class="control-group">
					<label for="send_email_inspector" class="control-label" title="<?php echo JText::_('COM_SECURITYCHECKPRO_URL_INSPECTOR_SEND_EMAIL_DESCRIPTION'); ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_URL_INSPECTOR_SEND_EMAIL_LABEL'); ?></label>
					<div class="controls">
						<?php echo booleanlist('send_email_inspector', array(), $this->send_email_inspector) ?>
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_URL_INSPECTOR_SEND_EMAIL_DESCRIPTION') ?></small></p></blockquote>
				</div>	
		</div>
		
		<div class="well span8 top-block">
			<legend><?php echo JText::_('COM_SECURITYCHECKPRO_URL_INSPECTOR_FORBIDDEN_WORDS_LABEL') ?></legend>
				<div class="control-group">
					<label for="inspector_forbidden_words" class="control-label" title="<?php echo JText::_('COM_SECURITYCHECKPRO_URL_INSPECTOR_FORBIDDEN_WORDS_DESCRIPTION') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_URL_INSPECTOR_FORBIDDEN_WORDS_LABEL'); ?></label>
					<div class="controls">
						<textarea cols="35" rows="3" name="inspector_forbidden_words" style="width: 560px; height: 340px;"><?php echo $this->inspector_forbidden_words ?></textarea>
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_URL_INSPECTOR_FORBIDDEN_WORDS_DESCRIPTION') ?></small></p></blockquote>
				</div>			
		</div>
		
	</div>
</div>
</div>

</div>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="firewallinspector" />
</form>