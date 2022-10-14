<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var Akeeba\AdminTools\Admin\View\Scans\Html $this */
?>
@extends('any:lib_fof40/Common/browse')

@section('browse-page-top')
<div class="akeeba-block--info">
	<p>
		@lang('COM_ADMINTOOLS_MSG_SCAN_CONFIGUREHELP')
	</p>
</div>
@stop

@section('browse-table-header')
<tr>
	<th width="32">
		@jhtml('FEFHelp.browse.checkall')
	</th>
	<th>
		@sortgrid('id', '#')
	</th>
	<th>
		@sortgrid('scanstart', 'COM_ADMINTOOLS_LBL_SCAN_START')
	</th>
	<th>
		@lang('COM_ADMINTOOLS_LBL_SCAN_TOTAL')
	</th>
	<th>
		@lang('COM_ADMINTOOLS_LBL_SCAN_MODIFIED')
	</th>
	<th>
		@lang('COM_ADMINTOOLS_LBL_SCAN_THREATNONZERO')
	</th>
	<th>
		@lang('COM_ADMINTOOLS_LBL_SCAN_ADDED')
	</th>
	<th>
		@lang('COM_ADMINTOOLS_LBL_SCAN_ACTIONS')
	</th>
</tr>
@stop

@section('browse-table-body-norecords')
	{{-- Table body shown when no records are present. --}}
	<tr>
		<td colspan="99">
			@lang('COM_ADMINTOOLS_MSG_COMMON_NOITEMS')
		</td>
	</tr>
@stop

@section('browse-table-body-withrecords')
	<?php
	$i = 0;
	/** @var \Akeeba\AdminTools\Admin\Model\Scans $row */
	?>
	@foreach($this->items as $row)
		<tr>
			<td>
				@jhtml('FEFHelp.browse.id', ++$i, $row->getId())
			</td>
			<td>
				{{ $row->id }}
			</td>
			<td>
				{{ $row->scanstart }}
			</td>
			<td>
				{{ $row->totalfiles }}
			</td>
			<td>
				<span class="admintools-files-{{ $row->files_modified ? 'alert' : 'noalert' }}">
					{{ $row->files_modified }}
				</span>
			</td>
			<td>
				<span class="admintools-files-{{ $row->files_suspicious ? 'alert' : 'noalert' }}">
					{{ $row->files_suspicious }}
				</span>
			</td>
			<td>
				<span class="admintools-files-{{ $row->files_new ? 'alert' : 'noalert' }}">
					{{ $row->files_new }}
				</span>
			</td>
			<td>
				@if ($row->files_modified + $row->files_new + $row->files_suspicious)
				<a class="akeeba-btn--primary--small" href="index.php?option=com_admintools&view=ScanAlerts&scan_id={{ (int)($row->id) }}">
					@lang('COM_ADMINTOOLS_LBL_SCAN_ACTIONS_VIEW')
				</a>
				@endif
			</td>
		</tr>
	@endforeach
@stop

@section('browse-page-bottom')
<div id="admintools-scan-dim" style="display: none">
	<div id="admintools-scan-container" class="akeeba-renderer-fef">
		<div class="akeeba-block--info large">
			<h4>
				@lang('COM_ADMINTOOLS_MSG_SCAN_PLEASEWAIT')
			</h4>
			<p>
				@lang('COM_ADMINTOOLS_MSG_SCAN_SCANINPROGRESS')
			</p>
		</div>
		<p>
			<progress></progress>
		</p>
		<p>
			<span id="admintools-lastupdate-text" class="lastupdate"></span>
		</p>
	</div>
</div>
@stop