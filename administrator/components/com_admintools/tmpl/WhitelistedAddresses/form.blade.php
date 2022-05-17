<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var Akeeba\AdminTools\Admin\View\WhitelistedAddresses\Html $this */
?>
@extends('any:lib_fof40/Common/edit')

@section('edit-page-top')
	<div class="akeeba-block--info">
		<p>@lang('COM_ADMINTOOLS_LBL_WHITELISTEDADDRESS_IP_INTRO')</p>
		<ol>
			<li>@lang('COM_ADMINTOOLS_LBL_WHITELISTEDADDRESS_IP_OPT1')</li>
			<li>@lang('COM_ADMINTOOLS_LBL_WHITELISTEDADDRESS_IP_OPT2')</li>
			<li>@lang('COM_ADMINTOOLS_LBL_WHITELISTEDADDRESS_IP_OPT3')</li>
			<li>@lang('COM_ADMINTOOLS_LBL_WHITELISTEDADDRESS_IP_OPT4')</li>
			<li>@lang('COM_ADMINTOOLS_LBL_WHITELISTEDADDRESS_IP_OPT5')</li>
			<li>@lang('COM_ADMINTOOLS_LBL_WHITELISTEDADDRESS_IP_OPT6')</li>
		</ol>

		<p>
			@lang('COM_ADMINTOOLS_LBL_WHITELISTEDADDRESS_YOURIP')
			<code>{{{ $this->myIP }}}</code>
		</p>
	</div>
@stop

@section('edit-form-body')
	<div class="akeeba-container--50-50">
		<div>
			<div class="akeeba-form-group">
				<label for="ip">
					@lang('COM_ADMINTOOLS_LBL_WHITELISTEDADDRESS_IP')
				</label>

				<input type="text" name="ip" id="ip" value="{{{ $this->item->ip }}}" />
			</div>

			<div class="akeeba-form-group">
				<label for="description">
					@lang('COM_ADMINTOOLS_LBL_WHITELISTEDADDRESS_DESCRIPTION')
				</label>

				<input type="text" name="description" id="description"
					   value="{{{ $this->item->description }}}" />
			</div>
		</div>
	</div>
@stop