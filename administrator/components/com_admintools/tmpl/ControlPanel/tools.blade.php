<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var  Akeeba\AdminTools\Admin\View\ControlPanel\Html $this For type hinting in the IDE */

?>
<div class="akeeba-panel--default">
	<header class="akeeba-block-header">
		<h3>@lang('COM_ADMINTOOLS_LBL_CONTROLPANEL_TOOLS')</h3>
	</header>

	<div class="akeeba-grid">
		@if(strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
			<a href="index.php?option=com_admintools&view=ConfigureFixPermissions" class="akeeba-action--teal">
				<span class="akion-ios-gear"></span>
				@lang('COM_ADMINTOOLS_TITLE_FIXPERMSCONFIG')
			</a>

			@if ($this->enable_fixperms)
				<a id="fixperms" href="index.php?option=com_admintools&view=FixPermissions&tmpl=component"
				   class="akeeba-action--teal">
					<span class="akion-wand"></span>
					@lang('COM_ADMINTOOLS_TITLE_FIXPERMS')
				</a>
			@endif
		@endif

		@if ($this->isPro)
			<a href="index.php?option=com_admintools&view=TempSuperUsers" class="akeeba-action--teal">
				<span class="akion-clock"></span>
				@lang('COM_ADMINTOOLS_TITLE_TEMPSUPERUSERS')
			</a>
		@endif

		<a href="index.php?option=com_admintools&view=SEOAndLinkTools" class="akeeba-action--teal">
			<span class="akion-link"></span>
			@lang('COM_ADMINTOOLS_TITLE_SEOANDLINK')
		</a>

		@if ($this->enable_cleantmp)
			<a id="cleantmp" href="index.php?option=com_admintools&view=CleanTempDirectory&tmpl=component"
			   class="akeeba-action--teal">
				<span class="akion-trash-a"></span>
				@lang('COM_ADMINTOOLS_TITLE_CLEANTMP')
			</a>
		@endif

		@if ($this->enable_tmplogcheck)
			<a id="tmplogcheck" href="index.php?option=com_admintools&view=CheckTempAndLogDirectories&tmpl=component"
			   class="akeeba-action--teal">
				<span class="akion-trash-a"></span>
				@lang('COM_ADMINTOOLS_TITLE_TMPLOGCHECK')
			</a>
		@endif

		@if ($this->enable_dbchcol && $this->isMySQL && $this->isJoomla3)
			<a href="index.php?option=com_admintools&view=ChangeDBCollation" class="akeeba-action--teal">
				<span class="akion-ios-redo"></span>
				@lang('COM_ADMINTOOLS_CHANGEDBCOLLATION')
			</a>
		@endif

		@if ($this->enable_dbtools && $this->isMySQL)
			<a id="optimizedb" href="index.php?option=com_admintools&view=DatabaseTools&task=optimize&tmpl=component"
			   class="akeeba-action--teal">
				<span class="akion-wand"></span>
				@lang('COM_ADMINTOOLS_LBL_DATABASETOOLS_OPTIMIZEDB')
			</a>
		@endif

		@if ($this->enable_cleantmp && $this->isMySQL)
			<a href="index.php?option=com_admintools&view=DatabaseTools&task=purgesessions" id="optimize"
			   class="akeeba-action--teal">
				<span class="akion-nuclear"></span>
				@lang('COM_ADMINTOOLS_LBL_DATABASETOOLS_PURGESESSIONS')
			</a>
		@endif

		<a href="index.php?option=com_admintools&view=Redirections" class="akeeba-action--teal">
			<span class="akion-shuffle"></span>
			@lang('COM_ADMINTOOLS_TITLE_REDIRS')
		</a>

		@if ($this->isPro)
			<a href="index.php?option=com_plugins&task=plugin.edit&extension_id={{ (int) $this->pluginid }}"
			   target="_blank" class="akeeba-action--teal">
				<span class="akion-calendar"></span>
				@lang('COM_ADMINTOOLS_LBL_CONTROLPANEL_SCHEDULING')
			</a>

			<a href="index.php?option=com_admintools&view=ImportAndExport&task=export" class="akeeba-action--teal">
				<span class="akion-share"></span>
				@lang('COM_ADMINTOOLS_TITLE_EXPORT_SETTINGS')
			</a>

			<a href="index.php?option=com_admintools&view=ImportAndExport&task=import" class="akeeba-action--teal">
				<span class="akion-archive"></span>
				@lang('COM_ADMINTOOLS_TITLE_IMPORT_SETTINGS')
			</a>
		@endif
	</div>
</div>
