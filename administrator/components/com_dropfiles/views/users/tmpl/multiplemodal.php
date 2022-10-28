<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') || die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.multiselect');

$input = JFactory::getApplication()->input;
$field = $input->getCmd('field');
$selectedStr = $input->getString('selected');
$selected = explode(',', base64_decode($selectedStr));

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>
<form action="
<?php echo JRoute::_('index.php?option=com_dropfiles&view=users&layout=multiplemodal&tmpl=component&groups='
                     . $input->get('groups', '', 'BASE64') . '&excluded=' . $input->get('excluded', '', 'BASE64') . '&selected=' . $selectedStr); ?>
" method="post" name="adminForm" id="adminForm">
    <fieldset class="filter">
        <div id="filter-bar" class="btn-toolbar">
            <div class="filter-search btn-group pull-left">
                <label for="filter_search" class="element-invisible"><?php echo JText::_('JSEARCH_FILTER'); ?></label>
                <input type="text" name="filter_search" id="filter_search"
                       placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>"
                       value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="hasTooltip"
                       title="<?php echo JHtml::tooltipText('COM_DROPFILES_SEARCH_IN_NAME'); ?>"
                       data-placement="bottom"/>
            </div>
            <div class="btn-group pull-left">
                <button type="submit" class="btn hasTooltip"
                        title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>" data-placement="bottom"><span
                        class="icon-search"></span></button>
                <button type="button" class="btn hasTooltip"
                        title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" data-placement="bottom"
                        onclick="document.getElementById('filter_search').value='';this.form.submit();"><span
                        class="icon-remove"></span></button>
                <?php if ((int) $input->get('required', 0, 'int') !== 1) : ?>
                    <button type="button" class="btn button-select" data-user-value="0"
                            data-user-name="<?php echo $this->escape(JText::_('JLIB_FORM_SELECT_USER')); ?>"
                            data-user-field="
                            <?php echo $this->escape($field); ?>">
                        <?php echo JText::_('COM_DROPFILES_JOPTION_NO_USER'); ?>
                    </button>
                <?php endif; ?>
            </div>
            <div class="btn-group pull-right hidden-phone">
                <label for="filter_group_id"
                       class="element-invisible"><?php echo JText::_('COM_DROPFILES_FILTER_USER_GROUP'); ?></label>
                <?php echo JHtml::_(
                    'access.usergroup',
                    'filter_group_id',
                    $this->state->get('filter.group_id'),
                    'onchange="this.form.submit()"'
                ); ?>
            </div>
        </div>
    </fieldset>
    <?php if (empty($this->items)) : ?>
        <div class="alert alert-no-items">
            <?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
        </div>
    <?php else : ?>
        <table class="table table-striped table-condensed widefat">
            <thead>
            <tr>
                <th style="width: 15px"></th>
                <th class="left manage-colum">
                    <?php echo JHtml::_('grid.sort', 'COM_DROPFILES_HEADING_NAME', 'a.name', $listDirn, $listOrder); ?>
                </th>
                <th class="nowrap manage-colum" width="25%">
                    <?php echo JHtml::_('grid.sort', 'JGLOBAL_USERNAME', 'a.username', $listDirn, $listOrder); ?>
                </th>
                <th class="nowrap manage-colum" width="25%">
                    <?php echo JText::_('COM_DROPFILES_HEADING_GROUPS'); ?>
                </th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <td colspan="15" class="user-pagination">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
            </tfoot>
            <tbody>
            <?php
            $i = 0;

            foreach ($this->items as $item) : ?>
                <tr class="row<?php echo $i % 2; ?>">
                    <?php $checked = in_array((string) $item->id, $selected) ? 'checked="checked"' : ''; ?>
                    <td class="column-check"><input class="user-select"
                               data-user-value="<?php echo $item->id; ?>"
                               data-user-name="<?php echo $this->escape($item->name); ?>"
                               data-user-field="<?php echo $this->escape($field); ?>"
                               type="checkbox" onchange="jSelectMultipleUser(this)"
                            <?php echo $checked; ?>
                        /></td>
                    <td class="column-name">
                        <?php echo $item->name; ?>
                    </td>
                    <td align="center" class="column-username">
                        <?php echo $item->username; ?>
                    </td>
                    <td align="left" class="column-role">
                        <?php echo nl2br($item->group_names); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <div>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="field" value="<?php echo $this->escape($field); ?>"/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
<script>
    function jSelectMultipleUser(e) {

        if (jQuery(e).is(':checked')) {
            window.parent.multipleUser.setValue(jQuery(e).data('user-value'), jQuery(e).data('user-name'));
        } else {
            window.parent.multipleUser.unsetValue(jQuery(e).data('user-value'), jQuery(e).data('user-name'));
        }
    }
</script>