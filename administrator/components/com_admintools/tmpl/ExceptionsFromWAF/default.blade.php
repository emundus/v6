<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var $this Akeeba\AdminTools\Admin\View\ExceptionsFromWAF\Html */
?>
@extends('any:lib_fof40/Common/browse')

@section('browse-page-top')
	<div id="admintools-whatsthis" class="akeeba-block--info">
		<p>@lang('COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_WHATSTHIS_LBLA')</p>
		<ul>
			<li>@lang('COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_WHATSTHIS_LBLB')</li>
			<li>@lang('COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_WHATSTHIS_LBLC')</li>
		</ul>
	</div>

	{{-- Let's check if the system plugin is correctly installed AND published --}}
	@include('admin:com_admintools/ControlPanel/plugin_warning')
@stop

@section('browse-filters')
	<div class="akeeba-filter-element akeeba-form-group">
		@searchfilter('foption', null, 'COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_OPTION')
	</div>

	<div class="akeeba-filter-element akeeba-form-group">
		@searchfilter('fview', null, 'COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_VIEW')
	</div>

	<div class="akeeba-filter-element akeeba-form-group">
		@searchfilter('fquery', null, 'COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_QUERY')
	</div>
@stop

@section('browse-table-header')
	<tr>
		<th width="32">
			@jhtml('FEFHelp.browse.checkall')
		</th>
		<th>
			@sortgrid('foption', 'COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_OPTION')
		</th>
		<th>
			@sortgrid('fview', 'COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_VIEW')
		</th>
		<th>
			@sortgrid('fquery', 'COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_QUERY')
		</th>
	</tr>
@stop

@section('browse-table-body-withrecords')
	<?php
	$i = 0;
	/** @var \Akeeba\AdminTools\Admin\Model\ExceptionsFromWAF $row */
	?>
	@foreach($this->items as $row)
		<tr>
			<td>@jhtml('FEFHelp.browse.id', ++$i, $row->getId())</td>
			<td>
				<a href="index.php?option=com_admintools&view=ExceptionsFromWAF&task=edit&id={{ (int)($row->id) }}">
					@if(!empty($row->option))
						{{ $row->option }}
					@else
						@lang('COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_OPTION_ALL')
					@endif
				</a>
			</td>
			<td>
				<a href="index.php?option=com_admintools&view=ExceptionsFromWAF&task=edit&id={{ (int)($row->id) }}">
					@if(!empty($row->view))
						{{ $row->view }}
					@else
						@lang('COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_VIEW_ALL')
					@endif
				</a>
			</td>
			<td>
				<a href="index.php?option=com_admintools&view=ExceptionsFromWAF&task=edit&id={{ (int)($row->id) }}">
					@if(!empty($row->query))
						{{ $row->query }}
					@else
						@lang('COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_QUERY_ALL')
					@endif
				</a>
			</td>
		</tr>
	@endforeach
@stop

@section('browse-table-body-norecords')
	{{-- Table body shown when no records are present. --}}
	<tr>
		<td colspan="99">
			@lang('COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_NOITEMS')
		</td>
	</tr>
@stop
