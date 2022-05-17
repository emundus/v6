<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var \Akeeba\AdminTools\Admin\View\BadWords\Html $this */

/** @var \Akeeba\AdminTools\Admin\Model\BadWords $item */
$item = $this->getItem();
?>
@extends('any:lib_fof40/Common/edit')

@section('edit-form-body')
    <div class="akeeba-container--50-50">
        <div>
            <div class="akeeba-form-group">
                <label for="word">@lang('COM_ADMINTOOLS_LBL_BADWORD_WORD')</label>

                <input type="text" name="word" id="word" value="{{{ $item->word }}}" />
            </div>
        </div>
    </div>
@stop