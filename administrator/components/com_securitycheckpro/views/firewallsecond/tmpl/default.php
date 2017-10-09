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

function secondredirectlist( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
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
		jQuery("#second_level_limit_words").keypress(function(e) {
            var verified = (e.which == 8 || e.which == undefined || e.which == 0) ? null : String.fromCharCode(e.which).match(/[^0-9]/);
            if (verified) {e.preventDefault();}
		});
	});
		
</script>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&view=firewallsecond&'. JSession::getFormToken() .'=1');?>" method="post" name="adminForm" id="adminForm">

<div class="securitycheck-bootstrap">

<div class="row-fluid">
<div class="box span12">
	<div class="box-header well" data-original-title>
		<i class="icon-list-alt"></i><?php echo ' ' . JText::_('PLG_SECURITYCHECKPRO_SECOND_LEVEL_LABEL'); ?>
		<div class="box-icon">
			<a href="#" class="btn btn-minimize btn-round"><i class="icon-chevron-up"></i></a>
		</div>
	</div>
	<div class="box-content">
		
		<div class="well span4 top-block">
			<legend><?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_PARAMETERS') ?></legend>
				<div class="control-group">
					<label for="second_level" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_SECOND_LEVEL_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_SECOND_LEVEL_LABEL'); ?></label>
					<div class="controls">
						<?php echo booleanlist('second_level', array(), $this->second_level) ?>
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SECOND_LEVEL_DESCRIPTION') ?></small></p></blockquote>
				</div>	
				
				<div class="control-group">
					<label for="second_level" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_REDIRECT_IF_PATTERN_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_REDIRECT_IF_PATTERN_LABEL'); ?></label>
					<div class="controls">
						<?php echo secondredirectlist('second_level_redirect', array(), $this->second_level_redirect) ?>
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_REDIRECT_IF_PATTERN_DESCRIPTION') ?></small></p></blockquote>
				</div>
					
				<div class="control-group">
					<label for="second_level_limit_words" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_LIMIT_WORDS_DESCRIPTION') ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_LIMIT_WORDS_LABEL'); ?></label>
					<div class="controls">
						<input type="text" size="2" maxlength="2" id="second_level_limit_words" name="second_level_limit_words" value="<?php echo $this->second_level_limit_words ?>" title="" />		
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_LIMIT_WORDS_DESCRIPTION') ?></small></p></blockquote>
				</div>				
		</div>
		
		<div class="well span8 top-block">
			<legend><?php echo JText::_('PLG_SECURITYCHECKPRO_SECOND_LEVEL_WORDS_LABEL') ?></legend>
				<div class="control-group">
					<label for="second_level_words" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_SECOND_LEVEL_WORDS_DESCRIPTION') ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_SECOND_LEVEL_WORDS_LABEL'); ?></label>
					<div class="controls">
						<textarea cols="35" rows="3" name="second_level_words" style="width: 560px; height: 340px;"><?php echo $this->second_level_words ?></textarea>
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SECOND_LEVEL_WORDS_DESCRIPTION') ?></small></p></blockquote>
				</div>			
		</div>
		
	</div>
</div>
</div>

</div>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="firewallsecond" />
</form>