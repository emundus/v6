<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var $this Akeeba\AdminTools\Admin\View\WAFBlacklistedRequests\Html */
?>
@extends('any:lib_fof40/Common/browse')

@section('browse-page-top')
	@include('admin:com_admintools/ControlPanel/plugin_warning')
@stop

@section('browse-filters')
	<div class="akeeba-filter-element akeeba-form-group">
		{{ \Akeeba\AdminTools\Admin\Helper\Select::wafApplication('application', ['onchange' => 'document.adminForm.submit()'], $this->filters['application']) }}
	</div>
	<div class="akeeba-filter-element akeeba-form-group">
		{{ \Akeeba\AdminTools\Admin\Helper\Select::httpVerbs('fverb', ['onchange' => 'document.adminForm.submit()'], $this->filters['fverb']) }}
	</div>

	<div class="akeeba-filter-element akeeba-form-group">
		@searchfilter('foption', null, 'COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_OPTION')
	</div>

	<div class="akeeba-filter-element akeeba-form-group">
		@searchfilter('fview', null, 'COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_VIEW')
	</div>

	<div class="akeeba-filter-element akeeba-form-group">
		@searchfilter('ftask', null, 'COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_TASK')
	</div>

	<div class="akeeba-filter-element akeeba-form-group">
		@searchfilter('fquery', null, 'COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_QUERY')
	</div>

	<div class="akeeba-filter-element akeeba-form-group">
		@searchfilter('fquery_content', null, 'COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_QUERY_CONTENT')
	</div>

	<div class="akeeba-filter-element akeeba-form-group">
		{{ \Akeeba\AdminTools\Admin\Helper\Select::published($this->filters['published'], 'enabled', ['onchange' => 'document.adminForm.submit()']) }}
	</div>
@stop

@section('browse-table-header')
	<tr>
		<th width="32">
			@jhtml('FEFHelp.browse.checkall')
		</th>
		<th>
			@sortgrid('fverb', 'COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_VERB')
		</th>
		<th>
			@sortgrid('foption', 'COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_OPTION')
		</th>
		<th>
			@sortgrid('fview', 'COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_VIEW')
		</th>
		<th>
			@sortgrid('ftask', 'COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_TASK')
		</th>
		<th>
			@sortgrid('fquery', 'COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_QUERY')
		</th>
		<th>
			@sortgrid('query_content', 'COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_QUERY_CONTENT')
		</th>
		<th>
			@sortgrid('application', 'COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_APPLICATION')
		</th>
		<th>
			@sortgrid('published', 'JPUBLISHED')
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
	/** @var \Akeeba\AdminTools\Admin\Model\BadWords $row */
	?>
	@foreach($this->items as $row)
		<tr>
			<td>
				@jhtml('FEFHelp.browse.id', ++$i, $row->getId())
			</td>
			<td>
				<a href="index.php?option=com_admintools&view=WAFBlacklistedRequests&task=edit&id={{ $row->id }}">
					@if ($row->verb)
						{{{ $row->verb }}}
					@else
						@lang('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_ALL')
					@endif
				</a>
			</td>
			<td>
				<a href="index.php?option=com_admintools&view=WAFBlacklistedRequests&task=edit&id={{ $row->id }}">
					@if ($row->option)
						{{{ $row->option }}}
					@else
						@lang('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_ALL')
					@endif
				</a>
			</td>
			<td>
				<a href="index.php?option=com_admintools&view=WAFBlacklistedRequests&task=edit&id={{ $row->id }}">
					@if ($row->view)
						{{{ $row->view }}}
					@else
						@lang('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_ALL')
					@endif
				</a>
			</td>
			<td>
				<a href="index.php?option=com_admintools&view=WAFBlacklistedRequests&task=edit&id={{ $row->id }}">
					@if ($row->task)
						{{{ $row->task }}}
					@else
						@lang('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_ALL')
					@endif
				</a>
			</td>
			<td>
				<a href="index.php?option=com_admintools&view=WAFBlacklistedRequests&task=edit&id={{ $row->id }}">
					@if ($row->query)
						{{{ $row->query }}}
					@else
						@lang('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_ALL')
					@endif
				</a>
			</td>
			<td>
				<a href="index.php?option=com_admintools&view=WAFBlacklistedRequests&task=edit&id={{ $row->id }}">
					@if ($row->query_content)
						{{{ $row->query_content }}}
					@else
						@lang('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_ALL')
					@endif
				</a>
			</td>
			<td>
				<a href="index.php?option=com_admintools&view=WAFBlacklistedRequests&task=edit&id={{ $row->id }}">
					@if($row->application == 'site')
						@lang('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_APPLICATION_SITE')
					@elseif($row->application == 'admin')
						@lang('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_APPLICATION_ADMIN')
					@else
						@lang('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_APPLICATION_BOTH')
					@endif
				</a>
			</td>
			<td>
				@jhtml('jgrid.published', $row->enabled, $i, '', $this->container->platform->getUser()->authorise('core.edit.state', 'com_admintools'), 'cb')
			</td>
		</tr>
	@endforeach
@stop
