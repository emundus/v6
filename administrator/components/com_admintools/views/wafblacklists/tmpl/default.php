<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

$this->loadHelper('select');

$model = $this->getModel();

JLoader::import('joomla.filesystem.file');
$pEnabled = JPluginHelper::getPlugin('system', 'admintools');
$pExists = JFile::exists(JPATH_ROOT . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'admintools' . DIRECTORY_SEPARATOR . 'admintools.php');
$pExists |= JFile::exists(JPATH_ROOT . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'admintools.php');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');

$sortFields = array(
	'id'     => JText::_('JGRID_HEADING_ID'),
	'verb'   => JText::_('ATOOLS_LBL_WAFBLACKLISTS_VERB'),
	'option' => JText::_('ATOOLS_LBL_WAFEXCEPTIONS_OPTION'),
	'view'   => JText::_('ATOOLS_LBL_WAFEXCEPTIONS_VIEW'),
	'task'   => JText::_('ATOOLS_LBL_WAFBLACKLISTS_TASK'),
	'query'  => JText::_('ATOOLS_LBL_WAFEXCEPTIONS_QUERY'),
	'query_content'  => JText::_('ATOOLS_LBL_WAFBLACKLISTS_QUERY_CONTENT'),
);

?>
<?php if (!$pExists): ?>
	<p class="alert alert-error">
		<a class="close" data-dismiss="alert" href="#">×</a>
		<?php echo JText::_('ATOOLS_ERR_WAF_NOPLUGINEXISTS'); ?>
	</p>
<?php elseif (!$pEnabled): ?>
	<p class="alert alert-error">
		<a class="close" data-dismiss="alert" href="#">×</a>
		<?php echo JText::_('ATOOLS_ERR_WAF_NOPLUGINACTIVE'); ?>
		<br/>
		<a href="index.php?option=com_plugins&client=site&filter_type=system&search=admin%20tools">
			<?php echo JText::_('ATOOLS_ERR_WAF_NOPLUGINACTIVE_DOIT'); ?>
		</a>
	</p>
<?php endif; ?>

<script type="text/javascript">
	Joomla.orderTable = function ()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '$order')
		{
			dirn = 'asc';
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn);
	}
</script>

