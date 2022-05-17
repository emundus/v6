<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var $this Akeeba\AdminTools\Admin\View\WAFEmailTemplates\Html */

/** @var Akeeba\AdminTools\Admin\Model\WAFEmailTemplates $item */
$item = $this->getItem();
?>
@extends('any:lib_fof40/Common/edit')

@section('edit-form-body')
	<div class="akeeba-form-group">
		<label for="key_field">
			@lang('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_REASON_SELECT')
		</label>

		{{ \Akeeba\AdminTools\Admin\Helper\Select::reasons('reason', $item->reason, ['all' => 1, 'misc' => 1]) }}
	</div>

	<div class="akeeba-form-group">
		<label for="subject_field">
			@lang('COM_ADMINTOOLS_WAFEMAILTEMPLATES_FIELD_SUBJECT_LBL')
		</label>

		<input type="text" id="subject_field" name="subject" value="{{{ $item->subject }}}" />
		<span class="akeeba-help-text">@lang('COM_ADMINTOOLS_WAFEMAILTEMPLATES_FIELD_SUBJECT_DESC')</span>
	</div>

	<div class="akeeba-form-group">
		<label for="enabled">
			@lang('JPUBLISHED')
		</label>

		@jhtml('FEFHelp.select.booleanswitch', 'enabled', $item->enabled)
	</div>

	<div class="akeeba-form-group">
		<label for="language">
			@lang('COM_ADMINTOOLS_WAFEMAILTEMPLATES_FIELD_LANGUAGE_LBL')
		</label>

		{{ \Akeeba\AdminTools\Admin\Helper\Select::languages('language', $item->language) }}

		<span class="akeeba-help-text">@lang('COM_ADMINTOOLS_WAFEMAILTEMPLATES_FIELD_LANGUAGE_DESC')</span>
	</div>

	<div class="akeeba-form-group">
		<label for="language">
			@lang('COM_ADMINTOOLS_WAFEMAILTEMPLATES_FIELD_SENDLIMIT_LBL')
		</label>

		<div>
			<input class="input-mini" type="text" size="5" name="email_num"
				   value="{{ (int) $item->email_num }}" />
			<span>@lang('COM_ADMINTOOLS_WAFEMAILTEMPLATES_NUMFREQ')</span>
			<input class="input-mini" type="text" size="5" name="email_numfreq"
				   value="{{ (int) $item->email_numfreq }}" />
			{{ \Akeeba\AdminTools\Admin\Helper\Select::trsfreqlist('email_freq', ['class' => 'input-small'], $item->email_freq) }}

			<span class="akeeba-help-text">@lang('COM_ADMINTOOLS_WAFEMAILTEMPLATES_FIELD_SENDLIMIT_DESC')</span>
		</div>
	</div>

	<div class="akeeba-form-group">
		<label for="template">
			@lang('COM_ADMINTOOLS_WAFEMAILTEMPLATES_FIELD_TEMPLATE_LBL')
		</label>

		@editor('template', $item->template, '97%', '391', '50', '20', false)

		<span class="akeeba-help-text">@lang('COM_ADMINTOOLS_WAFEMAILTEMPLATES_FIELD_TEMPLATE_DESC')</span>
		<span class="akeeba-help-text">@lang('COM_ADMINTOOLS_WAFEMAILTEMPLATES_FIELD_TEMPLATE_DESC_2')</span>
	</div>
@stop
