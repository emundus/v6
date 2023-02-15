<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var Akeeba\AdminTools\Admin\View\TempSuperUsers\Html $this */
?>
@extends('any:lib_fof40/Common/edit')

@section('edit-form-body')
	<section class="akeeba-panel--information">
		<header class="akeeba-block-header">
			<h3>@lang('COM_ADMINTOOLS_TEMPSUPERUSERS_FIELD_EXPIRATION')</h3>
		</header>

		<div class="akeeba-form-group">
			<label for="expiration">
				@lang('COM_ADMINTOOLS_TEMPSUPERUSERS_FIELD_EXPIRATION')
			</label>

			@if (version_compare(JVERSION, '3.999.999', 'le'))
				@jhtml('calendar', empty($this->item->expiration) ? $this->userInfo['expiration'] : $this->item->expiration, 'expiration', 'expiration', '%Y-%m-%d %H:%M', [
					'class'    => 'input-small',
					'showTime' => true,
			])
			@else
				<input
						type="datetime-local"
						pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}"
						name="expiration"
						id="expiration"
						value="{{{ empty($this->item->expiration) ? $this->userInfo['expiration'] : $this->item->expiration }}}"
				>
			@endif
		</div>
	</section>

	<section class="akeeba-panel--information">
		<header class="akeeba-block-header">
			<h3>
				@lang('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_USERINFO')
			</h3>
		</header>

		<div class="akeeba-form-group">
			<label for="username">
				@lang('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_USERNAME')
			</label>

			<input type="text" name="username" value="{{ $this->userInfo['username'] }}" />
		</div>

		<div class="akeeba-form-group">
			<label for="password">
				@lang('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_PASSWORD')
			</label>

			<input type="text" name="password" value="{{ $this->userInfo['password'] }}" />
		</div>

		<div class="akeeba-form-group">
			<label for="password2">
				@lang('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_PASSWORD2')
			</label>

			<input type="text" name="password2" value="{{ $this->userInfo['password2'] }}" />
		</div>

		<div class="akeeba-form-group">
			<label for="email">
				@lang('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_EMAIL')
			</label>

			<input type="text" name="email" value="{{ $this->userInfo['email'] }}" />
		</div>

		<div class="akeeba-form-group">
			<label for="name">
				@lang('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_NAME')
			</label>

			<input type="text" name="name" value="{{ $this->userInfo['name'] }}" />
		</div>

		<div class="akeeba-form-group">
			<label for="groups">
				@lang('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_GROUPS')
			</label>

			@jhtml('access.usergroup', 'groups', $this->userInfo['groups'], [
					'multiple' => true,
					'size'     => 15,
			], false, 'groups')
		</div>
	</section>
@stop