<?php
/**
 * @version     1.0.0
 * @package     com_jcrm
 * @copyright   Copyright (C) 2014. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      Décision Publique <dev@emundus.fr> - http://www.emundus.fr
 */
// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHTML::_('script', 'system/multiselect.js', false, true);
// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_jcrm/assets/css/jcrm.css');

$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$canOrder = $user->authorise('core.edit.state', 'com_jcrm');
$saveOrder = $listOrder == 'a.ordering';
$input = JFactory::getApplication()->input;
jimport('joomla.form.form');
JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
$form = JForm::getInstance('com_jcrm.contact', 'contact');
?>

<form action="<?php echo JRoute::_('index.php?option=com_jcrm&view=contacts'); ?>" method="post" name="adminForm" id="adminForm">
    <fieldset id="filter-bar">
        <div class="filter-search fltlft">
            <label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
            <input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('Search'); ?>" />
            <button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
            <button type="button" onclick="document.id('filter_search').value = '';
                    this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
        </div>

        
		<div class="filter-select fltrt">
			<select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), "value", "text", $this->state->get('filter.state'), true);?>
			</select>
		</div>


    </fieldset>
    <div class="clr"> </div>

    <table class="adminlist">
        <thead>
            <tr>
                <th width="1%">
                    <input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
                </th>
                
				<th class="left">
					<?php echo JHtml::_('grid.sort',  'COM_JCRM_CONTACTS_LAST_NAME', 'a.last_name', $listDirn, $listOrder); ?>
				</th>

				<th class="left">
					<?php echo JHtml::_('grid.sort',  'COM_JCRM_CONTACTS_FIRST_NAME', 'a.first_name', $listDirn, $listOrder); ?>
				</th>

				<th class="left">
					<?php echo JHtml::_('grid.sort',  'COM_JCRM_CONTACTS_ORGANISATION', 'a.organisation', $listDirn, $listOrder); ?>
				</th>

				<th class="left">
					<?php echo JHtml::_('grid.sort',  'COM_JCRM_CONTACTS_EMAIL', 'a.email', $listDirn, $listOrder); ?>
				</th>

				<th class="left">
					<?php echo JHtml::_('grid.sort',  'COM_JCRM_CONTACTS_PHONE', 'a.phone', $listDirn, $listOrder); ?>
				</th>

				<th class="left">
					<?php echo JHtml::_('grid.sort',  'COM_JCRM_CONTACTS_JCARD', 'a.jcard', $listDirn, $listOrder); ?>
				</th>

                <?php if (isset($this->items[0]->state)) : ?>
                    <th width="5%">
                        <?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'a.state', $listDirn, $listOrder); ?>
                    </th>
                <?php endif; ?>

                <?php if (isset($this->items[0]->ordering)) : ?>
                    <th width="10%">
                        <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ORDERING', 'a.ordering', $listDirn, $listOrder); ?>
                        <?php if ($canOrder && $saveOrder) : ?>
                            <?php echo JHtml::_('grid.order', $this->items, 'filesave.png', 'contacts.saveorder'); ?>
                        <?php endif; ?>
                    </th>
                <?php endif; ?>

                <?php if (isset($this->items[0]->id)) : ?>
                    <th width="1%" class="nowrap">
                        <?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                    </th>
                <?php endif; ?>
            </tr>
        </thead>
        <tfoot>
            <?php
            if (isset($this->items[0])) {
                $colspan = count(get_object_vars($this->items[0]));
            } else {
                $colspan = 10;
            }
            ?>
            <tr>
                <td colspan="<?php echo $colspan ?>">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
        </tfoot>
        <tbody>
            <?php
            foreach ($this->items as $i => $item) :
                $ordering = ($listOrder == 'a.ordering');
                $canCreate = $user->authorise('core.create', 'com_jcrm');
                $canEdit = $user->authorise('core.edit', 'com_jcrm');
                $canCheckin = $user->authorise('core.manage', 'com_jcrm');
                $canChange = $user->authorise('core.edit.state', 'com_jcrm');
                ?>
                <tr class="row<?php echo $i % 2; ?>">
                    <td class="center">
                        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                    </td>
                    
					<td>
					<?php if (isset($item->checked_out) && $item->checked_out) : ?>
						<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_a, 'contacts.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit): ?>
						<a href="<?php echo JRoute::_('index.php?option=com_jcrm&task=contact.edit&id=' . (int) $item->id); ?>">

							<?php echo $this->escape($item->last_name); ?>
						</a>
					<?php else: ?>
						<?php echo $this->escape($item->last_name); ?>
					<?php endif; ?>
					</td>

					<td>
						<?php echo $item->first_name; ?>
					</td>

					<td>
						<?php echo $item->organisation; ?>
					</td>

					<td>
						<?php echo $item->email; ?>
					</td>

					<td>
						<?php echo $item->phone; ?>
					</td>

					<td>
						<?php echo $item->jcard; ?>
					</td>

                    <?php if (isset($this->items[0]->state)) { ?>
                        <td class="center">
                            <?php echo JHtml::_('jgrid.published', $item->state, $i, 'contacts.', $canChange, 'cb'); ?>
                        </td>
                    <?php } ?>

                    <?php if (isset($this->items[0]->ordering)) { ?>
                        <td class="order">
                            <?php if ($canChange) : ?>
                                <?php if ($saveOrder) : ?>
                                    <?php if ($listDirn == 'asc') : ?>
                                        <span><?php echo $this->pagination->orderUpIcon($i, true, 'contacts.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
                                        <span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'contacts.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
                                    <?php elseif ($listDirn == 'desc') : ?>
                                        <span><?php echo $this->pagination->orderUpIcon($i, true, 'contacts.orderdown', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
                                        <span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'contacts.orderup', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php $disabled = $saveOrder ? '' : 'disabled="disabled"'; ?>
                                <input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>" <?php echo $disabled ?> class="text-area-order" />
                            <?php else : ?>
                                <?php echo $item->ordering; ?>
                            <?php endif; ?>
                        </td>
                    <?php } ?>

                    <?php if (isset($this->items[0]->id)) { ?>
                        <td class="center">
                            <?php echo (int) $item->id; ?>
                        </td>
                    <?php } ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>