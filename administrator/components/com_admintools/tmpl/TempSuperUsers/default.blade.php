<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var $this \Akeeba\AdminTools\Admin\View\TempSuperUsers\Html */
?>
@extends('any:lib_fof40/Common/browse')

@section('browse-filters')
	<div class="akeeba-filter-element akeeba-form-group">
		@searchfilter('username', null, 'COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_USERNAME')
	</div>
@stop

@section('browse-table-header')
	<tr>
		<th width="32">
			@jhtml('FEFHelp.browse.checkall')
		</th>
		<th>
			@sortgrid('user_id', 'COM_ADMINTOOLS_TEMPSUPERUSERS_FIELD_USER_ID')
		</th>
		<th>
			@lang('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_USERNAME')
		</th>
		<th>
			@sortgrid('expiration', 'COM_ADMINTOOLS_TEMPSUPERUSERS_FIELD_EXPIRATION')
		</th>
	</tr>
@stop

@section('browse-table-body-norecords')
	{{-- Table body shown when no records are present. --}}
	<tr>
		<td colspan="99">
			@lang('COM_ADMINTOOLS_ERR_TEMPSUPERUSERS_NOITEMS')
		</td>
	</tr>
	<tr>
		<td colspan="6">
			<a href="index.php?option=com_admintools&view=TempSuperUsers&task=add"
			   class="akeeba-btn--green--big">
				<span class="akion-android-person-add"></span>
				@lang('COM_ADMINTOOLS_BTN_TEMPSUPERUSERS_ADD')
			</a>
		</td>
	</tr>
@stop

@section('browse-table-body-withrecords')
	<?php
	$i = 0;
	/** @var \Akeeba\AdminTools\Admin\Model\TempSuperUsers $row */
	?>
	@foreach($this->items as $row)
	<tr>
		<td>
			@jhtml('FEFHelp.browse.id', ++$i, $row->getId())
		</td>
		<td>
			<a href="index.php?option=com_admintools&view=TempSuperUsers&task=edit&id={{ (int)($row->user_id) }}">
				{{{ $row->user_id }}}
			</a>
		</td>
		<td>
			@if(empty($row->user))
				<span class="akeeba-label--red hasPopover"
					  data-title="@lang('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_INVALIDUSER')"
					  data-content="@lang('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_INVALIDUSER_TIP')">
					@lang('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_INVALIDUSER')
				</span>
			@else
				<strong>
					<a href="index.php?option=com_admintools&view=TempSuperUsers&task=edit&id={{ $row->user_id }}">
						{{ $row->user->username }}
					</a>
				</strong>
				<br />
				<small>
					<a href="index.php?option=com_users&task=user.edit&id={{ $row->user_id }}"
					   target="_blank">
						{{ $row->user->name }} <em>{{{ $row->user->email }}}</em>
					</a>
				</small>
			@endif
		</td>
		<td>
			{{ \Akeeba\AdminTools\Admin\Helper\Html::localisedDate($row->expiration) }}
		</td>
	</tr>
	@endforeach
@stop
