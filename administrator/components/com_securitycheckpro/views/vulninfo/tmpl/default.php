<?php 

/*
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access'); 

// Add style declaration
$media_url = "media/com_securitycheckpro/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);

$bootstrap_css = "media/com_securitycheckpro/stylesheets/bootstrap.min.css";
JHTML::stylesheet($bootstrap_css);

?>

<form action="index.php" method="post" name="adminForm" id="adminForm">

<div class="securitycheck-bootstrap">

<div id="editcell">
<div class="accordion-group">
</div>

<div>
	<?php $local_joomla_branch = explode(".",JVERSION); 
		// Construimos la cabecera de la versión de Joomla para la que se muestran vulnerabilidades según la versión instalada
		if ( $local_joomla_branch[0] == "3" ) {
			$joomla_version_header = "<img src=\"../media/com_securitycheckpro/images/compat_icon_3_x.png\" title=\"Joomla 3.x\" alt=\"Joomla 3.x\">";
		}
	?>
	<span class="badge" style="background-color: #C68C51; padding: 10px 10px 10px 10px; float:right;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_VULNERABILITY_LIST' ); echo $joomla_version_header; ?></span>
</div>

	<table class="table table-bordered table-hover">
	<thead>
		<tr>
			<th class="vulnerabilities-list" align="center" width="20%">
				<?php echo JText::_( 'COM_SECURITYCHECKPRO_VULNERABILITY_PRODUCT' ); ?>
			</th>
			<th class="vulnerabilities-list" align="center">
				<?php echo JText::_( 'COM_SECURITYCHECKPRO_VULNERABILITY_DETAILS' ); ?>
			</th>
			<th class="vulnerabilities-list" align="center">
				<?php echo JText::_( 'COM_SECURITYCHECKPRO_VULNERABILITY_CLASS' ); ?>
			</th>
			<th class="vulnerabilities-list" align="center">
				<?php echo JText::_( 'COM_SECURITYCHECKPRO_VULNERABILITY_PUBLISHED' ); ?>
			</th>
			<th class="vulnerabilities-list" align="center">
				<?php echo JText::_( 'COM_SECURITYCHECKPRO_VULNERABILITY_VULNERABLE' ); ?>
			</th>
			<th class="vulnerabilities-list" align="center">
				<?php echo JText::_( 'COM_SECURITYCHECKPRO_VULNERABILITY_SOLUTION' ); ?>
			</th>
		</tr>
	</thead>
<?php
$k = 0;
$local_joomla_branch = explode(".",JVERSION); // Versión de Joomla instalada
foreach ($this->vuln_details as &$row) {
	// Variable que indica cuándo se ha de mostrar la información de cada elemento del array
	$to_list = false;
	/* Array con todas las versiones y modificadores para las que es vulnerable el producto */
	$vuln_joomla_version_array = explode(",",$row->Joomlaversion);
	foreach ($vuln_joomla_version_array as $joomla_version) {
		$vulnerability_branch = explode(".",$joomla_version);
		if ( $vulnerability_branch[0] == $local_joomla_branch[0] ) {							
			$to_list = true;
			break;
		}
	}
	// Hemos de mostrar la información porque la vulnerabilidad es aplicable a nuestra versión de Joomla
	if ( $to_list ) {
?>
<tr>
	<td align="center">
			<?php echo $row->Product; ?>	
	</td>
	<td align="center">
			<?php echo $row->description; ?>	
	</td>		
	<td align="center">
			<?php echo $row->vuln_class; ?>	
	</td>	
	<td align="center">
			<?php echo $row->published; ?>	
	</td>
	<td align="center">
			<?php echo $row->vulnerable; ?>	
	</td>
	<td align="center">
		<?php 
			$solution_type = $row->solution_type;			
			if ( $solution_type == 'update' )
			{
				echo JText::_('COM_SECURITYCHECKPRO_SOLUTION_TYPE_' . $row->solution_type) . ' ' . $row->solution;
			}else if ( $solution_type == 'none' ){
				echo JText::_('COM_SECURITYCHECKPRO_SOLUTION_TYPE_NONE');
			}
			
		?>
	</td>
</tr>
<?php
$k = $k+1;
}
}
?>
</table>
</div>


<div class="securitycheck-bootstrap">
	<div class="alert alert-success centrado">
		<?php echo JText::_('COM_SECURITYCHECKPRO_VULNERABILITY_EXPLAIN_1'); ?>	
	</div>
</div>	

<?php
if ( !empty($this->vuln_details) ) {		
?>
<div class="margen">
	<div>
		<?php echo $this->pagination->getListFooter(); echo $this->pagination->getLimitBox(); ?>
	</div>
</div>
<?php
}
?>

<div class="clr"></div>

<div class="accordion-group">
	<div class="accordion-heading">
		<?php echo JText::_('COM_SECURITYCHECKPRO_COPYRIGHT'); ?>
	</div>
</div>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="view" value="vulninfo" />
<input type="hidden" name="controller" value="securitycheckpro" />
</form>