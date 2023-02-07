<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var \Akeeba\AdminTools\Admin\View\AdminPassword\Html $this */

$modeOptions = [
		HTMLHelper::_('select.option', 'joomla', Text::_('COM_ADMINTOOLS_ADMINPASSWORD_LBL_MODE_JOOMLA')),
		HTMLHelper::_('select.option', 'php', Text::_('COM_ADMINTOOLS_ADMINPASSWORD_LBL_MODE_PHP')),
		HTMLHelper::_('select.option', 'everything', Text::_('COM_ADMINTOOLS_ADMINPASSWORD_LBL_MODE_EVERYTHING')),
];

?>
<div class="akeeba-panel--teal">
	<header class="akeeba-block-header">
		<h3>@lang('COM_ADMINTOOLS_LBL_ADMINPASSWORD_HOWITWORKS')</h3>
	</header>
	<p>
		@lang('COM_ADMINTOOLS_LBL_ADMINPASSWORD_INFO')
	</p>

	<p class="akeeba-block--warning">
		@lang('COM_ADMINTOOLS_LBL_ADMINPASSWORD_WARN')
	</p>
</div>

<form action="index.php" name="adminForm" id="adminForm" method="post" class="akeeba-form--horizontal">
	<div class="akeeba-form-group">
		<label for="mode">@lang('COM_ADMINTOOLS_ADMINPASSWORD_LBL_MODE')</label>
		@jhtml('select.genericlist', $modeOptions, 'mode', ['id' => 'mode', 'list.select' => $this->mode, 'list.attr'   => ['class' => 'form-select']])
		<p class="akeeba-help-text">
			@lang('COM_ADMINTOOLS_ADMINPASSWORD_LBL_MODE_HELP')
		</p>
	</div>

	<div class="akeeba-form-group">
		<label for="resetErrorPages">@lang('COM_ADMINTOOLS_LBL_ADMINPASSWORD_RESETERRORPAGES')</label>
		@jhtml('FEFHelp.select.booleanswitch', 'resetErrorPages', $this->resetErrorPages)
		<p class="akeeba-help-text">
			@lang('COM_ADMINTOOLS_LBL_ADMINPASSWORD_RESETERRORPAGES_HELP')
		</p>
	</div>

	<div class="akeeba-form-group">
		<label for="username">@lang('COM_ADMINTOOLS_LBL_ADMINPASSWORD_USERNAME')</label>
		<input type="text" name="username" id="username" value="{{{ $this->username }}}" autocomplete="off"/>
		<p class="akeeba-help-text">
			@lang('COM_ADMINTOOLS_LBL_ADMINPASSWORD_USERNAME_HELP')
		</p>
	</div>

	<div class="akeeba-form-group">
		<label for="password">@lang('COM_ADMINTOOLS_LBL_ADMINPASSWORD_PASSWORD')</label>
		<input type="password" name="password" id="password" value="{{{ $this->password }}}" autocomplete="off"/>
		<p class="akeeba-help-text">
			@lang('COM_ADMINTOOLS_LBL_ADMINPASSWORD_PASSWORD_HELP')
		</p>
	</div>

	<div class="akeeba-form-group">
		<label for="password2">@lang('COM_ADMINTOOLS_LBL_ADMINPASSWORD_PASSWORD2')</label>
		<input type="password" name="password2" id="password2" value="{{{ $this->password }}}"  autocomplete="off"/>
		<p class="akeeba-help-text">
			@lang('COM_ADMINTOOLS_LBL_ADMINPASSWORD_PASSWORD2_HELP')
		</p>
	</div>

	<div class="akeeba-form-group--pull-right">
		<div class="akeeba-form-group--actions">
			<button type="submit" class="akeeba-btn--orange">
				<span class="akion-android-lock"></span>
				@lang('COM_ADMINTOOLS_LBL_ADMINPASSWORD_PROTECT')
			</button>

			@if ($this->adminLocked)
			<a class="akeeba-btn--green"
			   href="index.php?option=com_admintools&view=AdminPassword&task=unprotect&@token()=1"
			>
				<span class="akion-android-unlock"></span>
				@lang('COM_ADMINTOOLS_LBL_ADMINPASSWORD_UNPROTECT')
			</a>
			@endif
		</div>
	</div>

    <input type="hidden" name="option" value="com_admintools"/>
    <input type="hidden" name="view" value="AdminPassword"/>
    <input type="hidden" name="task" id="task" value="protect"/>
    <input type="hidden" name="@token()" value="1"/>
</form>