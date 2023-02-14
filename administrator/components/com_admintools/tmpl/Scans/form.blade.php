<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var Akeeba\AdminTools\Admin\View\Scans\Html $this */
?>
@extends('any:lib_fof40/Common/edit')

@section('edit-form-body')
	<div>
		<div class="akeeba-form-group">
			<label for="comment">
				@lang('COM_ADMINTOOLS_SCANS_EDIT_COMMENT')
			</label>

			@editor('comment', $this->item->comment, '100%', '350', '50', '20', false)
		</div>
	</div>
@stop