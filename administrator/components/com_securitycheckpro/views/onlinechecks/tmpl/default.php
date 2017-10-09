<?php 

/*
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access'); 
JRequest::checkToken( 'get' ) or die( 'Invalid Token' );

JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
JHTML::_( 'behavior.framework', true );

// Add style declaration
$media_url = "media/com_securitycheckpro/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);

$bootstrap_css = "media/com_securitycheckpro/stylesheets/bootstrap.min.css";
JHTML::stylesheet($bootstrap_css);
?>

<div class="securitycheck-bootstrap">

<div class="alert alert-warn">
	<?php echo JText::_('COM_SECURITYCHECKPRO_PROFESSIONAL_HELP'); ?>
	<p>
		<a href="http://securitycheck.protegetuordenador.com/index.php/contact-us" target="_blank" class="btn btn-primary btn-success btn-large">
			<?php echo JText::_('COM_SECURITYCHECKPRO_CONTACT_US'); ?>
		</a>
	</p>
</div>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheckpro&view=onlinechecks&'. JSession::getFormToken() .'=1');?>" method="post" name="adminForm" id="adminForm">

<div id="filter-bar" class="btn-toolbar">
	<div class="filter-search btn-group pull-left">
		<input type="text" name="filter_onlinechecks_search" placeholder="<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>" id="filter_onlinechecks_search" value="<?php echo $this->escape($this->state->get('filter.onlinechecks_search')); ?>" title="<?php echo JText::_('JSEARCH_FILTER'); ?>" />
	</div>
	<div class="btn-group pull-left">
		<button class="btn tip" type="submit" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
		<button class="btn tip" type="button" onclick="document.id('filter_onlinechecks_search').value='';this.form.submit();" rel="tooltip" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
	</div>
</div>

<div class="clearfix"> </div>

<div>
	<span class="badge" style="background-color: #19AAFF; padding: 10px 10px 10px 10px; float:right;"><?php echo JText::_('COM_SECURITYCHECKPRO_ONLINE_CHECK_LOGS');?></span>
</div>
	
	<table id="onlinechecks_logs_table" class="table table-bordered table-hover">
		<thead>
			<tr>
				<th class="onlinelogs-table center"><?php echo JText::_('COM_SECURITYCHECKPRO_FILEMANAGER_FILES_SCANNED'); ?></th>
				<th class="onlinelogs-table center"><?php echo JText::_('COM_SECURITYCHECKPRO_THREATS_FOUND'); ?></th>
				<th class="onlinelogs-table center"><?php echo JText::_('COM_SECURITYCHECKPRO_INFECTED_FILES'); ?></th>
				<th class="onlinelogs-table center"><?php echo JText::_('COM_SECURITYCHECKPRO_CREATION_DATE'); ?></th>
				<th class="onlinelogs-table center"></th>
				<th class="onlinelogs-table center">
					<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
				</th>
			</tr>
		</thead>   
		<tbody>
			<?php
			if ( count($this->items)>0 ) {
				$k = 0;
				foreach ($this->items as &$row) { 				
			?>
			<tr>
				<td class="center">
				<?php
					$span = "<span class=\"badge badge-inverse\">";
					echo $span . $row[2]; ?>
					</span>					
				</td>
				<td class="center">
					<?php 
						if ( $row[3] == 0 ) {
							$span = "<span class=\"badge badge-success\">";
							echo $span . $row[3];
						} else  {
							$span = "<span class=\"badge badge-important\">";
							echo $span . $row[3];
						}
					?>
					</span>					
				</td>
				<td class="center">
				<?php
				if ( empty($row[5]) ) {
					$span = "<span class=\"badge badge-success\">";
					echo $span . JText::_('COM_SECURITYCHECKPRO_NONE') . "</span>";
				} else {
					// Decodificamos los nombres, que vendrán en formato json
					$infected_files = json_decode($row[5],true);
					// Contamos los elementos, puesto que vamos a mostrar sólo 3 nombres en la tabla por motivos de claridad.
					$elements = count($infected_files);
					$cont = 0;
					while ( ($cont <=2) && ($cont < $elements) ) {
						$span = "<span class=\"badge badge-warning\">";
						echo $span . $infected_files[$cont] . "</span><br/>";
						$cont++;
					}
					// Si hay más elementos, lo indicamos
					if ( $cont < $elements ) {
						$span = "<span class=\"badge\">";
						echo $span . JText::sprintf('COM_SECURITYCHECKPRO_MORE_FILES',$elements - $cont) . "</span><br/>";
					}					
				}				
				?></td>				
				<td class="center" style="font-size:14px"><?php echo $row[4]; ?></td>
				</td>
				<td class="center">
					<?php
						$ref1 = "<a href=\"index.php?option=com_securitycheckpro&controller=logview&view=logview&name=";
						$ref2 = "&tmpl=component\" class=\"modal\" rel=\"{handler: 'iframe', size: {x: 600, y: 250}}\">";
						echo $ref1 . $row[1] . $ref2;
					?>				
						<button class="btn btn-primary">
							<?php echo JText::_('COM_SECURITYCHECKPRO_REPAIR_VIEW_LOG_MESSAGE'); ?>
						</button>
					</a>					
				</td>
				<td class="center">
					<?php echo JHtml::_('grid.id', $k, $row[1], '', 'onlinechecks_logs_table'); ?>
				</td>
			</tr>
			<?php 
				$k++;
				} 
			}	?>
		</tbody>
	</table>
</div>

<?php
if ( !empty($this->items) ) {		
?>
<div class="margen">
<tfoot>
	<tr>
		<td colspan="9"><?php echo $this->pagination->getListFooter(); echo $this->pagination->getLimitBox(); ?>
		</td>
	</tr>
</tfoot>
</div>
<?php
}
?>

<input type="hidden" name="option" value="com_securitycheckpro" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="onlinechecks" />
</form>