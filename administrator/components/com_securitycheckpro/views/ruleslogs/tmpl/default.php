<?php 

/*
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access'); 

JHTML::_( 'behavior.framework', true );

// Add style declaration
$media_url = "media/com_securitycheckpro/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);

$bootstrap_css = "media/com_securitycheckpro/stylesheets/bootstrap.min.css";
JHTML::stylesheet($bootstrap_css);
?>

<div class="securitycheck-bootstrap">

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&view=ruleslogs');?>" method="post" name="adminForm" id="adminForm">

<div id="filter-bar" class="btn-toolbar">
	<div class="filter-search btn-group pull-left">
		<input type="text" name="filter_rules_search" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>" id="filter_rules_search" value="<?php echo $this->escape($this->state->get('filter.rules_search')); ?>" title="<?php echo JText::_('JSEARCH_FILTER'); ?>" />
	</div>
	<div class="btn-group pull-left">
		<button class="btn tip" type="submit" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
		<button class="btn tip" type="button" onclick="document.id('filter_rules_search').value='';this.form.submit();" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
	</div>
</div>

<div>
	<span class="badge" style="background-color: #4EC40F; padding: 10px 10px 10px 10px; float:right;"><?php echo JText::_('COM_SECURITYCHECKPRO_RULES_LOGS');?></span>
</div>

<div class="clearfix"> </div>
	<table class="table table-bordered table-hover">
	<thead>
		<tr>
			<th class="rules-logs">
				<?php echo JText::_( "Ip" ); ?>
			</th>
			<th class="rules-logs">
				<?php echo JText::_( 'COM_SECURITYCHECKPRO_USER' ); ?>
			</th>
			<th class="rules-logs">
				<?php echo JText::_( 'COM_SECURITYCHECKPRO_RULES_LOGS_LAST_ENTRY' ); ?>
			</th>
			<th class="rules-logs">
				<?php echo JText::_( 'COM_SECURITYCHECKPRO_RULES_LOGS_REASON_HEADER' ); ?>
			</th>
		</tr>
	</thead>
	<tbody>
<?php
$k = 0;
foreach ($this->log_details as &$row) {	
?>
<tr class="row<?php echo $k % 2; ?>">
	<td class="rules-logs">
			<?php echo $row->ip; ?>	
	</td>
	<td class="rules-logs">
			<?php echo $row->username; ?>	
	</td>
	<td class="rules-logs">
			<?php echo $row->last_entry; ?>	
	</td>
	<td class="rules-logs">
			<?php echo $row->reason; ?>	
	</td>
</tr>
<?php
$k = $k+1;
}
?>
	</tbody>
</table>

<?php
if ( !empty($this->log_details) ) {		
?>
<div class="margen">
	<div>
		<?php echo $this->pagination->getListFooter(); echo $this->pagination->getLimitBox(); ?>
	</div>
</div>
<?php
}
?>

</div>

<div class="clearfix"> </div>

<div id="footer">
	<p class="copyright">
		<?php echo JText::_('COM_SECURITYCHECKPRO_COPYRIGHT'); ?></td>
	</p>
</div>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="ruleslogs" />
</form>