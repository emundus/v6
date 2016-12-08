<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

/** @var    $this   \Akeeba\AdminTools\Admin\View\ConfigureFixPermissions\Html */
use Akeeba\AdminTools\Admin\Helper\Select;

defined('_JEXEC') or die;

$path = $this->at_path;

if (!empty($path))
{
	$path .= '/';
}

?>
<form name="defaultsForm" id="defaultsForm" action="index.php" method="post" class="form form-inline well">
	<input type="hidden" name="option" value="com_admintools"/>
	<input type="hidden" name="view" value="ConfigureFixPermissions"/>
	<input type="hidden" name="task" value="savedefaults"/>
	<input type="hidden" name="<?php echo $this->escape(JFactory::getSession()->getFormToken()); ?>" value="1"/>

	<h4><?php echo \JText::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_DEFAULTS'); ?></h4>

	<label for="perms_show_hidden"><?php echo JText::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_SHOW_HIDDEN')?></label>
	<?php echo Select::booleanlist('perms_show_hidden', array('class' => 'input-mini'), $this->perms_show_hidden)?>

	<label for="dirperms"><?php echo \JText::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_DEFDIRPERM'); ?></label>
	<?php echo Select::perms('dirperms', array('class' => 'input-mini'), $this->dirperms); ?>


	<label for="fileperms"><?php echo \JText::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_DEFFILEPERMS'); ?></label>
	<?php echo Select::perms('fileperms', array('class' => 'input-mini'), $this->fileperms); ?>


	<input type="submit" class="btn btn-primary"
		   value="<?php echo \JText::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_SAVEDEFAULTS'); ?>"/>
</form>

<?php if (!empty($this->listing['crumbs'])): ?>
	<ul class="breadcrumb">
		<li>
			<?php echo \JText::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_PATH'); ?>:
			<a href="index.php?option=com_admintools&view=ConfigureFixPermissions&path=/">
				<?php echo \JText::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_ROOT'); ?>
			</a>
			<span class="divider">/</span>
		</li>

		<?php $relpath = '';
		$i = 1; ?>
		<?php foreach ($this->listing['crumbs'] as $crumb): ?>
			<?php $i++;
			if (empty($crumb))
			{
				continue;
			} ?>
			<?php $relpath = ltrim($relpath . '/' . $crumb, '/'); ?>
			<li>
				<a href="index.php?option=com_admintools&view=ConfigureFixPermissions&path=<?php echo $this->escape(urlencode($relpath)); ?>">
					<?php echo $this->escape($this->escape($crumb)); ?>

				</a>
				<?php if ($i < count($this->listing['crumbs'])): ?>
					<span class="divider">/</span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif ?>

<form name="adminForm" id="adminForm" action="index.php" method="post">
	<input type="hidden" name="option" value="com_admintools"/>
	<input type="hidden" name="view" value="ConfigureFixPermissions"/>
	<input type="hidden" name="task" value="saveperms"/>
	<input type="hidden" name="path" value="<?php echo $this->escape($this->at_path); ?>"/>
	<input type="hidden" name="<?php echo $this->escape(JFactory::getSession()->getFormToken()); ?>" value="1"/>

	<input type="submit" class="btn btn-success" value="<?php echo \JText::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_SAVEPERMS'); ?>"/>
	<input type="submit" class="btn btn-warning"
		   value="<?php echo \JText::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_SAVEAPPLYPERMS'); ?>"
		   onclick="document.forms.adminForm.task.value='saveapplyperms';"/>

	<div id="splitlist">
		<fieldset>
			<table class="table table-striped">
				<thead>
				<tr>
					<th><?php echo \JText::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_FOLDER'); ?></th>
					<th><?php echo \JText::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_OWNER'); ?></th>
					<th colspan="2"><?php echo \JText::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_PERMS'); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php $i = 1; ?>
				<?php if (!empty($this->listing['folders'])) foreach ($this->listing['folders'] as $folder): ?>
					<?php $i = 1 - $i; ?>
					<tr class="row<?php echo $this->escape($i); ?>">
						<td>
							<a href="index.php?option=com_admintools&view=ConfigureFixPermissions&path=<?php echo $this->escape(urlencode($folder['path'])); ?>">
								<?php echo $this->escape($this->escape($folder['item'])); ?>

							</a>
						</td>
						<td>
							<?php echo $this->escape($this->renderUGID($folder['uid'], $folder['gid'])); ?>

						</td>
						<td>
							<?php echo $this->escape($this->renderPermissions($folder['realperms'])); ?>

						</td>
						<td align="right">
							<?php echo Select::perms('folders[' . $folder['path'] . ']', array('class' => 'input-mini'), $folder['perms']); ?>

						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</fieldset>

		<fieldset>
			<table class="table table-striped">
				<thead>
				<tr>
					<th><?php echo \JText::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_FILE'); ?></th>
					<th><?php echo \JText::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_OWNER'); ?></th>
					<th colspan="2"><?php echo \JText::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_PERMS'); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php $i = 1; ?>
				<?php if (!empty($this->listing['files'])) foreach ($this->listing['files'] as $file): ?>
					<?php $i = 1 - $i; ?>
					<tr class="row<?php echo $this->escape($i); ?>">
						<td>
							<?php echo $this->escape($this->escape($file['item'])); ?>

						</td>
						<td>
							<?php echo $this->escape($this->renderUGID($file['uid'], $file['gid'])); ?>

						</td>
						<td>
							<?php echo $this->escape($this->renderPermissions($file['realperms'])); ?>

						</td>
						<td align="right">
							<?php echo Select::perms('files[' . $file['path'] . ']', array('class' => 'input-mini'), $file['perms']); ?>

						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</fieldset>
	</div>
	<div style="clear: left;"></div>

	<div class="form-actions">
		<input type="submit" class="btn btn-success"
			   value="<?php echo \JText::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_SAVEPERMS'); ?>"/>
		<input type="submit" class="btn btn-warning"
			   value="<?php echo \JText::_('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_SAVEAPPLYPERMS'); ?>"
			   onclick="document.forms.adminForm.task.value='saveapplyperms';"/>
	</div>
</form>