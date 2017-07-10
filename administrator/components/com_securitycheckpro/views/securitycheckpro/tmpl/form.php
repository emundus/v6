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
	<span class="badge" style="background-color: #A8907A; padding: 10px 10px 10px 10px; float:right;"><?php echo JText::_( 'COM_SECURITYCHECKPRO_VULNERABILITY_HEAD' );?></span>
</div>

	<table class="table table-bordered table-hover">
		<thead>
		<tr>
			<th class="vulnerabilities-info" align="center">
				<?php echo JText::_( 'COM_SECURITYCHECKPRO_VULNERABILITY_DETAILS' ); ?>
			</th>
			<th class="vulnerabilities-info" align="center">
				<?php echo JText::_( 'COM_SECURITYCHECKPRO_VULNERABILITY_CLASS' ); ?>
			</th>
			<th class="vulnerabilities-info" align="center">
				<?php echo JText::_( 'COM_SECURITYCHECKPRO_VULNERABILITY_PUBLISHED' ); ?>
			</th>
			<th class="vulnerabilities-info" align="center">
				<?php echo JText::_( 'COM_SECURITYCHECKPRO_VULNERABILITY_VULNERABLE' ); ?>
			</th>
			<th class="vulnerabilities-info" align="center">
				<?php echo JText::_( 'COM_SECURITYCHECKPRO_VULNERABILITY_SOLUTION' ); ?>
			</th>
		</tr>
	</thead>	
<?php
$k = 0;
foreach ($this->vuln_details as &$row) {
?>
<tr>
	<td align="center">
			<?php $description_sanitized = filter_var($this->vuln_details[$k]['description'], FILTER_SANITIZE_STRING);
			echo $description_sanitized; ?>	
	</td>
	<td align="center">
			<?php $class_sanitized = filter_var($this->vuln_details[$k]['vuln_class'], FILTER_SANITIZE_STRING);
			echo $class_sanitized; ?>
	</td>
	<td align="center">
		<?php $published_sanitized = filter_var($this->vuln_details[$k]['published'], FILTER_SANITIZE_STRING);
			echo $published_sanitized; ?>		
	</td>
	<td align="center">
		<?php $vulnerable_sanitized = filter_var($this->vuln_details[$k]['vulnerable'], FILTER_SANITIZE_STRING);
			echo $vulnerable_sanitized; ?>
	</td>
	<td align="center">
		<?php 
			$solution_type = filter_var($this->vuln_details[$k]['solution_type'], FILTER_SANITIZE_STRING);
			$solution = filter_var($this->vuln_details[$k]['solution'], FILTER_SANITIZE_STRING);
			if ( $solution_type == 'update' )
			{
				echo JText::_('COM_SECURITYCHECKPRO_SOLUTION_TYPE_UPDATE') . ' ' . $solution;				
			} else if ( $solution_type == 'none' ){
				echo JText::_('COM_SECURITYCHECKPRO_SOLUTION_TYPE_NONE');
			}		
		?>
	</td>	
</tr>
<?php
$k = $k+1;
}
?>
</table>
</div>

<div class="securitycheck-bootstrap">
	<div class="alert alert-success centrado">
		<?php echo JText::_('COM_SECURITYCHECKPRO_VULNERABILITY_EXPLAIN_1'); ?>	
	</div>
</div>

<div class="clr"></div>

<div class="accordion-group">
	<div class="accordion-heading">
		<?php echo JText::_('COM_SECURITYCHECKPRO_COPYRIGHT'); ?>
	</div>
</div>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="securitycheckpro" />
</form>