<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var $this \Akeeba\AdminTools\Admin\View\WAFEmailTemplates\Html */
?>
@extends('any:lib_fof40/Common/browse')

@section('browse-filters')
	<div class="akeeba-filter-element akeeba-form-group">
		@searchfilter('reason', null, 'COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_REASON_SELECT')
	</div>

	<div class="akeeba-filter-element akeeba-form-group">
		{{ \Akeeba\AdminTools\Admin\Helper\Select::published($this->filters['enabled'], 'enabled', ['onchange' => 'document.adminForm.submit()']) }}
	</div>

	<div class="akeeba-filter-element akeeba-form-group">
		{{ \Akeeba\AdminTools\Admin\Helper\Select::languages('language', $this->filters['language'], ['onchange' => 'document.adminForm.submit()']) }}
	</div>
@stop

@section('browse-table-header')
	<tr>
		<th width="32">
			@jhtml('FEFHelp.browse.checkall')
		</th>
		<th style="width: 130px;">
			@sortgrid('reason', 'COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_REASON')
		</th>
		<th>
			@sortgrid('subject', 'COM_ADMINTOOLS_WAFEMAILTEMPLATES_FIELD_SUBJECT_LBL')
		</th>
		<th style="width:8%">
			@sortgrid('enabled', 'JPUBLISHED')
		</th>
		<th style="width: 20%">
			@sortgrid('language', 'COM_ADMINTOOLS_COMMON_LANGUAGE')
		</th>
	</tr>
@stop

@section('browse-table-body-norecords')
	{{-- Table body shown when no records are present. --}}
	<tr>
		<td colspan="99">
			@lang('COM_ADMINTOOLS_MSG_COMMON_NOITEMS')
		</td>
	</tr>
@stop

@section('browse-table-body-withrecords')
	<?php
	$i = 0;
	/** @var \Akeeba\AdminTools\Admin\Model\BadWords $row */
	?>
	@foreach($this->items as $row)
		<?php
		$edit = 'index.php?option=com_admintools&view=WAFEmailTemplates&task=edit&id=' . $row->admintools_waftemplate_id;
		$enabled = $this->container->platform->getUser()->authorise('core.edit.state', 'com_admintools')
		?>
		<tr>
			<td>
				@jhtml('FEFHelp.browse.id', ++$i, $row->getId())
			</td>
			<td>
				<a href="index.php?option=com_admintools&view=WAFEmailTemplates&task=edit&id={{{ $row->getId() }}}">
					{{ $row->reason }}
				</a>
			</td>
			<td>
				<a href="index.php?option=com_admintools&view=WAFEmailTemplates&task=edit&id={{{ $row->getId() }}}">
					{{ $row->subject }}
				</a>
			</td>
			<td>
				@jhtml('FEFHelp.browse.published', $row->enabled ?? 0, $i, '', $this->container->platform->getUser()->authorise('core.edit.state', 'com_admintools'))
			</td>
			<td>
				{{ \Akeeba\AdminTools\Admin\Helper\Html::language($row->language) }}
			</td>
		</tr>
	@endforeach
@stop
