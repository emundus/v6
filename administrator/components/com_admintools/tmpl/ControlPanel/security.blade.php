<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var  Akeeba\AdminTools\Admin\View\ControlPanel\Html $this For type hinting in the IDE */
?>

<div class="akeeba-panel--primary">
	<header class="akeeba-block-header">
		<h3>@lang('COM_ADMINTOOLS_LBL_CONTROLPANEL_SECURITY')</h3>
	</header>

	<div class="akeeba-grid">
		@if (ADMINTOOLS_PRO && $this->needsQuickSetup)
			<a href="index.php?option=com_admintools&view=QuickStart" class="akeeba-action--orange">
				<span class="akion-flash"></span>
				@lang('COM_ADMINTOOLS_TITLE_QUICKSTART')
			</a>
		@endif

		@if ($this->htMakerSupported)
			<a href="index.php?option=com_admintools&view=EmergencyOffline" class="akeeba-action--red">
				<span class="akion-power"></span>
				@lang('COM_ADMINTOOLS_TITLE_EOM')<br />
			</a>
		@endif

		<a href="index.php?option=com_admintools&view=MasterPassword" class="akeeba-action--orange">
			<span class="akion-key"></span>
			@lang('COM_ADMINTOOLS_TITLE_MASTERPW')<br />
		</a>

		<?php if ($this->htMakerSupported): ?>
			<a href="index.php?option=com_admintools&view=AdminPassword" class="akeeba-action--orange">
				<span class="akion-{{ $this->adminLocked ? 'locked' : 'unlocked' }}"></span>
				@lang('COM_ADMINTOOLS_TITLE_ADMINPW')<br />
			</a>
		<?php endif; ?>

		@if ($this->isPro)
			@if ($this->htMakerSupported)
				<a href="index.php?option=com_admintools&view=HtaccessMaker" class="akeeba-action--teal">
					<span class="akion-document-text"></span>
					@lang('COM_ADMINTOOLS_TITLE_HTMAKER')<br />
				</a>
			@endif

			@if ($this->nginxMakerSupported)
				<a href="index.php?option=com_admintools&view=NginXConfMaker" class="akeeba-action--teal">
					<span class="akion-document-text"></span>
					@lang('COM_ADMINTOOLS_TITLE_NGINXMAKER')<br />
				</a>
			@endif

			@if ($this->webConfMakerSupported)
				<a href="index.php?option=com_admintools&view=WebConfigMaker" class="akeeba-action--teal">
					<span class="akion-document-text"></span>
					@lang('COM_ADMINTOOLS_TITLE_WCMAKER')<br />
				</a>
			@endif

			<a href="index.php?option=com_admintools&view=WebApplicationFirewall" class="akeeba-action--grey">
				<span class="akion-close-circled"></span>
				@lang('COM_ADMINTOOLS_TITLE_WAF')<br />
			</a>

			<a href="index.php?option=com_admintools&view=Scans" class="akeeba-action--grey">
				<span class="akion-search"></span>
				@lang('COM_ADMINTOOLS_TITLE_SCANS')<br />
			</a>

			<a href="index.php?option=com_admintools&view=SchedulingInformation" class="akeeba-action--grey">
				<span class="akion-calendar"></span>
				@lang('COM_ADMINTOOLS_TITLE_SCHEDULINGINFORMATION')<br />
			</a>
		@endif
	</div>
</div>
