<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var $this Akeeba\AdminTools\Admin\View\ExceptionsFromWAF\Html */
?>
@extends('any:lib_fof40/Common/edit')

@section('edit-form-body')
	<div class="akeeba-container--66-33">
		<div>
			<div class="akeeba-form-group">
				<label for="foption">
					@lang('COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_OPTION')
				</label>

				<input type="text" name="foption" id="foption"
					   value="{{{ $this->item->option }}}" />
				<p class="akeeba-help-text">
					@lang('COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_OPTION_TIP')
				</p>
			</div>

			<div class="akeeba-form-group">
				<label for="fview">
					@lang('COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_VIEW')
				</label>

				<input type="text" name="fview" id="fview" value="{{{ $this->item->view }}}" />

				<p class="akeeba-help-text">
					@lang('COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_VIEW_TIP')
				</p>
			</div>

			<div class="akeeba-form-group">
				<label for="fquery">
					@lang('COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_QUERY')
				</label>

				<input type="text" name="fquery" id="fquery" value="{{{ $this->item->query }}}" />

				<p class="akeeba-help-text">
					@lang('COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_QUERY_TIP')
				</p>
			</div>
		</div>
	</div>@stop
