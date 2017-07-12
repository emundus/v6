 <?php
/**
* Geoblock View para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

// Protect from unauthorized access
defined('_JEXEC') or die();
JRequest::checkToken( 'get' ) or die( 'Invalid Token' );

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

<script type="text/javascript" language="javascript">	
	function muestra_progreso(){
		jQuery("#div_update_geoblock_database").modal('show');		
		jQuery("#div_refresh").show();
	}
</script>

<div class="securitycheck-bootstrap">


<div class="row-fluid">
<div class="box span12">
	<div class="box-header well" data-original-title>
		<i class="icon-list-alt"></i><?php echo ' ' . JText::_('COM_SECURITYCHECKPRO_GEOBLOCK_LABEL'); ?>
		<div class="box-icon">
			<a href="#" class="btn btn-minimize btn-round"><i class="icon-chevron-up"></i></a>
		</div>
	</div>
	<div class="box-content">
	
		<div id="div_update_geoblock_database" class="modal hide fade">
			<fieldset class="uploadform" style="margin-left: 10px;">
				<legend><?php echo JText::_('COM_SECURITYCHECKPRO_UPDATE_DATABASE_TEXT'); ?></legend>
				<div class="form-actions center" id="div_refresh" style="display:none;">
					<span class="tammano-18"><?php echo JText::_('COM_SECURITYCHECKPRO_UPDATING'); ?></span><br/>					
				</div>						
			</fieldset>			
		</div>
		
		<div class="alert alert-info">
			<h5><?php echo JText::_('COM_SECURITYCHECKPRO_GEOBLOCK_DESCRIPTION'); ?></h5>
		<?php if ($this->geoip_database_update > 30) {?>	
			<button class="btn btn-info" type="button" onclick="muestra_progreso(); Joomla.submitbutton('update_geoblock_database');"><?php echo JText::_( 'COM_SECURITYCHECKPRO_UPDATE_GEOBLOCK_DATABASE' ); ?></button>
		<?php } ?>
		</div>
				
		<div class="label label-important">
			<h5><?php echo JText::_('COM_SECURITYCHECKPRO_GEOBLOCK_ALERT'); ?></h5>
		</div>
		
		<div class="span11">			
			
			<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&view=geoblock&'. JSession::getFormToken() .'=1');?>" method="post" name="adminForm" id="adminForm" >
				<input type="hidden" name="option" value="com_securitycheckpro" />
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="boxchecked" value="1" />
				<input type="hidden" name="controller" value="geoblock" />
					
				<fieldset id="continents">
					<legend><?php echo JText::_('COM_SECURITYCHECKPRO_CONTINENTS_LABEL')?></legend>
					
					<button class="btn btn-small btn-primary" type="button" onclick="CheckAll('continents',true)"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CHECK_ALL' ); ?></button>
					<button class="btn btn-small" type="button" onclick="CheckAll('continents',false)"><?php echo JText::_( 'COM_SECURITYCHECKPRO_UNCHECK_ALL' ); ?></button>
								
					<table id="continents" class="table table-bordered" style="margin-top: 10px;">
						<tbody>
							<?php
							foreach($this->allContinents as $code => $name) {
								if(empty($name)) continue;
								$checked = in_array($code, $this->continents) ? 'checked="$checked"' : '';								
							?>									
							<tr>
							<?php
							if ( $checked ) {		
							?>	
									<td class="marcado">
							<?php 
								} else {
							?>
									<td>
							<?php
								}
							?>
									<?php echo $name; ?>
									<input type="checkbox" name="continent[<?php echo $code?>]" id="continent<?php echo $code?>" <?php echo $checked?> />
								</td>
							</tr>
							<?php 
							} 
							?>
						</tbody>
					</table>				
				</fieldset>
				

				<fieldset id="countries">
					<legend><?php echo JText::_('COM_SECURITYCHECKPRO_COUNTRIES_LABEL')?></legend>
					
					<button class="btn btn-small btn-primary" type="button" onclick="CheckAll('countries',true)"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CHECK_ALL' ); ?></button>
					<button class="btn btn-small" type="button" onclick="CheckAll('countries',false)"><?php echo JText::_( 'COM_SECURITYCHECKPRO_UNCHECK_ALL' ); ?></button>
					
					<table id="countries" class="table table-bordered" style="margin-top: 10px;">
						<tbody>
							<?php
							$i = 0;
							
							foreach($this->allCountries as $code => $name) {
								$i++;
								if(empty($name)) continue;
								$checked = in_array($code, $this->countries) ? 'checked="$checked"' : '';								
							?>
							<?php 
								if ( ($i % 4 == 0) && ($i<4) ) {
									echo '<tr>';
								}
								if ( $checked ) {		
							?>	
									<td class="marcado">
							<?php 
								} else {
							?>
									<td>
							<?php
								}
							?>							
									<?php echo $name; ?>
									<input type="checkbox" name="country[<?php echo $code?>]" id="country<?php echo $code?>" <?php echo $checked?> />
								</td>								
							<?php 
								if($i % 4 == 0) {
									echo '</tr>';
								}
							} 
							?>
						</tbody>
					</table>
				</fieldset>
			</form>

		</div>
	</div>
</div>
</div>

<script type="text/javascript">
    function CheckAll(idname, checktoggle) {
		var checkboxes = new Array();
		checkboxes = document.getElementById(idname).getElementsByTagName('input');
		
		for (var i=0; i<checkboxes.length; i++) {
			if (checkboxes[i].type == 'checkbox') {
				checkboxes[i].checked = checktoggle;
			}			
		}
		
    }
</script>


</div>