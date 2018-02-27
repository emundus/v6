<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var $this \Akeeba\AdminTools\Admin\View\Redirections\Html */

use Akeeba\AdminTools\Admin\Helper\Html;
use Akeeba\AdminTools\Admin\Helper\Select;
use FOF30\Utils\FEFHelper\Html as FEFHtml;

defined('_JEXEC') or die;

$js = FEFHtml::jsOrderingBackend($this->order);
$this->getContainer()->template->addJSInline($js);

$model = $this->getModel();

// Let's check if the system plugin is correctly installed AND published
echo $this->loadAnyTemplate('admin:com_admintools/ControlPanel/plugin_warning');

?>

<form name="enableForm" action="index.php" method="post" class="akeeba-form--inline">
    <div class="akeeba-form-group">
        <label for="urlredirection"><?php echo JText::_('COM_ADMINTOOLS_LBL_REDIRECTION_PREFERENCE'); ?></label>
        <?php echo \JHtml::_('FEFHelper.select.booleanswitch', 'urlredirection', $this->urlredirection) ?>
    </div>
    <button class="akeeba-btn--dark--small"><?php echo JText::_('COM_ADMINTOOLS_LBL_REDIRECTION_PREFERENCE_SAVE') ?></button>

    <input type="hidden" name="option" id="option" value="com_admintools"/>
    <input type="hidden" name="view" id="view" value="Redirections"/>
    <input type="hidden" name="task" id="task" value="applypreference"/>
</form>

<form action="index.php" method="post" name="adminForm" id="adminForm" class="akeeba-form">

    <section class="akeeba-panel--33-66 akeeba-filter-bar-container">
        <div class="akeeba-filter-bar akeeba-filter-bar--left akeeba-form-section akeeba-form--inline">
            <div class="akeeba-filter-element akeeba-form-group">
                <input type="text" name="source" placeholder="<?php echo \JText::_('COM_ADMINTOOLS_LBL_REDIRECTION_SOURCE'); ?>"
                       id="filter_source" onchange="document.adminForm.submit();"
                       value="<?php echo $this->escape($this->filters['source']); ?>"
                       title="<?php echo \JText::_('COM_ADMINTOOLS_LBL_REDIRECTION_SOURCE'); ?>"/>
            </div>

            <div class="akeeba-filter-element akeeba-form-group">
                <input type="text" name="dest" placeholder="<?php echo \JText::_('COM_ADMINTOOLS_LBL_REDIRECTION_DEST'); ?>"
                       id="filter_dest" onchange="document.adminForm.submit();"
                       value="<?php echo $this->escape($this->filters['dest']); ?>"
                       title="<?php echo \JText::_('COM_ADMINTOOLS_LBL_REDIRECTION_DEST'); ?>"/>
            </div>

            <div class="akeeba-filter-element akeeba-form-group">
                <?php echo Select::keepUrlParamsList('keepurlparams', ['onchange' => 'document.adminForm.submit()'], $this->filters['keepParams'])?>
            </div>

            <div class="akeeba-filter-element akeeba-form-group">
				<?php echo Select::published($this->filters['published'], 'published', ['onchange' => 'document.adminForm.submit()'])?>
            </div>
        </div>

		<?php echo FEFHtml::selectOrderingBackend($this->getPagination(), $this->sortFields, $this->order, $this->order_Dir)?>

    </section>

    <table class="akeeba-table akeeba-table--striped" id="itemsList">
        <thead>
            <tr>
                <th width="20px">
                    <a href="#" onclick="Joomla.tableOrdering('ordering','asc','');return false;" class="hasPopover" title="" data-content="Select to sort by this column" data-placement="top" data-original-title="Ordering"><i class="icon-menu-2"></i></a>
                </th>
                <th width="32">
                    <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/>
                </th>
                <th>
                    <?php echo \JHtml::_('grid.sort', 'COM_ADMINTOOLS_LBL_REDIRECTION_SOURCE', 'source', $this->order_Dir, $this->order, 'browse'); ?>
                </th>
                <th>
                    <?php echo \JHtml::_('grid.sort', 'COM_ADMINTOOLS_LBL_REDIRECTION_DEST', 'dest', $this->order_Dir, $this->order, 'browse'); ?>
                </th>
                <th>
                    <?php echo \JHtml::_('grid.sort', 'COM_ADMINTOOLS_REDIRECTIONS_FIELD_KEEPURLPARAMS', 'keepurlparams', $this->order_Dir, $this->order, 'browse'); ?>
                </th>
                <th>
                    <?php echo \JHtml::_('grid.sort', 'JPUBLISHED', 'published', $this->order_Dir, $this->order, 'browse'); ?>
                </th>
            </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan="11" class="center">
                <?php echo $this->pagination->getListFooter(); ?>
            </td>
        </tr>
        </tfoot>
        <tbody>
        <?php if (!count($this->items)):?>
            <tr>
                <td colspan="6">
                    <?php echo JText::_('COM_ADMINTOOLS_ERR_REDIRECTION_NOITEMS')?>
                </td>
            </tr>
        <?php endif;?>
        <?php
        if ($this->items):
                $i = 0;
                foreach($this->items as $row):

                    $source = (strstr($row->source, '://') ? $row->source : '../' . $row->source);
                    $edit   = 'index.php?option=com_admintools&view=Redirections&task=edit&id='.$row->id;
                    $keepParams = $row->keepurlparams == 0 ? 'OFF' : ($row->keepurlparams == 1 ? 'ALL' : 'ADD');
                    $enabled = $this->container->platform->getUser()->authorise('core.edit.state', 'com_admintools')
                ?>
            <tr>
                <td>
                    <?php echo Html::ordering($this, 'ordering', $row->ordering)?>
                </td>
                <td><?php echo \JHtml::_('grid.id', ++$i, $row->id); ?></td>
                <td>
                    <a href="<?php echo $row->source?>" target="_blank">
                        <?php echo htmlentities($row->source)?>
                        <span class="akion-android-open"></span>
                    </a>
                </td>
                <td>
                    <a href="<?php echo $edit?>">
                        <?php echo $row->dest?>
                    </a>
                </td>
                <td>
                    <?php echo \JText::_('COM_ADMINTOOLS_REDIRECTION_KEEPURLPARAMS_LBL_' . $keepParams)?>
                </td>
                <td>
                    <?php echo JHTML::_('jgrid.published', $row->published, $i, '', $enabled, 'cb')?>
                </td>
            </tr>
            <?php
                endforeach;
        endif; ?>
        </tbody>

    </table>

    <div class="akeeba-hidden-fields-container">
        <input type="hidden" name="option" id="option" value="com_admintools"/>
        <input type="hidden" name="view" id="view" value="Redirections"/>
        <input type="hidden" name="boxchecked" id="boxchecked" value="0"/>
        <input type="hidden" name="task" id="task" value="browse"/>
        <input type="hidden" name="filter_order" id="filter_order" value="<?php echo $this->escape($this->order); ?>"/>
        <input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="<?php echo $this->escape($this->order_Dir); ?>"/>
        <input type="hidden" name="<?php echo $this->container->platform->getToken(true); ?>" value="1"/>
    </div>
</form>
