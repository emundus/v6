<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var $this Akeeba\AdminTools\Admin\View\WAFBlacklistedRequests\Html */

/** @var \Akeeba\AdminTools\Admin\Model\WAFBlacklistedRequests $item */
$item = $this->getItem();
?>
@js('admin://components/com_admintools/media/js/Wafblacklist.min.js', $this->getContainer()->mediaVersion, 'text/javascript', true)

@extends('any:lib_fof40/Common/edit')

@section('edit-form-body')
	<div class="akeeba-container--66-33">
		<div>
			<div class="akeeba-form-group">
				<label for="dest">
					@lang('JPUBLISHED')
				</label>

				@jhtml('FEFHelp.select.booleanswitch', 'enabled', $item->enabled)
				<p class="akeeba-help-text">
					@lang('COM_ADMINTOOLS_REDIRECTIONS_FIELD_PUBLISHED_DESC')
				</p>
			</div>

			<div class="akeeba-form-group">
				<label for="application">
					@lang('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_APPLICATION')
				</label>

				{{ \Akeeba\AdminTools\Admin\Helper\Select::wafApplication('application', null, $item->application) }}
				<p class="akeeba-help-text">
					@lang('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_APPLICATION_DESC')
				</p>
			</div>

			<div class="akeeba-form-group">
				<label for="verb">
					@lang('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_VERB')
				</label>

				{{ \Akeeba\AdminTools\Admin\Helper\Select::httpVerbs('verb', null, $item->verb) }}
				<p class="akeeba-help-text">
					@lang('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_VERB_TIP')
				</p>
			</div>

			<div class="akeeba-form-group">
				<label for="foption">
					@lang('COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_OPTION')
				</label>

				<input type="text" name="foption" id="foption" value="{{{ $item->option }}}" />
				<p class="akeeba-help-text">
					@lang('COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_OPTION_TIP')
				</p>
			</div>

			<div class="akeeba-form-group">
				<label for="fview">
					@lang('COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_VIEW')
				</label>

				<input type="text" name="fview" id="fview" value="{{{ $item->view }}}" />

				<p class="akeeba-help-text">
					@lang('COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_VIEW_TIP')
				</p>
			</div>

			<div class="akeeba-form-group">
				<label for="ftask">
					@lang('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_TASK')
				</label>

				<input type="text" name="ftask" id="ftask" value="{{{ $item->ftask }}}" />

				<p class="akeeba-help-text">
					@lang('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_TASK_TIP')
				</p>
			</div>

			<div class="akeeba-form-group">
				<label for="query_type">
					@lang('COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_QUERY')
				</label>

				{{ \Akeeba\AdminTools\Admin\Helper\Select::queryParamType('query_type', null, $item->query_type) }}
			</div>

			<div class="akeeba-form-group" id="fquery_container">
				<label for="fquery">
					@lang('COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_QUERY')
				</label>

				<input type="text" name="fquery" id="fquery" value="{{{ $item->query }}}" />

				<p class="akeeba-help-text">
					@lang('COM_ADMINTOOLS_LBL_EXCEPTIONSFROMWAF_QUERY_TIP')
				</p>
			</div>

			<div class="akeeba-form-group">
				<label for="query_content">
					@lang('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_QUERY_CONTENT')
				</label>

				<input type="text" name="query_content" id="query_content"
					   value="{{{ $item->query_content }}}" />

				<p class="akeeba-help-text">
					@lang('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_QUERY_CONTENT_TIP')
				</p>
			</div>
		</div>
	</div>
@stop