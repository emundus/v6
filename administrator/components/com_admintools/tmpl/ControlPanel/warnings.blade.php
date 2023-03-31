<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var  Akeeba\AdminTools\Admin\View\ControlPanel\Html $this For type hinting in the IDE */

$root      = realpath(JPATH_ROOT) ?: '';
$root      = trim($root);
$emptyRoot = empty($root);

?>
{{-- Joomla 4 upgrade prompt --}}
@if(version_compare(JVERSION, '3.999.999', 'gt'))
	<div class="akeeba-block--warning--large">
		<h1>🚨 🚨 🚨 Please upgrade to Admin Tools 7 🚨 🚨 🚨</h1>
		<p style="font-size: 1.5rem; margin: 1rem 0 0">
			You are currently using Admin Tools 6. This version was only made minimally compatible with Joomla 4 to allow you to upgrade from Joomla 3 to Joomla 4 without losing your settings. We will not provide support for Admin Tools 6 running on Joomla 4.0 and later.
		</p>
		<p style="font-size: 1.5rem; margin: 1rem 0 0">
			You need to download and install Admin Tools 7, our Joomla 4 native version of Akeeba Backup. It is fully supported for use on Joomla 4.
		</p>
		<p style="font-size: 1.5rem; margin: 1rem 0 0">
			The update should show up on your site's extensions update page. It may take a day or two as Joomla is caching the updates. If you do not see the update, you can download Admin Tools 7 manually and install it over Admin Tools 6 <em>without</em> uninstalling your existing copy of Admin Tools.
		</p>
		<p>
			<a href="https://www.akeeba.com/download.html" class="akeeba-btn--green--big--block" style="font-size: 1.5rem; margin: 2rem 0">
				<span class="akion akion-ios-download" aria-hidden="true"></span>
				Download Admin Tools 7 now
			</a>
		</p>
	</div>
@endif

{{-- Joomla 3 End of Life notice --}}
@if(time() > 1660683600)
	<div class="akeeba-block--warning">
		<h1>Joomla 3 is approaching its End of Life</h1>
		<p>
			Joomla 3 will become End of Life on August 17th, 2023. We will no longer provide any support or software updates for Joomla 3 after it becomes End of Life.
		</p>
		<p>
			Please upgrade your site to the latest Joomla version (Joomla 4 at the time of this writing) as soon as humanly possible. Afterwards, please update Admin Tools to the latest released version. The longer you delay the less likely is that there will be an upgrade path for your site.
		</p>
	</div>
@elseif(time() > 1692219600)
	<div class="akeeba-block--info">
		<h1>Joomla 3 is End of Life</h1>
		<p>
			We no longer provide support for using our software on Joomla 3 after it became End of Life on August 17th, 2023. We will no longer provide any kind of updates for this software including but not limited to: security updates, bug fixes, new features, or addressing compatibility issues with third party services and new web server and web browser versions.
		</p>
		<p>
			Please upgrade your site to the latest Joomla version (Joomla 4 at the time of this writing) as soon as humanly possible. Afterwards, please update Admin Tools to the latest released version. The longer you delay the less likely is that there will be an upgrade path for your site.
		</p>
	</div>
@endif

@include('admin:com_admintools/ControlPanel/needsipworkarounds')

@if (isset($this->jwarnings) && !empty($this->jwarnings))
	<div class="akeeba-block--failure">
		<h3>@lang('COM_ADMINTOOLS_ERR_CONTROLPANEL_JCONFIG')</h3>
		<p>{{ $this->jwarnings }}</p>
	</div>
@endif

{{-- Stuck database updates warning --}}
@if ($this->stuckUpdates)
	<div class="akeeba-block--failure">
		<p>
			@sprintf('COM_ADMINTOOLS_CPANEL_ERR_UPDATE_STUCK',
				$this->getContainer()->db->getPrefix(),
				'index.php?option=com_admintools&view=ControlPanel&task=forceUpdateDb'
			)
		</p>
	</div>
