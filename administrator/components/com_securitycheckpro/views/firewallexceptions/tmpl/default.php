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

function booleanlist_js( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  '0', JText::_( 'COM_SECURITYCHECKPRO_NO' ) ),
		JHTML::_('select.option',  '1', JText::_( 'COM_SECURITYCHECKPRO_YES' ) )
	);
	return JHTML::_('select.genericlist',  $arr, $name, 'onchange="Disable()"', 'value', 'text', (int) $selected, $id );
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

<script type="text/javascript">
	var ExceptionsActiveTab = "header_referer"; 
	
	function SetActiveTab($value) {
		ExceptionsActiveTab = $value;
		storeValue('exceptions_active', ExceptionsActiveTab);
	}
	
	function storeValue(key, value) {
		if (localStorage) {
			localStorage.setItem(key, value);
		} else {
			$.cookies.set(key, value);
		}
	}
	
	function getStoredValue(key) {
		if (localStorage) {
			return localStorage.getItem(key);
		} else {
			return $.cookies.get(key);
		}
	}
	
	function hideElement(Id) {
		document.getElementById(Id).innerHTML = '';
	}
	
	window.onload = function() {
		ExceptionsActiveTab = getStoredValue('exceptions_active');
		$('.nav-tabs a[href=#'+ExceptionsActiveTab+']').tab('show');
	};
</script>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&view=firewallexceptions&'. JSession::getFormToken() .'=1');?>" method="post" name="adminForm" id="adminForm">

<div class="securitycheck-bootstrap">

