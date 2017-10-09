<?php 

/**
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted access');
JRequest::checkToken() or die( 'Invalid Token' );

$kind_array = array(JHtml::_('select.option',JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TITLE_FILE'), JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TITLE_FILE')),
			JHtml::_('select.option',JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TITLE_FOLDER'), JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TITLE_FOLDER')));
$status_array = array(JHtml::_('select.option','0', JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TITLE_WRONG')),
			JHtml::_('select.option','1', JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TITLE_OK')),
			JHtml::_('select.option','2', JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_TITLE_EXCEPTIONS')));

// Add style declaration
$media_url = "media/com_securitycheckpro/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);

$bootstrap_css = "media/com_securitycheckpro/stylesheets/bootstrap.min.css";
JHTML::stylesheet($bootstrap_css);

$opa_icons = "media/com_securitycheckpro/stylesheets/opa-icons.css";
JHTML::stylesheet($opa_icons);

JHtml::_('formbehavior.chosen', 'select');
?>

<?php if ( ($this->files_with_incorrect_permissions > 0 ) && ( empty($this->items) ) ) { ?>
<div class="securitycheck-bootstrap">

<div class="alert alert-error">
	<?php echo JText::_('COM_SECURITYCHECKPRO_EMPTY_ITEMS'); ?>
</div>
</div>
<?php } ?>

<?php if ( $this->database_error == "DATABASE_ERROR" ) { ?>
<div class="securitycheck-bootstrap">

<div class="alert alert-error">
	<?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_DATABASE_ERROR'); ?>
</div>
</div>
<?php } ?>

<?php if ( $this->files_with_incorrect_permissions >3000 ) { ?>
<div class="securitycheck-bootstrap">

<div class="alert alert-error">
	<?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_ALERT'); ?>
</div>
</div>
<?php } ?>

<?php if ( $this->show_all == 1 ) { ?>
<div class="securitycheck-bootstrap">

<div class="alert alert-info">
	<?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_INFO'); ?>
</div>
</div>
<?php } ?>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&controller=filesstatus');?>" method="post" name="adminForm" id="adminForm">
<?php echo JHTML::_( 'form.token' ); ?>

<div id="filter-bar" class="btn-toolbar">
	<div class="filter-search btn-group pull-left">
		<input type="text" name="filter_filemanager_search" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>" id="filter_filemanager_search" value="<?php echo $this->escape($this->state->get('filter.filemanager_search')); ?>" title="<?php echo JText::_('JSEARCH_FILTER'); ?>" />
	</div>
	<div class="btn-group pull-left">
		<button class="btn tip" type="submit" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
		<button class="btn tip" type="button" onclick="document.getElementById('filter_filemanager_search').value='';this.form.submit();" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
	</div>
	
	<div class="btn-group pull-left">
			<select name="filter_filemanager_kind" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_KIND_DESCRIPTION');?></option>
				<?php echo JHtml::_('select.options', $kind_array, 'value', 'text', $this->state->get('filter.filemanager_kind'));?>
			</select>
			<select name="filter_filemanager_permissions_status" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_PERMISSIONS_STATUS_DESCRIPTION');?></option>
				<?php echo JHtml::_('select.options', $status_array, 'value', 'text', $this->state->get('filter.filemanager_permissions_status'));?>
			</select>
	</div>	
</div>


<div class="clearfix"> </div>

<div id="editcell">
<div class="accordion-group">
<table class="table table-striped">
<caption style="font-weight:bold;font-size:10pt",align="center"><?php echo JText::_( 'COM_SECURITYCHECKPRO_COLOR_CODE' ); ?></caption>
<thead>
	<tr>
		<td><span class="label label-success"> </span>
		</td>
		<td>
			<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_GREEN_COLOR' ); ?>
		</td>
		<td><span class="label label-warning"> </span>
		</td>
		<td>
			<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_YELLOW_COLOR' ); ?>
		</td>
		<td><span class="label label-important"> </span>
		</td>
		<td>
			<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_RED_COLOR' ); ?>
		</td>
	</tr>
</thead>
</table>
</div>

<?php
if ( (!empty($this->items)) && (!$this->state->get('filter.filemanager_permissions_status')) ) {		
?>
<div id="permissions_buttons" class="btn-toolbar">
	<div class="btn-group pull-right">
		<button class="btn btn-success" onclick="Joomla.submitbutton('addfile_exception')" href="#">
			<i class="iconboot-new icon-white"> </i>
			<?php echo JText::_('COM_SECURITYCHECKPRO_ADD_AS_EXCEPTION'); ?>
		</button>
	</div>
</div>
<?php
} else if ( $this->state->get('filter.filemanager_permissions_status') == 2 ) { ?>
	<div id="permissions_buttons" class="btn-toolbar">
		<div class="btn-group pull-right">
			<button class="btn btn-danger" onclick="Joomla.submitbutton('deletefile_exception')" href="#">
				<i class="icon-trash icon-white"> </i>
				<?php echo JText::_('COM_SECURITYCHECKPRO_DELETE_EXCEPTION'); ?>
			</button>
		</div>
</div>

<?php } ?>

<div>
	<span class="badge" style="background-color: #CEA0EA; padding: 10px 10px 10px 10px;"><?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_ANALYZED_FILES');?></span>
</div>

<table id="filesstatus_table" class="table table-bordered table-hover">
<thead>
	<tr>
		<th class="filesstatus-table">
			<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_NAME' ); ?>
		</th>
		<th class="filesstatus-table">
			<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_EXTENSION' ); ?>				
		</th>
		<th class="filesstatus-table">
			<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_KIND' ); ?>				
		</th>
		<th class="filesstatus-table">
			<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_RUTA' ); ?>
		</th>
		<th class="filesstatus-table">
			<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_TAMANNO' ); ?>
		</th>
		<th class="filesstatus-table">
			<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_PERMISSIONS' ); ?>
		</th>
		<th class="filesstatus-table">
			<?php echo JText::_( 'COM_SECURITYCHECKPRO_FILEMANAGER_LAST_MODIFIED' ); ?>
		</th>
		<th class="filesstatus-table">
			<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
		</th>		
	</tr>
</thead>
<?php
$k = 0;
if ( !empty($this->items) ) {	
	foreach ($this->items as &$row) {		
?>
	<td style="text-align: center;">
		<?php 
		// Obtenemos la extensión del archivo
		$last_part = explode(DIRECTORY_SEPARATOR,$row['path']);		
		$last_part_2 = explode('.',end($last_part));
		$name = reset($last_part_2);	
		echo $name; 	
		?>
	</td>
	<td style="text-align: center;">
		<?php 
		$last_part = explode(DIRECTORY_SEPARATOR,$row['path']);
		echo end($last_part);
		?>
	</td>
	<td style="text-align: center;">
		<?php echo $row['kind']; ?>
	</td>
	<td style="text-align: center;">
		<?php echo $row['path']; ?>
	</td>
	<td style="text-align: center;">
		<?php 
			if ( JFile::exists($row['path']) ) {
				echo filesize($row['path']);
			}
		?>
	</td>
	<?php 
		$safe = $row['safe'];
		if ( $safe == '0' ) {
			echo "<td style=\"text-align: center;\"><span class=\"label label-important\">";
		} else if ( $safe == '1' ) {
			echo "<td style=\"text-align: center;\"><span class=\"label label-success\">";
		} else if ( $safe == '2' ) {
			echo "<td style=\"text-align: center;\"><span class=\"label label-warning\">";
		} ?>
		<?php echo $row['permissions']; ?>
	</td>
	<td style="text-align: center;">
		<?php echo $row['last_modified']; ?>
	</td>
	<td style="text-align: center;">
		<?php echo JHtml::_('grid.id', $k, $row['path'], '', 'filesstatus_table'); ?>		
	</td>
</tr>
<?php
	$k = $k+1;
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

<?php } ?>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
</form>