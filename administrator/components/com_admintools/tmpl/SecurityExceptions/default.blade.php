<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */


defined('_JEXEC') || die;

/** @var $this \Akeeba\AdminTools\Admin\View\SecurityExceptions\Html */

?>
@extends('any:lib_fof40/Common/browse')

@section('browse-page-top')
	@include('admin:com_admintools/BlacklistedAddresses/toomanyips_warning')
	@include('admin:com_admintools/ControlPanel/needsipworkarounds', [
		'returnurl' => base64_encode('index.php?option=com_admintools&view=SecurityExceptions'),
    ])
@stop

@section('browse-filters')
	<div class="akeeba-filter-element akeeba-form-group akeeba-filter-joomlacalendarfix">
		@if (version_compare(JVERSION, '3.999.999', 'le'))
			@jhtml('calendar', $this->filters['from'], 'datefrom', 'datefrom', '%Y-%m-%d', ['class' => 'input-small'])
		@else
			<input
					type="date"
					pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}"
					name="datefrom"
					id="datefrom"
					value="{{{ $this->filters['from'] }}}"
					placeholder="@lang('COM_CONTACTUS_ITEMS_FIELD_CREATED_ON')"
			>
		@endif
	</div>

	<div class="akeeba-filter-element akeeba-form-group akeeba-filter-joomlacalendarfix">
		@if (version_compare(JVERSION, '3.999.999', 'le'))
			@jhtml('calendar', $this->filters['to'], 'dateto', 'dateto', '%Y-%m-%d', ['class' => 'input-small'])
		@else
			<input
					type="date"
					pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}"
					name="dateto"
					id="dateto"
					value="{{{ $this->filters['to'] }}}"
					placeholder="@lang('COM_CONTACTUS_ITEMS_FIELD_CREATED_ON')"
			>
		@endif
	</div>

	<div class="akeeba-filter-element akeeba-form-group">
		@searchfilter('ip', null, 'COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_IP')
	</div>

	<div class="akeeba-filter-element akeeba-form-group">
		{{ \Akeeba\AdminTools\Admin\Helper\Select::reasons('reason', $this->filters['reason'], ['onchange' => 'document.adminForm.submit()']) }}
	</div>
@stop

@section('browse-table-header')
	<tr>
		<th width="20px">
			@jhtml('FEFHelp.browse.checkall')
		</th>
		<th style="width:17%">
			@sortgrid('logdate', 'COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_LOGDATE')
		</th>
		<th style="width:15%">
			@sortgrid('ip', 'COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_IP')
		</th>
		<th style="width: 15%">
			@sortgrid('reason', 'COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_REASON')
		</th>
		<th>
			@sortgrid('url', 'COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_URL')
		</th>
	</tr>
@stop

@section('browse-table-body-norecords')
	{{-- Table body shown when no records are present. --}}
	<tr>
		<td colspan="99">
			@lang('COM_ADMINTOOLS_ERR_SECURITYEXCEPTION_NOITEMS')
		</td>
	</tr>
@stop

@section('browse-table-body-withrecords')
	<?php
	$i = 0;
	$cparams = \Akeeba\AdminTools\Admin\Helper\Storage::getInstance();
	$iplink  = $cparams->getValue('iplookupscheme', 'http') . '://' . $cparams->getValue('iplookup', 'ip-lookup.net/index.php?ip={ip}');
	/** @var \Akeeba\AdminTools\Admin\Model\SecurityExceptions $row */
	?>
	@foreach($this->items as $row)
	<tr>
		<td>
			@jhtml('FEFHelp.browse.id', ++$i, $row->getId())
		</td>
		<td>
			{{ \Akeeba\AdminTools\Admin\Helper\Html::localisedDate($row->logdate, 'Y-m-d H:i:s T', false) }}
		</td>
		<td>
			<a href="{{ str_replace('{ip}', urlencode($row->ip), $iplink) }}" target="_blank" class="akeeba-btn--small">
				<span class="akion-search"></span>
			</a>&nbsp;
			@if ($row->block)
				<a class="akeeba-btn--green--small"
				   href="index.php?option=com_admintools&view=SecurityExceptions&task=unban&id={{ $row->id }}&@token=1"
				   title="@lang('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_UNBAN')">
					<span class="akion-minus"></span>
				</a>&nbsp;
			@else
				<a class="akeeba-btn--red--small"
				   href="index.php?option=com_admintools&view=SecurityExceptions&task=ban&id={{ $row->id }}&@token=1"
				   title="@lang('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_BAN')">
					<span class="akion-flag"></span>
				</a>&nbsp;
			@endif
			{{{ $row->ip }}}
		</td>
		<td>
			@lang('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_REASON_' . $row->reason)
			@if ($row->extradata)
				<?php [$moreinfo, $techurl] = explode('|', $row->extradata . ((stristr($row->extradata, '|') === false) ? '|' : '')) ?>
				&nbsp;
				@jhtml('tooltip', strip_tags(htmlspecialchars($moreinfo, ENT_COMPAT, 'UTF-8')), '', 'tooltip.png', '', $techurl)
			@endif
		</td>
		<td>
			{{{ $row->url }}}
		</td>
	</tr>
	@endforeach
@stop