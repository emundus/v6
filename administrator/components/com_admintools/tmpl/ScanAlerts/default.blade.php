<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var $this Akeeba\AdminTools\Admin\View\ScanAlerts\Html */

defined('_JEXEC') || die;

$scan_id = $this->getModel()->getState('scan_id', '');

/** @var \Akeeba\AdminTools\Site\Model\Scans $scanModel */
$scanModel = $this->getContainer()->factory->model('Scans')->tmpInstance();
$scanModel->find($scan_id);

$returnUrl = \Joomla\CMS\Router\Route::_('index.php?option=com_admintools&view=ScanAlerts&id=' . $scan_id);
?>
@extends('any:lib_fof40/Common/browse')

@section('browse-page-top')
	<div class="akeeba-container--50-50">
		<div></div>
		<div>
			<a href="index.php?option=com_admintools&view=Scans&task=edit&id={{ $scan_id }}&returnurl={{ base64_encode($returnUrl) }}"
			   class="akeeba-btn--green--small" style="float:right">
				<span class="icon-pencil"></span>
				@lang('COM_ADMINTOOLS_SCANALERTS_EDIT_COMMENT')
			</a>
			<span id="showComment" class="akeeba-btn--primary--small" style="margin-right: 10px;float:right">
				<span class="icon-comments icon-white"></span>
				@lang('COM_ADMINTOOLS_SCANALERTS_SHOWCOMMENT')
        	</span>
			<div style="clear: both;"></div>
			<div id="comment" style="display:none">
				<p class="akeeba-panel--information" style="margin:5px 0 0">
					{{ $scanModel->comment }}
				</p>
			</div>
		</div>
	</div>
@stop

@section('browse-filters')
	<div class="akeeba-filter-element akeeba-form-group">
		{{ \Akeeba\AdminTools\Admin\Helper\Select::scanresultstatus('status', $this->filters['status'], ['onchange' => 'document.adminForm.submit()']) }}
	</div>

	<div class="akeeba-filter-element akeeba-form-group">
		{{ \Akeeba\AdminTools\Admin\Helper\Select::markedsafe('acknowledged', $this->filters['acknowledged'], ['onchange' => 'document.adminForm.submit()']) }}
	</div>


	<div class="akeeba-filter-element akeeba-form-group">
		@searchfilter('path', null, 'COM_ADMINTOOLS_LBL_SCANALERTS_PATH')
	</div>
@stop

@section('browse-table-header')
	<tr>
		<th width="32">
			@jhtml('FEFHelp.browse.checkall')
		</th>
		<th>
			@sortgrid('path', 'COM_ADMINTOOLS_LBL_SCANALERTS_PATH')
		</th>
		<th>
			@sortgrid('filestatus', 'COM_ADMINTOOLS_LBL_SCANALERTS_STATUS')
		</th>
		<th>
			@sortgrid('threat_score', 'COM_ADMINTOOLS_LBL_SCANALERTS_THREAT_SCORE')
		</th>
		<th>
			@sortgrid('acknowledged', 'COM_ADMINTOOLS_LBL_SCANALERTS_ACKNOWLEDGED')
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
	/** @var \Akeeba\AdminTools\Admin\Model\ScanAlerts $row */
	?>
	@foreach($this->items as $row)
		<tr>
			<td>
				@jhtml('FEFHelp.browse.id', ++$i, $row->getId())
			</td>
			<td>
				<a href="index.php?option=com_admintools&view=ScanAlerts&task=edit&id={{ (int)($row->admintools_scanalert_id) }}" title="{{{ (strlen($row->path) > 100) ? $row->path : '' }}}">
					@if (strlen($row->path) > 100)
						&hellip;
						{{{ substr($row->path, -100) }}}
					@else
						{{{ $row->path }}}
					@endif
				</a>
			</td>
			<td>
				@if($row->newfile)
				<span class="admintools-scanfile-new {{ $row->threat_score ? '' : 'admintools-scanfile-nothreat' }}">
					@lang('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS_NEW')
				</span>
				@elseif($row->suspicious)
				<span class="admintools-scanfile-suspicious {{ $row->threat_score ? '' : 'admintools-scanfile-nothreat' }}">
					@lang('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS_SUSPICIOUS')
				</span>
				@else
				<span class="admintools-scanfile-modified {{ $row->threat_score ? '' : 'admintools-scanfile-nothreat' }}">
					@lang('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS_MODIFIED')
				</span>
				@endif
			</td>
			<td>
				<?php
				$threatindex = 'high';

				if ($row->threat_score == 0)
				{
					$threatindex = 'none';
				}
				elseif ($row->threat_score < 10)
				{
					$threatindex = 'low';
				}
				elseif ($row->threat_score < 100)
				{
					$threatindex = 'medium';
				}
				?>
				<span class="admintools-scanfile-threat-{{ $threatindex }}">
					<span class="admintools-scanfile-pic">&nbsp;</span>
					{{ $row->threat_score }}
				</span>
			</td>
			<td>
				@jhtml('FEFHelp.browse.published', $row->acknowledged, $i, '', $this->getContainer()->platform->getUser()->authorise('core.edit.state', 'com_admintools'), 'cb')
			</td>
		</tr>
	@endforeach
@stop

@section('browse-default-hidden-fields')
<input type="hidden" name="option" id="option" value="{{{ $this->getContainer()->componentName }}}"/>
<input type="hidden" name="view" id="view" value="{{{ $this->getName() }}}"/>
<input type="hidden" name="boxchecked" id="boxchecked" value="0"/>
<input type="hidden" name="task" id="task" value="{{{ $this->getTask() }}}"/>
<input type="hidden" name="filter_order" id="filter_order" value="{{{ $this->lists->order }}}"/>
<input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="{{{ $this->lists->order_Dir }}}"/>
<input type="hidden" name="@token()" value="1"/>
<input type="hidden" name="scan_id" id="scan_id" value="{{{ $this->getModel()->getState('scan_id', '') }}}"/>
@overwrite