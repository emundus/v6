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

function redirectionlist( $name, $attribs = null, $selected = null, $id=false )
{
	$arr = array(
		JHTML::_('select.option',  '1', JText::_( 'PLG_SECURITYCHECKPRO_JOOMLA_PATH_LABEL' ) ),
		JHTML::_('select.option',  '2', JText::_( 'COM_SECURITYCHECKPRO_REDIRECTION_OWN_PAGE' ) )
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

$site_url = JURI::root();
JHtml::_('formbehavior.chosen', 'select');
?>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&view=firewallredirection&'. JSession::getFormToken() .'=1');?>" method="post" name="adminForm" id="adminForm">

<div class="securitycheck-bootstrap">

<div class="row-fluid">
<div class="box span12">
	<div class="box-header well" data-original-title>
		<i class="icon-list-alt"></i><?php echo ' ' . JText::_('PLG_SECURITYCHECKPRO_REDIRECTION_LABEL'); ?>
		<div class="box-icon">
			<a href="#" class="btn btn-minimize btn-round"><i class="icon-chevron-up"></i></a>
		</div>
	</div>
	<div class="box-content">
		
		<div class="well span7 top-block">
			<legend><?php echo JText::_('COM_SECURITYCHECKPRO_GLOBAL_PARAMETERS') ?></legend>
				<div class="control-group">
					<label for="redirect_after_attack" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_REDIRECT_AFTER_ATTACK_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_REDIRECT_AFTER_ATTACK_LABEL'); ?></label>
					<div class="controls">
						<?php echo booleanlist('redirect_after_attack', array(), $this->redirect_after_attack) ?>
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_REDIRECT_AFTER_ATTACK_DESCRIPTION') ?></small></p></blockquote>
				</div>	
				
				<div class="control-group">
					<label for="redirect_options" class="control-label" title="<?php echo JText::_('PLG_SECURITYCHECKPRO_REDIRECT_DESCRIPTION'); ?>"><?php echo JText::_('PLG_SECURITYCHECKPRO_REDIRECT_LABEL'); ?></label>
					<div class="controls" id="redirect_options">
						<?php echo redirectionlist('redirect_options', array(), $this->redirect_options) ?>
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('PLG_SECURITYCHECKPRO_REDIRECT_DESCRIPTION') ?></small></p></blockquote>
				</div>

				<div class="control-group">
					<label for="redirection_url" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_REDIRECTION_URL_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_REDIRECTION_URL_TEXT'); ?></label>
					<div class="controls controls-row">
						<div class="input-prepend">
							<span class="add-on" style="background-color: #8EBBFF;"><?php echo $site_url ?></span>
							<input class="input-large" type="text" name="redirect_url" id="redirect_url" value="<?php echo $this->redirect_url?>" placeholder="<?php echo $this->redirect_url ?>">
						</div>						
					</div>
					<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_REDIRECTION_URL_EXPLAIN') ?></small></p></blockquote>
				</div>
				
				<div class="control-group">
					<label for="editor" class="control-label-more-width" title="<?php echo JText::_('COM_SECURITYCHECKPRO_EDITOR_EXPLAIN') ?>"><?php echo JText::_('COM_SECURITYCHECKPRO_EDITOR_TEXT'); ?></label>
					<blockquote><p class="text-info"><small><?php echo JText::_('COM_SECURITYCHECKPRO_EDITOR_EXPLAIN') ?></small></p></blockquote>
					<?php 
					// IMPORT EDITOR CLASS
					jimport( 'joomla.html.editor' );

					// GET EDITOR SELECTED IN GLOBAL SETTINGS
					$config = JFactory::getConfig();
					$global_editor = $config->get( 'editor' );

					// GET USER'S DEFAULT EDITOR
					$user_editor = JFactory::getUser()->getParam("editor");

					if($user_editor && $user_editor !== 'JEditor') {
						$selected_editor = $user_editor;
					} else {
						$selected_editor = $global_editor;
					}

					// INSTANTIATE THE EDITOR
					$editor = JEditor::getInstance($selected_editor);
					
					// SET EDITOR PARAMS
					$params = array( 'smilies'=> '0' ,
						'style'  => '1' ,
						'layer'  => '0' ,
						'table'  => '0' ,
						'clear_entities'=>'0'
					);

					// DISPLAY THE EDITOR (name, html, width, height, columns, rows, bottom buttons, id, asset, author, params)
					echo $editor->display('custom_code', $this->custom_code, '400', '400', '20', '20', true, null, null, null, $params);				
					
					?>
					
					
				</div>	
		</div>
		
	</div>
</div>
</div>

<script type="text/javascript" language="javascript">
	// Añadimos la función Disable cuando se cargue la página para que deshabilite (o no) el campo de la url
	window.addEvent('domready', function() {
		Disable();
	});
	
	function Disable() {
		//Obtenemos el índice las opciones de redirección
		var element = adminForm.elements["redirect_options"].selectedIndex;
						
		// Si está establecida la opción de la propia página, habilitamos el campo redirect_url para escritura. Si no, lo deshabilitamos
		if ( element==0 ) {
			document.getElementById('redirect_url').readOnly = true;
		} else {			
			document.getElementById('redirect_url').readOnly = false;
		}
		
	}
</script>

</div>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="firewallredirection" />
</form>