<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var $this Akeeba\AdminTools\Admin\View\ScanAlerts\Html */

defined('_JEXEC') || die;

?>
@extends('any:lib_fof40/Common/edit')

@section('edit-form-body')
<div class="akeeba-panel--information">
	<header class="akeeba-block-header">
		<h3>@lang('COM_ADMINTOOLS_LBL_SCANALERT_FILEINFO')</h3>
	</header>

	<table class="akeeba-table--striped">
		<tr>
			<td>
				@lang('COM_ADMINTOOLS_LBL_SCANALERTS_PATH')
			</td>
			<td>
				{{ $this->item->path }}
			</td>
		</tr>
		<tr>
			<td>
				@lang('COM_ADMINTOOLS_LBL_SCANALERT_SCANDATE')
			</td>
			<td>
				{{ $this->scanDate->format(\Joomla\CMS\Language\Text::_('DATE_FORMAT_LC2') . ' T', true) }}
			</td>
		</tr>
		<tr>
			<td>
				@lang('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS')
			</td>
			<td>
				<span class="admintools-scanfile-{{ $this->fstatus }} {{ $this->item->threat_score ? '' : 'admintools-scanfile-nothreat' }}">
					@lang('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS_' . $this->fstatus)
				</span>
			</td>
		</tr>
		<tr>
			<td>
				@lang('COM_ADMINTOOLS_LBL_SCANALERTS_THREAT_SCORE')
			</td>
			<td>
				<span class="admintools-scanfile-threat-{{ $this->threatindex }}">
					{{ $this->item->threat_score }}
				</span>
			</td>
		</tr>
		<tr>
			<td>
				@lang('COM_ADMINTOOLS_LBL_SCANALERTS_ACKNOWLEDGED')
			</td>
			<td>
				@jhtml('FEFHelp.select.booleanswitch', 'acknowledged', $this->item->acknowledged)
			</td>
		</tr>

	</table>
</div>

<div class="{{ ($this->generateDiff && ($this->fstatus == 'modified')) ? 'akeeba-tabs' : '' }}">
	@if($this->generateDiff && ($this->fstatus == 'modified'))
		<label for="diff"
			   class="active">@lang('COM_ADMINTOOLS_LBL_SCANALERT_DIFF')</label>
		<section id="diff">
			<pre class="highlightCode {{ $this->suspiciousFile ? 'php' : 'diff' }}">{{ $this->item->diff }}</pre>
		</section>
	@endif

	@if (!@file_exists(JPATH_SITE . '/' . $this->item->path) && !$this->generateDiff)
		<div class="akeeba-block--failure">
			@sprintf('COM_ADMINTOOLS_LBL_SCANALERT_FILENOTFOUND', $this->item->path)
		</div>
	@else
		@if($this->generateDiff && ($this->fstatus == 'modified'))
			<label for="source">@lang('COM_ADMINTOOLS_LBL_SCANALERT_SOURCE')</label>
		@else
			<h4>@lang('COM_ADMINTOOLS_LBL_SCANALERT_SOURCE')</h4>
		@endif

		<section id="source">
			<div class="akeeba-block--warning--small">
				@lang('COM_ADMINTOOLS_LBL_SCANALERT_SOURCE_NOTE')
			</div>

			<div class="akeeba-form-group">
				<label>@lang('COM_ADMINTOOLS_LBL_SCANALERTS_MD5')</label>

				<div>
					<span class="akeeba-help-text">{{ @md5_file(JPATH_SITE . '/' . $this->item->path) }}</span>
				</div>
			</div>

			<div style="clear:left"></div>

			<pre class="highlightCode language-php">{{ $this->item->getFileSourceForDisplay(true) }}</pre>
		</section>
	@endif
</div>
@stop
