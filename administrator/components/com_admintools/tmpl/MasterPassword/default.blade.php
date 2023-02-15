<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die;

/** @var $this Akeeba\AdminTools\Admin\View\MasterPassword\Html */
?>
<form action="index.php" method="post" name="adminForm" id="adminForm" class="akeeba-form--horizontal">
	<div class="akeeba-panel--primary">
		<header class="akeeba-block-header">
			<h3>@lang('COM_ADMINTOOLS_LBL_MASTERPASSWORD_PASSWORD')</h3>
		</header>

		<div class="akeeba-form-section--horizontal">
			<label for="masterpw">@lang('COM_ADMINTOOLS_LBL_MASTERPASSWORD_PWPROMPT')</label>

			<div>
				<input id="masterpw" type="password" name="masterpw" value="{{{ $this->masterpw }}}" />
			</div>
		</div>
	</div>

	<div class="akeeba-panel--primary">
		<header class="akeeba-block-header">
			<h3>@lang('COM_ADMINTOOLS_LBL_MASTERPASSWORD_PROTVIEWS')</h3>
		</header>

		<div class="akeeba-form-group">
			<label>@lang('COM_ADMINTOOLS_LBL_MASTERPASSWORD_QUICKSELECT')&nbsp;</label>
			<div>
				<button class="akeeba-btn--primary--small admintoolsMPMassSelect"
						data-newstate="1">
					@lang('COM_ADMINTOOLS_LBL_MASTERPASSWORD_ALL')
				</button>
				<button class="akeeba-btn--dark--small admintoolsMPMassSelect"
						data-newstate="0">
					@lang('COM_ADMINTOOLS_LBL_MASTERPASSWORD_NONE')
				</button>
			</div>
		</div>
		@foreach ($this->items as $view => $x)
			<?php [$locked, $langKey] = $x; ?>
			<div class="akeeba-form-group">
				<label for="views[{{{ $view }}}]">@lang($langKey)</label>

				@jhtml('FEFHelp.select.booleanswitch', 'views[' . $view . ']', ($locked ? 1 : 0), ['class' => 'masterpwcheckbox'])
			</div>
		@endforeach
	</div>

	<input type="hidden" name="option" value="com_admintools" />
	<input type="hidden" name="view" value="MasterPassword" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="@token(true)" value="1" />
</form>
