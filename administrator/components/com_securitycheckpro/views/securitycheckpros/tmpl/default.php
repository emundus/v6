<?php 

/**
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted access');
JRequest::checkToken( 'get' ) or die( 'Invalid Token' );

$type_array = array(JHtml::_('select.option','Component', JText::_('COM_SECURITYCHECKPRO_TITLE_COMPONENT')),
			JHtml::_('select.option','Plugin', JText::_('COM_SECURITYCHECKPRO_TITLE_PLUGIN')),
			JHtml::_('select.option','Module', JText::_('COM_SECURITYCHECKPRO_TITLE_MODULE')));
			
$vulnerable_array = array(JHtml::_('select.option','Si', JText::_('COM_SECURITYCHECKPRO_HEADING_VULNERABLE')),
			JHtml::_('select.option','No', JText::_('COM_SECURITYCHECKPRO_GREEN_COLOR')));

// Add style declaration
$media_url = "media/com_securitycheckpro/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);

$bootstrap_css = "media/com_securitycheckpro/stylesheets/bootstrap.min.css";
JHTML::stylesheet($bootstrap_css);

JHtml::_('formbehavior.chosen', 'select');
?>

<div class="securitycheck-bootstrap">
	<?php if ( ($this->update_database_plugin_exists) && ($this->update_database_plugin_enabled) && ($this->database_message == "PLG_SECURITYCHECKPRO_UPDATE_DATABASE_DATABASE_UPDATED") ) { ?>
		<div class="label label-success">
			<h4><?php echo JText::_( 'COM_SECURITYCHECKPRO_REAL_TIME_UPDATES' ); ?></h4>
			<p><strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_DATABASE_VERSION' ); ?></strong><?php echo($this->database_version); ?></p>
			<p><strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_LAST_CHECK' ); ?></strong><?php echo($this->last_check); ?></p>
		</div>
	<?php } else if ( ($this->update_database_plugin_exists) && ($this->update_database_plugin_enabled) && (is_null($this->database_message)) ) { ?>
		<div class="label label-success">
			<h4><?php echo JText::_( 'COM_SECURITYCHECKPRO_REAL_TIME_UPDATES' ); ?></h4>
			<p><strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_REAL_TIME_UPDATES_NOT_LAUNCHED' ); ?></strong></p>						
		</div>
	<?php } else if ( ($this->update_database_plugin_exists) && ($this->update_database_plugin_enabled) && ( !($this->database_message == "PLG_SECURITYCHECKPRO_UPDATE_DATABASE_DATABASE_UPDATED") && !(is_null($this->database_message) )) ) { ?>
		<div class="label label-important">
			<h4><?php echo JText::_( 'COM_SECURITYCHECKPRO_REAL_TIME_UPDATES_PROBLEM' ); ?></h4>
			<p><strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_DATABASE_MESSAGE' ); ?></strong><?php echo JText::_( $this->database_message ); ?></p>
			<?php
				if ( $this->database_message != "COM_SECURITYCHECKPRO_UPDATE_DATABASE_SUBSCRIPTION_EXPIRED" ) {
			?>
				<a href="<?php echo 'index.php?option=com_plugins&task=plugin.edit&extension_id=' . $this->plugin_id?>" class="btn"><?php echo JText::_('COM_SECURITYCHECKPRO_CHECK_CONFIG'); ?></a>			
			<?php } else { ?>
				<a href="https://securitycheck.protegetuordenador.com/subscriptions" target="_blank" class="btn"><?php echo JText::_('COM_SECURITYCHECKPRO_RENEW'); ?></a>
			<?php } ?>
			
		</div>	
	<?php } else if ( !($this->update_database_plugin_exists) ) { ?>
		<div class="label label-info">
			<h4><?php echo JText::_( 'COM_SECURITYCHECKPRO_REAL_TIME_UPDATES_NOT_INSTALLED' ); ?></h4>
			<p><strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_REAL_TIME_UPDATES_NOT_RECEIVE' ); ?></strong></p>			
		</div>
	
	<?php } ?>
	<p class="text-success"><strong><?php echo($this->comp_ok); ?></strong></p>	
	<p class="text-success"><strong><?php echo($this->core_actualizado); ?></strong></p>					
</div>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=securitycheckpro&'. JSession::getFormToken() .'=1');?>" method="post" name="adminForm" id="adminForm">

<div id="editcell">
<div class="accordion-group">
<table class="table table-striped">
<caption style="font-weight:bold;font-size:10pt",align="center"><?php echo JText::_( 'COM_SECURITYCHECKPRO_COLOR_CODE' ); ?></caption>
<thead>
	<tr>
		<td><span class="label label-success"> </span>
		</td>
		<td>
			<?php echo JText::_( 'COM_SECURITYCHECKPRO_GREEN_COLOR' ); ?>
		</td>
		<td><span class="label label-warning"> </span>
		</td>
		<td>
			<?php echo JText::_( 'COM_SECURITYCHECKPRO_YELLOW_COLOR' ); ?>
		</td>
		<td><span class="label label-important"> </span>
		</td>
		<td>
			<?php echo JText::_( 'COM_SECURITYCHECKPRO_RED_COLOR' ); ?>
		</td>
	</tr>
</thead>
</table>
</div>

<div>
	<div>
		<select name="filter_extension_type" class="inputbox" onchange="this.form.submit()">
			<option value=""><?php echo JText::_('COM_SECURITYCHECKPRO_TYPE_DESCRIPTION');?></option>
			<?php echo JHtml::_('select.options', $type_array, 'value', 'text', $this->state->get('filter.extension_type'));?>
		</select>
		<select name="filter_vulnerable" class="inputbox" onchange="this.form.submit()">
			<option value=""><?php echo JText::_('COM_SECURITYCHECKPRO_VULNERABILITIES');?></option>
			<?php echo JHtml::_('select.options', $vulnerable_array, 'value', 'text', $this->state->get('filter.vulnerable'));?>
		</select>
		<span class="badge badge-info" style="padding: 10px 10px 10px 10px; float:right;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_UPDATE_DATE' ) . $this->last_update; ?></span>
	</div>
</div>

<table class="table table-bordered table-hover">
<thead>
	<tr>
		<th width="5" class="vulnerabilities">
			<?php echo JText::_( 'COM_SECURITYCHECKPRO_HEADING_ID' ); ?>
		</th>
		<th class="vulnerabilities">
			<?php echo JText::_( 'COM_SECURITYCHECKPRO_HEADING_PRODUCT' ); ?>
		</th>
		<th class="vulnerabilities">
			<?php echo JText::_( 'COM_SECURITYCHECKPRO_HEADING_TYPE' ); ?>
		</th>
		<th class="vulnerabilities">
			<?php echo JText::_( 'COM_SECURITYCHECKPRO_HEADING_INSTALLED_VERSION' ); ?>
		</th>
		<th class="vulnerabilities">
			<?php echo JText::_( 'COM_SECURITYCHECKPRO_HEADING_VULNERABLE' ); ?>
		</th>
	</tr>
</thead>
<?php
$k = 0;
if ( !empty($this->items) ) {
	foreach ($this->items as &$row) {
?>
<tr class="<?php echo "row$k"; ?>">
	<td align="center">
		<?php echo $row->id; ?>
	</td>
	<td align="center">
		<?php
			$vulnerable = $row->Vulnerable;
			if ( $vulnerable <> 'No' )
			{
				$link 		= JRoute::_( 'index.php?option=com_securitycheckpro&controller=securitycheckpro&task=view&product='. $row->Product );
				echo "<a href=\" $link \">$row->Product</a>";
			} else {
				echo $row->Product; 
			}
		?>	
	</td>
	<?php 
		$type = $row->sc_type;
		if ( $type == 'core' ) {
		 echo "<td><span class=\"label\" style=\"background-color: #FFADF5; \">";
		} else if ( $type == 'component' ) {
		 echo "<td><span class=\"label label-info\">";
		} else if ( $type == 'module' ) {
		 echo "<td><span class=\"label\">";
		} else {
		 echo "<td><span class=\"label label-inverse\">";
		}
	?>
	<?php echo JText::_('COM_SECURITYCHECKPRO_TYPE_' . $row->sc_type); ?>
	</span>
	</td>
	<td align="center">
		<?php echo $row->Installedversion; ?>
	</td>
<?php 
$vulnerable = $row->Vulnerable;
if ( $vulnerable == 'Si' )
{
 echo "<td><span class=\"label label-important\">";
} else if ( $vulnerable == 'Indefinido' )
{
 echo "<td><span class=\"label label-warning\">";
} else
{
 echo "<td><span class=\"label label-success\">";
}
?>
<?php echo JText::_('COM_SECURITYCHECKPRO_VULNERABLE_' . $row->Vulnerable); ?>
</span>
</td>
</tr>
<?php
	$k = 1 - $k;
	}
}
?>
</table>
</div>

<?php
if ( !empty($this->items) ) {		
?>
<div class="margen">
	<div>
		<?php echo $this->pagination->getListFooter(); echo $this->pagination->getLimitBox(); ?>
	</div>
</div>
<?php
}
?>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="securitycheckpro" />
</form>