<div class="row-fluid">
<div class="box span12">
	<div class="box-header well" data-original-title>
		<i class="icon-list-alt"></i><?php echo ' ' . JText::_('PLG_SECURITYCHECKPRO_EXCEPTIONS_LABEL'); ?>
		<div class="box-icon">
			<a href="#" class="btn btn-minimize btn-round"><i class="icon-chevron-up"></i></a>
		</div>
	</div>
	
	<div class="box-content">
		<div class="control-group">
			<label for="exclude_exceptions_if_vulnerable" class="control-label" title="<?php echo JText::_('COM_SECURITYCHECKPRO_EXCLUDE_EXCEPTIONS_IF_VULNERABLE_DESCRIPTION'); ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_EXCLUDE_EXCEPTIONS_IF_VULNERABLE_LABEL'); ?></label>
			<div class="controls">
				<?php echo booleanlist('exclude_exceptions_if_vulnerable', array(), $this->exclude_exceptions_if_vulnerable) ?>
			</div>
			<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_EXCLUDE_EXCEPTIONS_IF_VULNERABLE_DESCRIPTION') ?></small></p></blockquote>
		</div>
	</div>
	
	<div class="box-content">
		<ul class="nav nav-tabs" id="myTab">
			<li id="header_referer_li" onclick="SetActiveTab('header_referer');"><a href="#header_referer"><?php echo JText::_('PLG_SECURITYCHECKPRO_CHECK_HEADER_REFERER_LABEL') ?></a></li>
			<li id="base64_li" onclick="SetActiveTab('base64');"><a href="#base64"><?php echo JText::_('PLG_SECURITYCHECKPRO_CHECK_BASE64_LABEL') ?></a></li>
			<li id="xss_li" onclick="SetActiveTab('xss');"><a href="#xss"><?php echo JText::_('XSS') ?></a></li>
			<li id="sql_li" onclick="SetActiveTab('sql');"><a href="#sql"><?php echo JText::_('SQL Injection') ?></a></li>
			<li id="lfi_li" onclick="SetActiveTab('lfi');"><a href="#lfi"><?php echo JText::_('PLG_SECURITYCHECKPRO_LFI_EXCEPTIONS_LABEL') ?></a></li>
			<li id="second_li" onclick="SetActiveTab('second');"><a href="#second"><?php echo JText::_('PLG_SECURITYCHECKPRO_SECOND_LEVEL_EXCEPTIONS_LABEL') ?></a></li>
		</ul>
		
		<div id="myTabContent" class="tab-content">
			<div class="tab-pane" id="header_referer">
				<div class="box-content">
					<div class="control-group">
						<label for="check_header_referer" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_CHECK_HEADER_REFERER_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_CHECK_HEADER_REFERER_LABEL'); ?></label>
						<div class="controls">
							<?php echo booleanlist('check_header_referer', array(), $this->check_header_referer) ?>
						</div>
						<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_CHECK_HEADER_REFERER_DESCRIPTION') ?></small></p></blockquote>
					</div>
				</div>
			</div>
			
			<div class="tab-pane" id="base64">
				<div class="box-content">
					<div class="control-group">
						<label for="check_base_64" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_CHECK_BASE64_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_CHECK_BASE64_LABEL'); ?></label>
						<div class="controls">
							<?php echo booleanlist('check_base_64', array(), $this->check_base_64) ?>
						</div>
						<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_CHECK_BASE64_DESCRIPTION') ?></small></p></blockquote>
					</div>

					<div class="control-group">
						<label for="base64_exceptions" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_BASE64_EXCEPTIONS_DESCRIPTION') ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_BASE64_EXCEPTIONS_LABEL'); ?></label>
						<div class="controls">
							<textarea cols="35" rows="3" name="base64_exceptions" style="width: 560px; height: 140px;"><?php echo $this->base64_exceptions ?></textarea>								
						</div>
						<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_BASE64_EXCEPTIONS_DESCRIPTION') ?></small></p></blockquote>
					</div>
				</div>
			</div>
			
			<div class="tab-pane" id="xss">
				<div class="box-content">					
					<div class="control-group">
						<label for="strip_all_tags" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_STRIP_ALL_TAGS_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_STRIP_ALL_TAGS_LABEL'); ?></label>
						<div class="controls" id="strip_all_tags">
							<?php echo booleanlist_js('strip_all_tags', array(), $this->strip_all_tags) ?>
						</div>
						<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_STRIP_ALL_TAGS_DESCRIPTION') ?></small></p></blockquote>
					</div>
					
					<div class="control-group" id="tags_to_filter_div">
						<label for="tags_to_filter" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_TAGS_TO_FILTER_DESCRIPTION') ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_TAGS_TO_FILTER_LABEL'); ?></label>
						<div class="controls">
							<textarea cols="35" rows="3" name="tags_to_filter" style="width: 560px; height: 140px;"><?php echo $this->tags_to_filter ?></textarea>								
						</div>
						<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_TAGS_TO_FILTER_DESCRIPTION') ?></small></p></blockquote>
					</div>	
					
					<div class="control-group">
						<label for="strip_tags_exceptions" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_STRIP_TAGS_EXCEPTIONS_DESCRIPTION') ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_STRIP_TAGS_EXCEPTIONS_LABEL'); ?></label>
						<div class="controls">
							<textarea cols="35" rows="3" name="strip_tags_exceptions" style="width: 560px; height: 140px;"><?php echo $this->strip_tags_exceptions ?></textarea>								
						</div>
						<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_STRIP_TAGS_EXCEPTIONS_DESCRIPTION') ?></small></p></blockquote>
					</div>					
				</div>
			</div>
			
			<div class="tab-pane" id="sql">
				<div class="box-content">
					<div class="control-group">
						<label for="duplicate_backslashes_exceptions" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_DUPLICATE_BACKSLASHES_EXCEPTIONS_DESCRIPTION') ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_DUPLICATE_BACKSLASHES_EXCEPTIONS_LABEL'); ?></label>
						<div class="controls">
							<textarea cols="35" rows="3" name="duplicate_backslashes_exceptions" style="width: 560px; height: 140px;"><?php echo $this->duplicate_backslashes_exceptions ?></textarea>								
						</div>
						<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_DUPLICATE_BACKSLASHES_EXCEPTIONS_DESCRIPTION') ?></small></p></blockquote>
					</div>
					
					<div class="control-group">
						<label for="line_comments_exceptions" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_LINE_COMMENTS_EXCEPTIONS_DESCRIPTION') ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_LINE_COMMENTS_EXCEPTIONS_LABEL'); ?></label>
						<div class="controls">
							<textarea cols="35" rows="3" name="line_comments_exceptions" style="width: 560px; height: 140px;"><?php echo $this->line_comments_exceptions ?></textarea>								
						</div>
						<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_LINE_COMMENTS_EXCEPTIONS_DESCRIPTION') ?></small></p></blockquote>
					</div>
					
					<div class="control-group">
						<label for="sql_pattern_exceptions" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_SQL_PATTERN_EXCEPTIONS_DESCRIPTION') ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_SQL_PATTERN_EXCEPTIONS_LABEL'); ?></label>
						<div class="controls">
							<textarea cols="35" rows="3" name="sql_pattern_exceptions" style="width: 560px; height: 140px;"><?php echo $this->sql_pattern_exceptions ?></textarea>								
						</div>
						<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SQL_PATTERN_EXCEPTIONS_DESCRIPTION') ?></small></p></blockquote>
					</div>
					
					<div class="control-group">
						<label for="if_statement_exceptions" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_IF_STATEMENT_EXCEPTIONS_DESCRIPTION') ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_IF_STATEMENT_EXCEPTIONS_LABEL'); ?></label>
						<div class="controls">
							<textarea cols="35" rows="3" name="if_statement_exceptions" style="width: 560px; height: 140px;"><?php echo $this->if_statement_exceptions ?></textarea>								
						</div>
						<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_IF_STATEMENT_EXCEPTIONS_DESCRIPTION') ?></small></p></blockquote>
					</div>
					
					<div class="control-group">
						<label for="using_integers_exceptions" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_USING_INTEGERS_EXCEPTIONS_DESCRIPTION') ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_USING_INTEGERS_EXCEPTIONS_LABEL'); ?></label>
						<div class="controls">
							<textarea cols="35" rows="3" name="using_integers_exceptions" style="width: 560px; height: 140px;"><?php echo $this->using_integers_exceptions ?></textarea>								
						</div>
						<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_USING_INTEGERS_EXCEPTIONS_DESCRIPTION') ?></small></p></blockquote>
					</div>
					
					<div class="control-group">
						<label for="escape_strings_exceptions" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_ESCAPE_STRINGS_EXCEPTIONS_DESCRIPTION') ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_ESCAPE_STRINGS_EXCEPTIONS_LABEL'); ?></label>
						<div class="controls">
							<textarea cols="35" rows="3" name="escape_strings_exceptions" style="width: 560px; height: 140px;"><?php echo $this->escape_strings_exceptions ?></textarea>								
						</div>
						<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_ESCAPE_STRINGS_EXCEPTIONS_DESCRIPTION') ?></small></p></blockquote>
					</div>					
				</div>
			</div>
			
			<div class="tab-pane" id="lfi">
				<div class="box-content">
					<div class="control-group">
						<label for="lfi_exceptions" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_LFI_EXCEPTIONS_DESCRIPTION') ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_LFI_EXCEPTIONS_LABEL'); ?></label>
						<div class="controls">
							<textarea cols="35" rows="3" name="lfi_exceptions" style="width: 560px; height: 140px;"><?php echo $this->lfi_exceptions ?></textarea>								
						</div>
						<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_LFI_EXCEPTIONS_DESCRIPTION') ?></small></p></blockquote>
					</div>				
				</div>
			</div>
			
			<div class="tab-pane" id="second">
				<div class="box-content">
					<div class="control-group">
						<label for="second_level_exceptions" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_SECOND_LEVEL_EXCEPTIONS_DESCRIPTION') ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_SECOND_LEVEL_EXCEPTIONS_LABEL'); ?></label>
						<div class="controls">
							<textarea cols="35" rows="3" name="second_level_exceptions" style="width: 560px; height: 140px;"><?php echo $this->second_level_exceptions ?></textarea>								
						</div>
						<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_SECOND_LEVEL_EXCEPTIONS_DESCRIPTION') ?></small></p></blockquote>
					</div>						
				</div>
			</div>			
		</div>
	</div>
</div>
</div>
</div>

<script type="text/javascript" language="javascript">
	// Añadimos la función Disable cuando se cargue la página para que deshabilite (o no) el desplegable del launching interval
	window.addEvent('domready', function() {		
		Disable();
	});
		
	function Disable() {
		//Obtenemos el índice de la opción 'strip all tags'
		var element = adminForm.elements["strip_all_tags"].selectedIndex;
				
		// Ocultamos o mostramos la caja de texto según la elección anterior
		if ( element==1 ) {
			$("#tags_to_filter_div").hide();			
		} else {
			$("#tags_to_filter_div").show();			
		}
		
	}
</script>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="firewallexceptions" />
</form>