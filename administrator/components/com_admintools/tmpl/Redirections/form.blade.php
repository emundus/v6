<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var $this Akeeba\AdminTools\Admin\View\Redirections\Html */

defined('_JEXEC') || die;

$baseUri = \Joomla\CMS\Uri\Uri::base();

if (substr($baseUri, -14) == 'administrator/')
{
	$baseUri = substr($baseUri, 0, -14);
}
?>
@extends('any:lib_fof40/Common/edit')

@section('edit-form-body')
	<div class="akeeba-container--66-33">
		<div>
			<div class="akeeba-form-group">
				<label for="dest">
					@lang('COM_ADMINTOOLS_LBL_REDIRECTION_DEST')
				</label>
				<div class="akeeba-input-group">
					<span>{{ $baseUri }}</span>
					<input type="text" name="dest" id="dest" value="{{{ $this->item->dest }}}" />
				</div>
				<p class="akeeba-help-text">
					@lang('COM_ADMINTOOLS_REDIRECTIONS_FIELD_DEST_DESC')
				</p>
			</div>

			<div class="akeeba-form-group">
				<label for="source">
					@lang('COM_ADMINTOOLS_LBL_REDIRECTION_SOURCE')
				</label>
				<input type="text" name="source" id="source"
					   value="{{{ $this->item->source }}}" />
				<p class="akeeba-help-text">
					@lang('COM_ADMINTOOLS_REDIRECTIONS_FIELD_SOURCE_DESC')
				</p>
			</div>

			<div class="akeeba-form-group">
				<label for="keepurlparams">
					@lang('COM_ADMINTOOLS_REDIRECTIONS_FIELD_KEEPURLPARAMS')
				</label>

				{{ \Akeeba\AdminTools\Admin\Helper\Select::keepUrlParamsList('keepurlparams', null, $this->item->keepurlparams) }}

				<p class="akeeba-help-text">
					@lang('COM_ADMINTOOLS_REDIRECTIONS_FIELD_KEEPURLPARAMS_DESC')
				</p>
			</div>

			<div class="akeeba-form-group">
				<label for="dest">
					@lang('JPUBLISHED')
				</label>

				@jhtml('FEFHelp.select.booleanswitch', 'published', $this->item->published)
				<p class="akeeba-help-text">
					@lang('COM_ADMINTOOLS_REDIRECTIONS_FIELD_PUBLISHED_DESC')
				</p>
			</div>
		</div>
	</div>
@stop