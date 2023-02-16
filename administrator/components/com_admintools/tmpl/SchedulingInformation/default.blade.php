<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die();

/** @var $this Akeeba\AdminTools\Admin\View\SchedulingInformation\Html */

?>
<h2>
	@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_RUN_FILESCANNER')
</h2>

<p>
	@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_HEADERINFO')
</p>

<div class="akeeba-panel--information">
	<header class="akeeba-block-header">
		<h3>@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_CLICRON')</h3>
	</header>

	<p class="akeeba-block--info">
		@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_CLICRON_INFO')
		<br />
		<a class="akeeba-btn--primary--small"
		   href="https://www.akeeba.com/documentation/admin-tools/php-file-scanner-cron.html" target="_blank">
			@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_GENERICREADDOC')
		</a>
	</p>
	<p>
		@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_GENERICUSECLI')
		<code>
			{{{ $this->croninfo->info->php_path }}}
			{{{ $this->croninfo->cli->path }}}
		</code>
	</p>
	<p>
		<span class="akeeba-label--red">@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_CLIGENERICIMPROTANTINFO')</span>
		@sprintf('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_CLIGENERICINFO', $this->croninfo->info->php_path)
	</p>
</div>

<div class="akeeba-panel--information">
	<header class="akeeba-block-header">
		<h3>@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTENDBACKUP')</h3>
	</header>

	<p class="akeeba-block--info">
		@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTENDBACKUP_INFO')
		<br />
		<a class="akeeba-btn--primary--small"
		   href="https://www.akeeba.com/documentation/admin-tools/php-file-scanner-frontend.html" target="_blank">
			@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_GENERICREADDOC')
		</a>
	</p>
	@if(!$this->croninfo->info->feenabled)
		<p class="akeeba-block--failure">
			@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_DISABLED')
		</p>
	@elseif(!trim($this->croninfo->info->secret))
		<p class="akeeba-block--failure">
			@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_SECRET')
		</p>
	@else
		<p>
			@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTENDBACKUP_MANYMETHODS')
		</p>

		<h4>@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTENDBACKUP_TAB_WEBCRON')</h4>
		<p>
			@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON')
		</p>

		<table class="table table-striped">
			<tr>
				<td></td>
				<td>
					@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_INFO')
				</td>
			</tr>
			<tr>
				<td>
					@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_NAME')
				</td>
				<td>
					@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_NAME_INFO')
				</td>
			</tr>
			<tr>
				<td>
					@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_TIMEOUT')
				</td>
				<td>
					@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_TIMEOUT_INFO')
				</td>
			</tr>
			<tr>
				<td>
					@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_URL')
				</td>
				<td>
					{{{ $this->croninfo->info->root_url }}}/{{{ $this->croninfo->frontend->path }}}
				</td>
			</tr>
			<tr>
				<td>
					@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_LOGIN')
				</td>
				<td>
					@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_LOGINPASSWORD_INFO')
				</td>
			</tr>
			<tr>
				<td>
					@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_PASSWORD')
				</td>
				<td>
					@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_LOGINPASSWORD_INFO')
				</td>
			</tr>
			<tr>
				<td>
					@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_EXECUTIONTIME')
				</td>
				<td>
					@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_EXECUTIONTIME_INFO')
				</td>
			</tr>
			<tr>
				<td>
					@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_ALERTS')
				</td>
				<td>
					@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_ALERTS_INFO')
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WEBCRON_THENCLICKSUBMIT')
				</td>
			</tr>
		</table>

		<h4>@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTENDBACKUP_TAB_WGET')</h4>
		<p>
			@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_WGET')
			<code>
				wget --max-redirect=10000
				"{{{ $this->croninfo->info->root_url }}}/{{{ $this->croninfo->frontend->path }}}"
				-O - 1>/dev/null 2>/dev/null
			</code>
		</p>

		<h4>@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTENDBACKUP_TAB_CURL')</h4>
		<p>
			@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_CURL')
			<code>
				curl -L --max-redirs 1000 -v
				"{{{ $this->croninfo->info->root_url }}}/{{{ $this->croninfo->frontend->path }}}"
				1>/dev/null 2>/dev/null
			</code>
		</p>

		<h4>@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTENDBACKUP_TAB_SCRIPT')</h4>
		<p>
			@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_CUSTOMSCRIPT')
		</p>
		<pre>
{{ '&lt;?php' }}
	$curl_handle=curl_init();
	curl_setopt($curl_handle, CURLOPT_URL, '"{{{ $this->croninfo->info->root_url }}}/{{{ $this->croninfo->frontend->path }}}"');
	curl_setopt($curl_handle,CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($curl_handle,CURLOPT_MAXREDIRS, 10000);
	curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER, 1);
	$buffer = curl_exec($curl_handle);
	curl_close($curl_handle);
	if (empty($buffer))
		echo "Sorry, the scan didn't work.";
	else
		echo $buffer;
{{ '?&gt;' }}
		</pre>

		<h4>@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTENDBACKUP_TAB_URL')</h4>
		<p>
			@lang('COM_ADMINTOOLS_LBL_SCHEDULINGINFORMATION_FRONTEND_RAWURL')
			<code>
				{{{ $this->croninfo->info->root_url }}}/{{{ $this->croninfo->frontend->path }}}
			</code>
		</p>

	@endif
</div>