@endif

@if (isset($this->frontEndSecretWordIssue) && !empty($this->frontEndSecretWordIssue))
	<div class="akeeba-block--failure">
		<h3>@lang('COM_ADMINTOOLS_ERR_CONTROLPANEL_FESECRETWORD_HEADER')</h3>
		<p>@lang('COM_ADMINTOOLS_ERR_CONTROLPANEL_FESECRETWORD_INTRO')</p>
		<p>{{ $this->frontEndSecretWordIssue }}</p>
		<p>
			@lang('COM_ADMINTOOLS_ERR_CONTROLPANEL_FESECRETWORD_WHATTODO_JOOMLA')
			@sprintf('COM_ADMINTOOLS_ERR_CONTROLPANEL_FESECRETWORD_WHATTODO_COMMON', $this->newSecretWord)
		</p>
		<p>
			<a class="akeeba-btn--green akeeba-btn--big"
			   href="index.php?option=com_admintools&view=ControlPanel&task=resetSecretWord&@token()=1">
				<span class="akion-refresh"></span>
				@lang('COM_ADMINTOOLS_CONTROLPANEL_BTN_FESECRETWORD_RESET')
			</a>
		</p>
	</div>
@endif

{{-- Obsolete PHP version check --}}
@include('admin:com_admintools/ErrorPages/phpversion_warning', [
	'softwareName'  => 'Admin Tools',
	'minPHPVersion' => '7.2.0',
])

@if ($this->oldVersion && false)
	<div class="akeeba-block--warning">
		<strong>@lang('COM_ADMINTOOLS_ERR_CONTROLPANEL_OLDVERSION')</strong>
	</div>
@endif

@if ($emptyRoot)
	<div class="akeeba-block--failure">
		@lang('COM_ADMINTOOLS_LBL_CONTROLPANEL_EMPTYROOT')
	</div>
@endif

@if ($this->needsdlid)
	<div class="akeeba-block--success">
		<h3>
			@lang('COM_ADMINTOOLS_MSG_CONTROLPANEL_MUSTENTERDLID')
		</h3>
		<p>
			@sprintf('COM_ADMINTOOLS_LBL_CONTROLPANEL_NEEDSDLID', 'https://www.akeeba.com/download/official/add-on-dlid.html')
		</p>
		<form name="dlidform" action="index.php" method="post" class="akeeba-form--inline">
			<input type="hidden" name="option" value="com_admintools" />
			<input type="hidden" name="view" value="ControlPanel" />
			<input type="hidden" name="task" value="applydlid" />
			<input type="hidden" name="@token()" value="1" />
			<span>
				@lang('COM_ADMINTOOLS_MSG_CONTROLPANEL_PASTEDLID')
			</span>
			<input type="text" name="dlid"
				   placeholder="@lang('COM_ADMINTOOLS_LBL_JCONFIG_DOWNLOADID')"
				   class="akeeba-input--wide">
			<button type="submit" class="akeeba-btn--green">
				<span class="akion-checkmark-round"></span>
				@lang('COM_ADMINTOOLS_MSG_CONTROLPANEL_APPLYDLID')
			</button>
		</form>
	</div>
@endif

@if ($this->serverConfigEdited)
	<div class="akeeba-block--warning">
		<p>@lang('COM_ADMINTOOLS_CPANEL_SERVERCONFIGWARN')</p>

		<a href="index.php?option=com_admintools&view=ControlPanel&task=regenerateServerConfig"
		   class="akeeba-btn--green">
			@lang('COM_ADMINTOOLS_CPANEL_SERVERCONFIGWARN_REGENERATE')
		</a>
		<a href="index.php?option=com_admintools&view=ControlPanel&task=ignoreServerConfigWarn"
		   class="akeeba-btn--dark">
			@lang('COM_ADMINTOOLS_CPANEL_SERVERCONFIGWARN_IGNORE')
		</a>
	</div>
@endif
