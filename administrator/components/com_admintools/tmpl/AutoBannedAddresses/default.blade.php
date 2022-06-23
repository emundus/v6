<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

use Akeeba\AdminTools\Admin\Helper\Html;

/** @var $this Akeeba\AdminTools\Admin\View\AutoBannedAddresses\Html */


?>
@extends('any:lib_fof40/Common/browse')

@section('browse-page-top')
	@include('admin:com_admintools/ControlPanel/plugin_warning')
@stop

@section('browse-filters')
	<div class="akeeba-filter-element akeeba-form-group">
		@searchfilter('ip', null, 'COM_ADMINTOOLS_LBL_AUTOBANNEDADDRESS_IP')
	</div>
@stop

@section('browse-table-header')
	<tr>
		<th width="32">
			@jhtml('FEFHelp.browse.checkall')
		</th>
		<th>
			@sortgrid('ip', 'COM_ADMINTOOLS_LBL_AUTOBANNEDADDRESS_IP')
		</th>
		<th>
			@sortgrid('reason', 'COM_ADMINTOOLS_LBL_AUTOBANNEDADDRESS_REASON')
		</th>
		<th>
			@sortgrid('until', 'COM_ADMINTOOLS_LBL_AUTOBANNEDADDRESS_UNTIL')
		</th>
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
				{{ \Akeeba\AdminTools\Admin\Helper\Html::IpLookup($row->ip) }}
			</td>
			<td>
				@lang('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_REASON_' . strtoupper($row->reason))
			</td>
			<td>
				{{ \Akeeba\AdminTools\Admin\Helper\Html::localisedDate($row->until, 'Y-m-d H:i:s T', false) }}
			</td>
		</tr>
	@endforeach
@stop

@section('browse-table-body-norecords')
	{{-- Table body shown when no records are present. --}}
	<tr>
		<td colspan="99">
			@lang('COM_ADMINTOOLS_ERR_AUTOBANNEDADDRESS_NOITEMS')
		</td>
	</tr>
@stop
