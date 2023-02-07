<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var $this Akeeba\AdminTools\Admin\View\WhitelistedAddresses\Html */

?>
@extends('any:lib_fof40/Common/browse')

@section('browse-page-top')
	@include('admin:com_admintools/ControlPanel/plugin_warning')
	@include('admin:com_admintools/WhitelistedAddresses/feature_warning')
@stop

@section('browse-filters')
	<div class="akeeba-filter-element akeeba-form-group">
		@searchfilter('ip', null, 'COM_ADMINTOOLS_LBL_WHITELISTEDADDRESS_IP')
	</div>

	<div class="akeeba-filter-element akeeba-form-group">
		@searchfilter('description', null, 'COM_ADMINTOOLS_LBL_WHITELISTEDADDRESS_DESCRIPTION')
	</div>
@stop

@section('browse-table-header')
	<tr>
		<th width="32">
			@jhtml('FEFHelp.browse.checkall')
		</th>
		<th>
			@sortgrid('ip', 'COM_ADMINTOOLS_LBL_WHITELISTEDADDRESS_IP')
		</th>
		<th>
			@sortgrid('description', 'COM_ADMINTOOLS_LBL_WHITELISTEDADDRESS_DESCRIPTION')
		</th>
	</tr>
@stop

@section('browse-table-body-norecords')
	{{-- Table body shown when no records are present. --}}
	<tr>
		<td colspan="99">
			@lang('COM_ADMINTOOLS_ERR_WHITELISTEDADDRESS_NOITEMS')
		</td>
	</tr>
@stop

@section('browse-table-body-withrecords')
	<?php
	$i = 0;
	/** @var \Akeeba\AdminTools\Admin\Model\WhitelistedAddresses $row */
	?>
	@foreach($this->items as $row)
		<tr>
			<td>
				@jhtml('FEFHelp.browse.id', ++$i, $row->getId())
			</td>
			<td>
				<a href="index.php?option=com_admintools&view=WhitelistedAddresses&task=edit&id={{ $row->getId() }}">
					{{{ $row->ip }}}
				</a>
			</td>
			<td>
				{{{ $row->description }}}
			</td>
		</tr>
	@endforeach
@stop