<form name="adminForm" id="adminForm" action="index.php" method="post">
	<input type="hidden" name="option" id="option" value="com_admintools"/>
	<input type="hidden" name="view" id="view" value="wafblacklists"/>
	<input type="hidden" name="task" id="task" value="browse"/>
	<input type="hidden" name="boxchecked" id="boxchecked" value="0"/>
	<input type="hidden" name="hidemainmenu" id="hidemainmenu" value="0"/>
	<input type="hidden" name="filter_order" id="filter_order" value="<?php echo $this->lists->order ?>"/>
	<input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="<?php echo $this->lists->order_Dir ?>"/>
	<input type="hidden" name="<?php echo JFactory::getSession()->getFormToken(); ?>" value="1"/>

	<div id="filter-bar" class="btn-toolbar">
		<div class="btn-group pull-right hidden-phone">
			<label for="limit"
				   class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC') ?></label>
			<?php echo $this->getModel()->getPagination()->getLimitBox(); ?>
		</div>
		<?php
		$asc_sel = ($this->getLists()->order_Dir == 'asc') ? 'selected="selected"' : '';
		$desc_sel = ($this->getLists()->order_Dir == 'desc') ? 'selected="selected"' : '';
		?>
		<div class="btn-group pull-right hidden-phone">
			<label for="directionTable"
				   class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC') ?></label>
			<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
				<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC') ?></option>
				<option
					value="asc" <?php echo $asc_sel ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING') ?></option>
				<option
					value="desc" <?php echo $desc_sel ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING') ?></option>
			</select>
		</div>
		<div class="btn-group pull-right">
			<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY') ?></label>
			<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
				<option value=""><?php echo JText::_('JGLOBAL_SORT_BY') ?></option>
				<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $this->getLists()->order) ?>
			</select>
		</div>
	</div>
	<div class="clearfix"></div>

	<table class="table table-striped">
		<thead>
		<tr>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/>
			</th>
            <th>
                <?php echo JHTML::_('grid.sort', 'ATOOLS_LBL_WAFBLACKLISTS_VERB', 'verb', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
            </th>
			<th>
				<?php echo JHTML::_('grid.sort', 'ATOOLS_LBL_WAFEXCEPTIONS_OPTION', 'option', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
			</th>
			<th>
				<?php echo JHTML::_('grid.sort', 'ATOOLS_LBL_WAFEXCEPTIONS_VIEW', 'view', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
			</th>
            <th>
                <?php echo JHTML::_('grid.sort', 'ATOOLS_LBL_WAFBLACKLISTS_TASK', 'task', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
            </th>
			<th>
				<?php echo JHTML::_('grid.sort', 'ATOOLS_LBL_WAFEXCEPTIONS_QUERY', 'query', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
			</th>
            <th>
                <?php echo JHTML::_('grid.sort', 'ATOOLS_LBL_WAFBLACKLISTS_QUERY_CONTENT', 'query_content', $this->lists->order_Dir, $this->lists->order, 'browse'); ?>
            </th>
            <th style="width: 130px"></th>
		</tr>
		<tr>
			<td></td>
            <td>
                <?php echo AdmintoolsHelperSelect::httpVerbs('fverb', array('class' => 'input-small'), $model->getState('fverb', ''))?>
            </td>
			<td class="form-inline">
				<input type="text" name="foption" id="foption"
					   value="<?php echo $this->escape($model->getState('foption', '')); ?>" size="30"
					   class="input-small" onchange="document.adminForm.submit();"
					   placeholder="<?php echo JText::_('ATOOLS_LBL_WAFEXCEPTIONS_OPTION') ?>"/>
			</td>
			<td class="form-inline">
				<input type="text" name="fview" id="fview"
					   value="<?php echo $this->escape($model->getState('fview', '')); ?>" size="30"
					   class="input-small" onchange="document.adminForm.submit();"
					   placeholder="<?php echo JText::_('ATOOLS_LBL_WAFEXCEPTIONS_VIEW') ?>"/>
			</td>
            <td class="form-inline">
                <input type="text" name="ftask" id="ftask"
                       value="<?php echo $this->escape($model->getState('ftask', '')); ?>" size="30"
                       class="input-small" onchange="document.adminForm.submit();"
                       placeholder="<?php echo JText::_('ATOOLS_LBL_WAFBLACKLISTS_TASK') ?>"/>
            </td>
			<td class="form-inline">
				<input type="text" name="fquery" id="fquery"
					   value="<?php echo $this->escape($model->getState('fquery', '')); ?>" size="30"
					   class="input-small" onchange="document.adminForm.submit();"
					   placeholder="<?php echo JText::_('ATOOLS_LBL_WAFEXCEPTIONS_QUERY') ?>"/>
			</td>
            <td>
                <input type="text" name="fquery_content" id="fquery_content"
                       value="<?php echo $this->escape($model->getState('fquery_content', '')); ?>" size="30"
                       class="input-small" onchange="document.adminForm.submit();"
                       placeholder="<?php echo JText::_('ATOOLS_LBL_WAFBLACKLISTS_QUERY_CONTENT') ?>"/>
            </td>
            <td>
                <button class="btn" onclick="this.form.submit();">
                    <?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>
                </button>
                <button class="btn" onclick="document.adminForm.fview.value='';this.form.submit();">
                    <?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
                </button>
            </td>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="8">
				<?php if ($this->pagination->total > 0): ?>
					<?php echo $this->pagination->getListFooter(); ?>
				<?php endif; ?>
			</td>
		</tr>
		</tfoot>
		<tbody>
		<?php if ($count = count($this->items)): ?>
			<?php
			$i = 0;

			foreach ($this->items as $item):
				?>
				<tr>
					<td>
						<?php echo JHTML::_('grid.id', $i, $item->id, false); ?>
					</td>
                    <td>
                        <a href="index.php?option=com_admintools&view=wafblacklists&task=edit&id=<?php echo $item->id ?>">
                            <?php echo $item->verb ? $this->escape($item->verb) : JText::_('ATOOLS_LBL_WAFBLACKLISTS_ALL'); ?>
                        </a>
                    </td>
					<td>
						<a href="index.php?option=com_admintools&view=wafblacklists&task=edit&id=<?php echo $item->id ?>">
							<?php echo $item->option ? $this->escape($item->option) : JText::_('ATOOLS_LBL_WAFBLACKLISTS_ALL'); ?>
						</a>
					</td>
					<td>
						<a href="index.php?option=com_admintools&view=wafblacklists&task=edit&id=<?php echo $item->id ?>">
							<?php echo $item->view ? $this->escape($item->view) : JText::_('ATOOLS_LBL_WAFBLACKLISTS_ALL'); ?>
						</a>
					</td>
                    <td>
                        <a href="index.php?option=com_admintools&view=wafblacklists&task=edit&id=<?php echo $item->id ?>">
                            <?php echo $item->task ? $this->escape($item->task) : JText::_('ATOOLS_LBL_WAFBLACKLISTS_ALL'); ?>
                        </a>
                    </td>
					<td>
						<a href="index.php?option=com_admintools&view=wafblacklists&task=edit&id=<?php echo $item->id ?>">
							<?php echo $item->query ? $this->escape($item->query) : JText::_('ATOOLS_LBL_WAFBLACKLISTS_ALL'); ?>
						</a>
					</td>
                    <td colspan="2">
                        <a href="index.php?option=com_admintools&view=wafblacklists&task=edit&id=<?php echo $item->id ?>">
                            <?php echo $item->query_content ? $this->escape($item->query_content) : JText::_('ATOOLS_LBL_WAFBLACKLISTS_ALL'); ?>
                        </a>
                    </td>
				</tr>
				<?php
				$i++;
			endforeach;
			?>
		<?php else : ?>
			<tr>
				<td colspan="8" align="center"><?php echo JText::_('ATOOLS_LBL_WAFEXCEPTIONS_NOITEMS') ?></td>
			</tr>
		<?php endif ?>
		</tbody>
	</table>

</form>