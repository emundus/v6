<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

$scan_id  = $this->input->getInt('scan_id', 0);
$date     = new FOF40\Date\Date($this->scan->scanstart);
$timezone = $this->container->platform->getUser()->getParam(
		'timezone', $this->container->platform->getConfig()->get('offset', 'GMT')
);
$tz       = new DateTimeZone($timezone);

$date->setTimezone($tz);
?>
<h1>
	@sprintf('COM_ADMINTOOLS_TITLE_SCANALERTS', $scan_id)
</h1>
<h2>
	{{ $date->format(\Joomla\CMS\Language\Text::_('DATE_FORMAT_LC2') . ' T', true) }}
</h2>

<table class="table">
	<thead>
	<tr>
		<th width="10%"></th>
		<th>
			@lang('COM_ADMINTOOLS_LBL_SCANALERTS_PATH')
		</th>
		<th width="50%">
			@lang('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS')
		</th>
		<th width="20%">
			@lang('COM_ADMINTOOLS_LBL_SCANALERTS_THREAT_SCORE')
		</th>
		<th width="40%">
			@lang('COM_ADMINTOOLS_LBL_SCANALERTS_ACKNOWLEDGED')
		</th>
	</tr>
	</thead>
	<tbody>
	@if(count($this->items) > 0)
		<?php $i = 0; ?>
		@foreach ($this->items as $row)
		<tr>
			<td>
				{{ ++$i }}
			</td>
			<td>
				@if (strlen($row->path) > 100)
				&hellip;
				{{{ substr($row->path, -100) }}}
				@else
					{{{ $row->path }}}
				@endif
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
				@if($row->acknowledged)
					<span class="admintools-scanfile-markedsafe">
						@lang('JYES')
					</span>
				@else
					@lang('JNO')
				@endif
			</td>
		</tr>
		@endforeach
	@else
		<tr>
			<td colspan="20" align="center">@lang('COM_ADMINTOOLS_MSG_COMMON_NOITEMS')</td>
		</tr>
	@endif
	</tbody>
</table>
