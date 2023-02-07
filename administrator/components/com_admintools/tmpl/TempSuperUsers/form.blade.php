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
    @if(empty($this->item->user))
        <div class="akeeba-panel--danger">
            <header class="akeeba-block-header">
                <h3>
                    @lang('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_INVALIDUSER')
                </h3>
            </header>
            <p>
                @lang('COM_ADMINTOOLS_TEMPSUPERUSERS_LBL_INVALIDUSER_TIP')
            </p>
        </div>
    @else
        <div class="akeeba-container--50-50">
            <div>
                <div class="akeeba-form-group">
                    <label for="dummy">
                        @lang('COM_ADMINTOOLS_LBL_TEMPSUPERUSER_EDITINGUSER')
                    </label>
                    <p>
                        <strong>{{ $this->item->user->username }}</strong><br />
                        {{ $this->item->user->name }}
                        <em> ({{ $this->item->user->email }}) </em>
                    </p>
                </div>
                <div class="akeeba-form-group">
                    <label for="expiration">
                        @lang('COM_ADMINTOOLS_TEMPSUPERUSERS_FIELD_EXPIRATION')
                    </label>
                    @if (version_compare(JVERSION, '3.999.999', 'le'))
                        @jhtml('calendar', $this->item->expiration, 'expiration', 'expiration', '%Y-%m-%d %H:%M', [
                            'class'    => 'input-small',
                            'showTime' => true,
                    ])
                    @else
                        <input
                                type="datetime-local"
                                pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}"
                                name="expiration"
                                id="expiration"
                                value="{{{ $this->item->expiration }}}"
                        >
                    @endif
                </div>
            </div>
        </div>
    @endif
@stop