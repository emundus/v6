<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var $this \Akeeba\AdminTools\Admin\View\Redirections\Html */

$baseUri = \Joomla\CMS\Uri\Uri::base();

if (substr($baseUri, -14) == 'administrator/')
{
	$baseUri = substr($baseUri, 0, -14);
}
?>
@extends('any:lib_fof40/Common/browse')

@section('browse-page-top')
	@include('admin:com_admintools/ControlPanel/plugin_warning')

	<div class="akeeba-panel--info">
		<form name="enableForm" action="index.php" method="post" class="akeeba-form--inline">
			<div class="akeeba-form-group">
				<label for="urlredirection">@lang('COM_ADMINTOOLS_LBL_REDIRECTION_PREFERENCE')</label>
				@jhtml('FEFHelp.select.booleanswitch', 'urlredirection', $this->urlredirection)
				&nbsp;
			</div>
			<div class="akeeba-form-group--actions">
				<button class="akeeba-btn--primary">@lang('COM_ADMINTOOLS_LBL_REDIRECTION_PREFERENCE_SAVE')</button>
			</div>
			<div>
				<input type="hidden" name="option" id="option" value="com_admintools" />
				<input type="hidden" name="view" id="view" value="Redirections" />
				<input type="hidden" name="task" id="task" value="applypreference" />
			</div>
		</form>
	</div>
@stop

@section('browse-filters')
	<div class="akeeba-filter-element akeeba-form-group">
		@searchfilter('dest', null, 'COM_ADMINTOOLS_LBL_REDIRECTION_DEST')
	</div>
	<div class="akeeba-filter-element akeeba-form-group">
		@searchfilter('source', null, 'COM_ADMINTOOLS_LBL_REDIRECTION_SOURCE')
	</div>
	<div class="akeeba-filter-element akeeba-form-group">
		{{ \Akeeba\AdminTools\Admin\Helper\Select::keepUrlParamsList('keepurlparams', ['onchange' => 'document.adminForm.submit()'], $this->filters['keepParams']) }}
	</div>

	<div class="akeeba-filter-element akeeba-form-group">
		{{ \Akeeba\AdminTools\Admin\Helper\Select::published($this->filters['published'], 'published', ['onchange' => 'document.adminForm.submit()']) }}
	</div>
@stop

@section('browse-table-header')
	<tr>
		<th width="20px">
			@jhtml('FEFHelp.browse.orderfield', 'ordering')
		</th>
		<th width="32">
			@jhtml('FEFHelp.browse.checkall')
		</th>
		<th>
			@sortgrid('dest', 'COM_ADMINTOOLS_LBL_REDIRECTION_DEST')
		</th>
		<th>
			@sortgrid('source', 'COM_ADMINTOOLS_LBL_REDIRECTION_SOURCE')
		</th>
		<th>
			@sortgrid('keepurlparams', 'COM_ADMINTOOLS_REDIRECTIONS_FIELD_KEEPURLPARAMS')
		</th>
		<th width="8%">
			@sortgrid('published', 'JPUBLISHED')
		</th>
	</tr>
@stop

@section('browse-table-body-norecords')
	{{-- Table body shown when no records are present. --}}
	<tr>
		<td colspan="99">
			@lang('COM_ADMINTOOLS_ERR_REDIRECTION_NOITEMS')
		</td>
	</tr>
@stop

@section('browse-table-body-withrecords')
	<?php
	$i = 0;
	/** @var \Akeeba\AdminTools\Admin\Model\Redirections $row */
	?>
	@foreach($this->items as $row)
		<tr>
			<td>
				@jhtml('FEFHelp.browse.order', 'ordering', $row->ordering)
			</td>
			<td>
				@jhtml('FEFHelp.browse.id', ++$i, $row->getId())
			</td>
			<td>
				<a href="index.php?option=com_admintools&view=Redirections&task=edit&id={{ (int)($row->getId()) }}">
					<span class="muted">{{ $baseUri }}</span><strong>{{ $row->dest }}</strong>
				</a>
			</td>
			<td>
				<a href="{{ $row->source }}" target="_blank">
					{{ htmlentities($row->source) }}
					<span class="akion-android-open"></span>
				</a>
			</td>
			<td>
				@lang('COM_ADMINTOOLS_REDIRECTION_KEEPURLPARAMS_LBL_' . ($row->keepurlparams == 0 ? 'OFF' : ($row->keepurlparams == 1 ? 'ALL' : 'ADD')))
			</td>
			<td>
				@jhtml('FEFHelp.browse.published', $row->published, $i)
			</td>
		</tr>
	@endforeach
@stop