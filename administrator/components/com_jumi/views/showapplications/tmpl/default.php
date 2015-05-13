<?php 
/**
 * Joomla! 1.5 component sexy_polling
 *
 * @version $Id: default.php 2012-04-05 14:30:25 svn $
 * @author Simon Poghosyan
 * @package Joomla
 * @subpackage sexy_polling
 * @license GNU/GPL
 *
 *
 */

defined('_JEXEC') or die('Restricted access'); 

if(JV == 'j2') {
	//j2 stuff here///////////////////////////////////////////////////////////////////////////////////////////////////////
?>
 <form action="index.php?option=com_jumi" method="post" name="adminForm" id="adminForm">

        <table>
        <tr>
            <td align="left" width="100%">
                <?php echo JText::_( 'Filter' ); ?>:
                <input type="text" name="search" id="search" value="<?php echo $this->filter->search;?>" class="text_area" onchange="document.adminForm.submit();" />
                <button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
                <button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
            </td>
            <td nowrap="nowrap">
                <?php
               echo JHTML::_('grid.state',  $this->filter->filter_state );
                ?>
            </td>
        </tr>
        </table>

        <div id="tablecell">
            <table class="adminlist">
            <thead>
                <tr>
                    <th width="1%">
                        <?php echo JText::_( 'NUM' ); ?>
                    </th>
                    <th width="2%">
                        <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
                    </th>
                    <th class="title">
                        <?php echo JHTML::_('grid.sort', 'Title', 'm.title', @$this->filter->filter_order_Dir, @$this->filter->filter_order ); ?>
                    </th>
                    <th width="30%" align="center">
                        <?php echo JHTML::_('grid.sort', 'Path', 'm.path',  @$this->filter->filter_order_Dir, @$this->filter->filter_order ); ?>
                    </th>
                    <th width="8%" align="center">
                        <?php echo JHTML::_('grid.sort', 'Published', 'm.published',  @$this->filter->filter_order_Dir, @$this->filter->filter_order ); ?>
                    </th>
                    <!--th width="12%" align="center">
                        <?php echo JHTML::_('grid.sort', 'Access', 'g.name',  @$this->filter->filter_order_Dir, @$this->filter->filter_order ); ?>
                    </th-->
                    <th width="1%" nowrap="nowrap">
                        <?php echo JHTML::_('grid.sort', 'ID', 'm.id',  @$this->filter->filter_order_Dir, @$this->filter->filter_order ); ?>
                    </th>
                </tr>
            </thead>
            <?php
            $k = 0;
            for ($i=0, $n=count( $this->items ); $i < $n; $i++) {
            	
            	
            	$row =& $this->items[$i];
            	$checked    = JHTML::_( 'grid.id', $i, $row->id );
            	$link = JRoute::_( 'index.php?option=com_jumi&controller=application&task=edit&cid[]='. $row->id );
            	$published 	= JHTML::_('grid.published', $row, $i );
            	
                ?>
                <tr class="<?php echo "row$k"; ?>">
                    <td>
                        <?php echo $this->pagination->getRowOffset( $i ); ?>
                    </td>
                    <td>
                        <?php echo $checked; ?>
                    </td>
                    <td>
                        <a href="<?php echo $link; ?>" title="<?php echo JText::_( 'Edit Application' ); ?>">
                            <?php echo $row->title; ?></a>
                    </td>
                    <td align="center">
                        <?php echo $row->path; ?>
                    </td>
                    <td align="center">
                        <?php echo $published; ?>
                    </td>
                    <!--td align="center">
                        <?php echo $accesslevel; ?>
                    </td-->
                    <td align="center">
                        <?php echo $row->id; ?>
                    </td>
                </tr>
                <?php
                $k = 1 - $k;
            }
            ?>
            <tfoot>
                <td colspan="8">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tfoot>
            </table>
        </div>

        <input type="hidden" name="task" value="" />
        <input type="hidden" name="option" value="com_jumi" />
        <input type="hidden" name="controller" value="application" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="filter_order" value="<?php echo $this->filter->filter_order; ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->filter->filter_order_Dir; ?>" />
        </form>
<table class="adminlist" style="width: 100%;margin-top: 12px;"><tr>
<td align="center" valign="middle" id="jumi_td" style="">

</td>
</tr></table>
<?php 
}
else {
	//j3 stuff here///////////////////////////////////////////////////////////////////////////////////////////////////////
	
	JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
	JHtml::_('bootstrap.tooltip');
	JHtml::_('behavior.multiselect');
	JHtml::_('dropdown.init');
	JHtml::_('formbehavior.chosen', 'select');
	
	$listOrder	= $this->escape($this->filter->filter_order);
	$listDirn	= $this->escape($this->filter->filter_order_Dir);
	$sortFields = $this->getSortFields();
?>
<script type="text/javascript">
	Joomla.orderTable = function() {
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		} else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>
 <form action="index.php?option=com_jumi" method="post" name="adminForm" id="adminForm">
      <table>
        <tr>
            <td align="left" width="100%">
            	<div id="filter-bar" class="btn-toolbar">
	                <div class="filter-search btn-group pull-left">
						<label for="search" class="element-invisible"></label>
						<input type="text" name="search" id="search" placeholder="<?php echo JText::_( 'Filter Applications by Title' ); ?>" value="<?php echo $this->filter->search;?>" title="<?php echo JText::_( 'Filter Applications by Title' ); ?>" />
					</div>
					<div class="btn-group pull-left">
						<button class="btn hasTooltip" type="submit" title="<?php echo JText::_( 'Search' ); ?>"><i class="icon-search"></i></button>
						<button class="btn hasTooltip" type="button" title="<?php echo JText::_( 'Reset' ); ?>" onclick="document.id('search').value='';this.form.submit();"><i class="icon-remove"></i></button>
					</div>
					 <div class="filter-search btn-group pull-left">
	                	<?php echo JHTML::_('grid.state',  $this->filter->filter_state );?>
					</div>
					
					
					<div class="btn-group pull-right hidden-phone">
						<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
						<?php echo $this->pagination->getLimitBox(); ?>
					</div>
					<div class="btn-group pull-right hidden-phone">
						<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC');?></label>
						<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
							<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC');?></option>
							<option value="asc" <?php if ($listDirn == 'asc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING');?></option>
							<option value="desc" <?php if ($listDirn == 'desc') echo 'selected="selected"'; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING');?></option>
						</select>
					</div>
					<div class="btn-group pull-right">
						<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY');?></label>
						<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
							<option value=""><?php echo JText::_('JGLOBAL_SORT_BY');?></option>
							<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder);?>
						</select>
					</div>
					
					
					
				</div>
            </td>
            <td nowrap="nowrap">
            </td>
        </tr>
        </table>

        <div id="tablecell">
            <table class="table table-striped" id="articleList">
            <thead>
                <tr>
                	<th width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
					</th>
					<th width="1%" class="hidden-phone">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>
                    <th class="title">
                        <?php echo JHTML::_('grid.sort', 'Title', 'm.title', @$this->filter->filter_order_Dir, @$this->filter->filter_order ); ?>
                    </th>
                    <th width="30%" align="center">
                        <?php echo JHTML::_('grid.sort', 'Path', 'm.path',  @$this->filter->filter_order_Dir, @$this->filter->filter_order ); ?>
                    </th>
                    <th width="8%" align="center">
                        <?php echo JHTML::_('grid.sort', 'Published', 'm.published',  @$this->filter->filter_order_Dir, @$this->filter->filter_order ); ?>
                    </th>
                    <!--th width="12%" align="center">
                        <?php echo JHTML::_('grid.sort', 'Access', 'g.name',  @$this->filter->filter_order_Dir, @$this->filter->filter_order ); ?>
                    </th-->
                    <th width="1%" nowrap="nowrap">
                        <?php echo JHTML::_('grid.sort', 'ID', 'm.id',  @$this->filter->filter_order_Dir, @$this->filter->filter_order ); ?>
                    </th>
                </tr>
            </thead>
            <?php
            $k = 0;
            for ($i=0, $n=count( $this->items ); $i < $n; $i++) {
            	
            	
            	$row =& $this->items[$i];
            	$checked    = JHTML::_( 'grid.id', $i, $row->id );
            	$link = JRoute::_( 'index.php?option=com_jumi&controller=application&task=edit&cid[]='. $row->id );
            	$published 	= JHtml::_('jgrid.published', $row->published, $i);
            	
                ?>
                <tr class="row<?php echo $i % 2; ?>">
                    <td>
                        <?php echo $this->pagination->getRowOffset( $i ); ?>
                    </td>
                    <td>
                        <?php echo $checked; ?>
                    </td>
                    <td>
                        <a href="<?php echo $link; ?>" title="<?php echo JText::_( 'Edit Application' ); ?>">
                            <?php echo $row->title; ?></a>
                    </td>
                    <td align="center">
                        <?php echo $row->path; ?>
                    </td>
                    <td align="center">
                        <?php echo $published; ?>
                    </td>
                    <!--td align="center">
                        <?php echo $accesslevel; ?>
                    </td-->
                    <td align="center">
                        <?php echo $row->id; ?>
                    </td>
                </tr>
                <?php
                $k = 1 - $k;
            }
            ?>
            <tfoot>
                <td colspan="8">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tfoot>
            </table>
        </div>

        <input type="hidden" name="task" value="" />
        <input type="hidden" name="option" value="com_jumi" />
        <input type="hidden" name="controller" value="application" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="filter_order" value="<?php echo $this->filter->filter_order; ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->filter->filter_order_Dir; ?>" />
</form>



<table class="adminlist" style="width: 100%;margin-top: 12px;"><tr>
<td align="center" valign="middle" id="jumi_td" style="">

</td>
</tr></table>
<?php }?>