<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var $this \Akeeba\AdminTools\Admin\View\TempSuperUsers\Html */

use FOF30\Utils\FEFHelper\Html as FEFHtml;

defined('_JEXEC') or die;

// Let's check if the system plugin is correctly installed AND published
//echo $this->loadAnyTemplate('admin:com_admintools/ControlPanel/plugin_warning');

?>
<form action="index.php" method="post" name="adminForm" id="adminForm" class="akeeba-form">

    <section class="akeeba-panel--33-66 akeeba-filter-bar-container">
        <div class="akeeba-filter-bar akeeba-filter-bar--left akeeba-form-section akeeba-form--inline">
            <div class="akeeba-filter-element akeeba-form-group">
                <input type="text" name="username"
                       placeholder="<?php echo \JText::_('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_USERNAME'); ?>"
                       id="filter_username" onchange="document.adminForm.submit();"
                       value="<?php echo $this->escape($this->filters['username']); ?>"
                       title="<?php echo \JText::_('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_USERNAME'); ?>"/>
            </div>
        </div>

		<?php echo FEFHtml::selectOrderingBackend($this->getPagination(), $this->sortFields, $this->order, $this->order_Dir) ?>
    </section>

    <table class="akeeba-table akeeba-table--striped" id="itemsList">
        <thead>
        <tr>
            <th width="32">
                <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/>
            </th>
            <th>
				<?php echo \JHtml::_('grid.sort', 'COM_ADMINTOOLS_TEMPSUPERUSERS_FIELD_USER_ID', 'user_id', $this->order_Dir, $this->order, 'browse'); ?>
            </th>
            <th>
				<?php echo JText::_('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_USERNAME') ?>
            </th>
            <th>
				<?php echo \JHtml::_('grid.sort', 'COM_ADMINTOOLS_TEMPSUPERUSERS_FIELD_EXPIRATION', 'expiration', $this->order_Dir, $this->order, 'browse'); ?>
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
		<?php if (!count($this->items)): ?>
            <tr>
                <td colspan="6">
					<?php echo JText::_('COM_ADMINTOOLS_ERR_TEMPSUPERUSERS_NOITEMS') ?>
                </td>
            </tr>
            <tr>
                <td colspan="6">
                    <a href="index.php?option=com_admintools&view=TempSuperUsers&task=add"
                       class="akeeba-btn--green--big"
                    >
                        <span class="akion-android-person-add"></span>
						<?php echo JText::_('COM_ADMINTOOLS_BTN_TEMPSUPERUSERS_ADD') ?>
                    </a>
                </td>
            </tr>
		<?php endif; ?>
		<?php
		if ($this->items):
			$i = 0;
			/** @var \Akeeba\AdminTools\Admin\Model\TempSuperUsers $row */
			foreach ($this->items as $row):
				?>
                <tr>
                    <td><?php echo \JHtml::_('grid.id', ++$i, $row->user_id); ?></td>
                    <td>
                        <a href="index.php?option=com_admintools&view=TempSuperUsers&task=edit&id=<?php echo $row->user_id ?>">
							<?php echo $this->escape($row->user_id) ?>
                        </a>
                    </td>
                    <td>
                        <strong>
                            <a href="index.php?option=com_admintools&view=TempSuperUsers&task=edit&id=<?php echo $row->user_id ?>">
	                            <?php echo $row->user->username ?>
                            </a>
                        </strong>
                        <br/>
                        <small>
                            <a href="index.php?option=com_users&task=user.edit&id=<?php echo $row->user_id ?>"
                               target="_blank">
	                            <?php echo $row->user->name ?> <em>(<?php echo $row->user->email ?>)</em>
                            </a>
                        </small>
                    </td>
                    <td>
						<?php echo \Akeeba\AdminTools\Admin\Helper\Html::localisedDate($row->expiration) ?>
                    </td>
                </tr>
			<?php
			endforeach;
		endif; ?>
        </tbody>

    </table>

    <div class="akeeba-hidden-fields-container">
        <input type="hidden" name="option" id="option" value="com_admintools"/>
        <input type="hidden" name="view" id="view" value="TempSuperUsers"/>
        <input type="hidden" name="boxchecked" id="boxchecked" value="0"/>
        <input type="hidden" name="task" id="task" value="browse"/>
        <input type="hidden" name="filter_order" id="filter_order" value="<?php echo $this->escape($this->order); ?>"/>
        <input type="hidden" name="filter_order_Dir" id="filter_order_Dir"
               value="<?php echo $this->escape($this->order_Dir); ?>"/>
        <input type="hidden" name="<?php echo $this->container->platform->getToken(true); ?>" value="1"/>
    </div>
</form>
