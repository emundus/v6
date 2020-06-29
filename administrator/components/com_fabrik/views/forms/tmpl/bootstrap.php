<?php
/**
 * Admin Forms List Tmpl
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.0
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHTML::_('script', 'system/multiselect.js', false, true);

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
?>
<form action="<?php echo JRoute::_('index.php?option=com_fabrik&view=forms'); ?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php else : ?>
		<div id="j-main-container">
			<?php endif; ?>
			<div id="filter-bar" class="btn-toolbar">
				<div class="row-fluid">
					<div class="filter-search btn-group pull-left">
						<label class="element-invisible" for="filter_search"><?php echo FText::_('JSEARCH_FILTER_LABEL'); ?></label>
						<input type="text" name="filter_search" placeholder="<?php echo FText::_('JSEARCH_FILTER_LABEL'); ?>" id="filter_search" value="<?php echo $this->state->get('filter.search'); ?>"
							title="<?php echo FText::_('COM_FABRIK_SEARCH_IN_TITLE'); ?>" />&nbsp;
					</div>
					<div class="btn-group pull-left hidden-phone">
						<button class="btn tip" type="submit" rel="tooltip" title="<?php echo FText::_('JSEARCH_FILTER_SUBMIT'); ?>">
							<i class="icon-search"></i></button>
						<button class="btn tip" type="button" onclick="document.id('filter_search').value='';this.form.submit();" rel="tooltip" title="<?php echo FText::_('JSEARCH_FILTER_CLEAR'); ?>">
							<i class="icon-remove"></i></button>
					</div>
					<div class="btn-group pull-right hidden-phone">
						<label for="limit" class="element-invisible"><?php echo FText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
						<?php echo $this->pagination->getLimitBox(); ?>
					</div>
				</div>
			</div>
			<div class="clearfix"></div>
			<table class="table table-striped">
				<thead>
				<tr>
					<th width="2%">
						<?php echo JHTML::_('grid.sort', '#', 'f.id', $listDirn, $listOrder); ?>
					</th>
					<th width="1%">
						<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
					</th>
					<th width="35%">
						<?php echo JHTML::_('grid.sort', 'COM_FABRIK_LABEL', 'f.label', $listDirn, $listOrder); ?>
					</th>
					<th width="10%">
						<?php echo FText::_('COM_FABRIK_ELEMENT'); ?>
					</th>
					<th width="5%">
						<?php echo FText::_('COM_FABRIK_CONTENT_TYPE'); ?>
					</th>
					<th width="10%">
						<?php echo FText::_('COM_FABRIK_UPDATE_DATABASE'); ?>
					</th>
					<th width="12%">
						<?php echo FText::_('COM_FABRIK_VIEW_DATA'); ?>
					</th>
					<th width="5%">
						<?php echo JHTML::_('grid.sort', 'JPUBLISHED', 'f.published', $listDirn, $listOrder); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<td colspan="6">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
				</tfoot>
				<tbody>
				<?php foreach ($this->items as $i => $item) :
					$ordering    = ($listOrder == 'ordering');
					$link       = JRoute::_('index.php?option=com_fabrik&task=form.edit&id=' . (int) $item->id);
					$canCreate  = $user->authorise('core.create', 'com_fabrik.form.1');
					$canEdit    = $user->authorise('core.edit', 'com_fabrik.form.1');
					$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
					$canChange  = $user->authorise('core.edit.state', 'com_fabrik.form.1') && $canCheckin;
					$params     = new JRegistry($item->params);

					$elementLink = JRoute::_('index.php?option=com_fabrik&task=element.edit&id=0&filter_groupId=' . $item->group_id);
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td><?php echo $item->id; ?></td>
						<td><?php echo JHtml::_('grid.id', $i, $item->id); ?></td>
						<td>
							<?php if ($item->checked_out) : ?>
								<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'forms.', $canCheckin); ?>
							<?php endif; ?>
							<?php
							if ($item->checked_out && ($item->checked_out != $user->get('id')))
							{
								echo FText::_($item->label);
							}
							else
							{
								?>
								<a href="<?php echo $link; ?>">
									<?php echo FText::_($item->label); ?>
								</a>
							<?php } ?>
						</td>
						<td>
							<a href="<?php echo $elementLink ?>">
								<i class="icon-plus"></i> <?php echo FText::_('COM_FABRIK_ADD'); ?>
							</a>
						</td>
						<td>
							<div class="btn-group">
								<
								<button data-toggle="dropdown" class="dropdown-toggle btn btn-micro">
									<span class="caret"></span>
									<span class="element-invisible">Actions for: COM_FABRIK_EXPORT_CONTENT_TYPE</span>
								</button>
								<ul class="dropdown-menu">
									<li>
										<a href="javascript://" onclick="listItemTask('cb<?php echo $i; ?>', 'form.createContentType')">
											<span class="icon-upload"></span> <?php echo JText::_('COM_FABRIK_CONTENT_TYPE_EXPORT'); ?>
										</a>
									</li>
									<?php
									if ($params->get('content_type_path', '') !== '') :?>
										<li>
											<a href="index.php?option=com_fabrik&task=form.downloadContentType&cid=<?php echo $item->id; ?>">
												<span class="icon-download"></span> <?php echo JText::_('COM_FABRIK_CONTENT_TYPE_DOWNLOAD'); ?>
											</a>
										</li>
										<?php

									endif;
									?>
								</ul>
							</div>
						</td>
						<td>
							<a href="#edit" onclick="return listItemTask('cb<?php echo $i; ?>','forms.updateDatabase')">
								<i class="icon-refresh"></i> <?php echo FText::_('COM_FABRIK_UPDATE_DATABASE'); ?>
							</a>
						</td>
						<td>
							<a href="index.php?option=com_fabrik&task=list.view&listid=<?php echo $item->list_id ?>">
								<i class="icon-list-view"></i> <?php echo FText::_('COM_FABRIK_VIEW_DATA'); ?>
							</a>
						</td>
						<td class="center">
							<?php echo JHtml::_('jgrid.published', $item->published, $i, 'forms.', $canChange); ?>
						</td>
					</tr>

				<?php endforeach; ?>
				</tbody>
			</table>

			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
</form>
