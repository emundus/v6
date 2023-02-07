<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var \Akeeba\AdminTools\Admin\View\BadWords\Html $this */

?>
@extends('any:lib_fof40/Common/browse')

@section('browse-page-top')
    @include('admin:com_admintools/ControlPanel/plugin_warning')
@stop

@section('browse-filters')
    <div class="akeeba-filter-element akeeba-form-group">
        @searchfilter('word', null, 'COM_ADMINTOOLS_LBL_BADWORD_WORD')
    </div>
@stop


@section('browse-table-header')
    <tr>
        <th width="32">
            @jhtml('FEFHelp.browse.checkall')
        </th>
        <th>
            @sortgrid('word', 'COM_ADMINTOOLS_LBL_BADWORD_WORD')
        </th>
    </tr>
@stop

@section('browse-table-body-withrecords')
	<?php
	$i = 0;
	/** @var \Akeeba\AdminTools\Admin\Model\BadWords $row */
	?>
    @foreach($this->items as $row)
    <tr>
        <td>
            @jhtml('FEFHelp.browse.id', ++$i, $row->getId())
        </td>
        <td>
            <a href="index.php?option=com_admintools&view=BadWords&task=edit&id={{ $row->getId() }}">
                {{{ $row->word }}}
            </a>
        </td>
    </tr>
    @endforeach
@stop

@section('browse-table-body-norecords')
    {{-- Table body shown when no records are present. --}}
    <tr>
        <td colspan="99">
            @lang('COM_ADMINTOOLS_ERR_BADWORD_NOITEMS')
        </td>
    </tr>
@stop