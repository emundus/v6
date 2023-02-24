<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var    $this   \Akeeba\AdminTools\Admin\View\ConfigureFixPermissions\Html */

$path = $this->at_path . (empty($this->at_path) ? '' : '/');

?>
<div class="akeeba-panel--info">
	<header class="akeeba-block-header">
		<h3>@lang('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_DEFAULTS')</h3>
	</header>
	<form name="defaultsForm" id="defaultsForm" action="index.php" method="post" class="akeeba-form--inline">
		<div class="akeeba-form-group">
			<label for="perms_show_hidden">@lang('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_SHOW_HIDDEN')</label>
			@jhtml('FEFHelp.select.booleanswitch', 'perms_show_hidden', $this->perms_show_hidden)
			&nbsp;
		</div>

		<div class="akeeba-form-group">
			<label for="dirperms">@lang('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_DEFDIRPERM')</label>
			{{ \Akeeba\AdminTools\Admin\Helper\Select::perms('dirperms', [], $this->dirperms) }}
			&nbsp;
		</div>

		<div class="akeeba-form-group">
			<label for="fileperms">@lang('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_DEFFILEPERMS')</label>
			{{ \Akeeba\AdminTools\Admin\Helper\Select::perms('fileperms', [], $this->fileperms) }}
			&nbsp;
		</div>

		<div class="akeeba-form-group--actions">
			<input type="submit" class="akeeba-btn--primary"
			   value="@lang('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_SAVEDEFAULTS')"/>
		</div>

		<input type="hidden" name="option" value="com_admintools"/>
		<input type="hidden" name="view" value="ConfigureFixPermissions"/>
		<input type="hidden" name="task" value="savedefaults"/>
		<input type="hidden" name="@token(true)" value="1"/>
	</form>
</div>


@unless(empty($this->listing['crumbs']))
	<ul class="breadcrumb">
		<li>
			@lang('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_PATH'):
			<a href="index.php?option=com_admintools&view=ConfigureFixPermissions&path=/">
				@lang('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_ROOT')
			</a>
			<span class="divider">/</span>
		</li>

		<?php
		$relpath = '';
		$i = 1;
		?>
		@foreach($this->listing['crumbs'] as $crumb)
			@unless(empty($crumb))
				<?php
				$i++;
				$relpath = ltrim($relpath . '/' . $crumb, '/');
				?>
			@endunless
			<li>
				<a href="index.php?option=com_admintools&view=ConfigureFixPermissions&path={{{ urlencode($relpath) }}}">
					{{{ $this->escape($crumb) }}}
				</a>
				@if($i < (is_array($this->listing['crumbs']) || $this->listing['crumbs'] instanceof \Countable ? count($this->listing['crumbs']) : 0))
					<span class="divider">/</span>
				@endif
			</li>
		@endforeach
	</ul>
@endunless

<form name="adminForm" id="adminForm" action="index.php" method="post">
	<input type="hidden" name="option" value="com_admintools"/>
	<input type="hidden" name="view" value="ConfigureFixPermissions"/>
	<input type="hidden" name="task" value="saveperms"/>
	<input type="hidden" name="path" value="{{{ $this->at_path }}}"/>
	<input type="hidden" name="@token(true)" value="1"/>

	<input type="submit" class="akeeba-btn--green" value="@lang('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_SAVEPERMS')"/>
	<input type="submit" class="akeeba-btn--orange"
		   value="@lang('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_SAVEAPPLYPERMS')"
		   onclick="document.forms.adminForm.task.value='saveapplyperms';"/>

	<div class="akeeba-container--50-50">
        <table class="akeeba-table--striped">
            <thead>
            <tr>
                <th>@lang('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_FOLDER')</th>
                <th>@lang('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_OWNER')</th>
                <th colspan="2">@lang('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_PERMS')</th>
            </tr>
            </thead>
            <tbody>
            @if(!empty($this->listing['folders']))
				@foreach ($this->listing['folders'] as $folder)
                <tr>
                    <td>
                        <a href="index.php?option=com_admintools&view=ConfigureFixPermissions&path={{{ urlencode($folder['path']) }}}">
                            {{{ $this->escape($folder['item']) }}}

                        </a>
                    </td>
                    <td>
                        {{{ $this->renderUGID($folder['uid'], $folder['gid']) }}}

                    </td>
                    <td>
                        {{{ $this->renderPermissions($folder['realperms']) }}}

                    </td>
                    <td align="right">
                        {{ \Akeeba\AdminTools\Admin\Helper\Select::perms('folders[' . $folder['path'] . ']', array('class' => 'input-mini'), $folder['perms']) }}

                    </td>
                </tr>
            	@endforeach
			@endif
            </tbody>
        </table>

        <table class="akeeba-table--striped">
            <thead>
            <tr>
                <th>@lang('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_FILE')</th>
                <th>@lang('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_OWNER')</th>
                <th colspan="2">@lang('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_PERMS')</th>
            </tr>
            </thead>
            <tbody>
            @if(!empty($this->listing['files']))
				@foreach ($this->listing['files'] as $file)
                <tr>
                    <td>
                        {{{ $this->escape($file['item']) }}}

                    </td>
                    <td>
                        {{{ $this->renderUGID($file['uid'], $file['gid']) }}}

                    </td>
                    <td>
                        {{{ $this->renderPermissions($file['realperms']) }}}

                    </td>
                    <td align="right">
                        {{ \Akeeba\AdminTools\Admin\Helper\Select::perms('files[' . $file['path'] . ']', array('class' => 'input-mini'), $file['perms']) }}
                    </td>
                </tr>
            	@endforeach
			@endif
            </tbody>
        </table>
	</div>

    <p></p>

	<p>
		<input type="submit" class="akeeba-btn--green"
			   value="@lang('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_SAVEPERMS')"/>
		<input type="submit" class="akeeba-btn--orange"
			   value="@lang('COM_ADMINTOOLS_LBL_CONFIGUREFIXPERMISSIONS_SAVEAPPLYPERMS')"
			   onclick="document.forms.adminForm.task.value='saveapplyperms';"/>
	</p>
</form>
