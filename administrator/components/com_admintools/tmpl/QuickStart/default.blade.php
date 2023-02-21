<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var Akeeba\AdminTools\Admin\View\QuickStart\Html $this */

$formStyle    = $this->isFirstRun ? '' : 'display: none';
$warningStyle = $this->isFirstRun ? 'display: none' : '';
?>
@jhtml('formbehavior.chosen')

<div class="akeeba-block--failure" style="{{{ $warningStyle }}}" id="youhavebeenwarnednottodothat">
	<h4>
		@lang('COM_ADMINTOOLS_QUICKSTART_ALREADYCONFIGURED_HEAD')
	</h4>
	<p></p>
	<p>
		@lang('COM_ADMINTOOLS_QUICKSTART_ALREADYCONFIGURED_BODY')
	</p>
	<p></p>
	<p>
		<a href="index.php?option=com_admintools" class="akeeba-btn--green--large">
			<span class="akion-ios-home"></span>
			@lang('COM_ADMINTOOLS_QUICKSTART_ALREADYCONFIGURED_BTN_NO')
		</a>

		<a id="admintoolsQuickStartConfirmExecute"
		   class="akeeba-btn--red--small">
			<span class="akion-alert-circled"></span>
			@lang('COM_ADMINTOOLS_QUICKSTART_ALREADYCONFIGURED_BTN_YES')
		</a>
	</p>
</div>

<form name="adminForm" id="adminForm" action="index.php" method="post"
	  class="akeeba-form--horizontal"
	  style="{{{ $formStyle }}}">

	<div class="akeeba-block--info" style="{{{ $formStyle }}}">
		<p>
			@sprintf('COM_ADMINTOOLS_QUICKSTART_INTRO', 'https://www.akeeba.com/documentation/admin-tools.html')
		</p>
	</div>

	<div class="akeeba-block--failure" style="{{{ $warningStyle }}}">
		<h1>
			@lang('COM_ADMINTOOLS_QUICKSTART_ALREADYCONFIGURED_NOSUPPORT_HEAD')
		</h1>
		<p>
			@lang('COM_ADMINTOOLS_QUICKSTART_ALREADYCONFIGURED_NOSUPPORT_BODY')
		</p>
	</div>

	<div class="akeeba-panel--primary">
		<header class="akeeba-block-header">
			<h3>@lang('COM_ADMINTOOLS_QUICKSTART_HEAD_ADMINSECURITY')</h3>
		</header>

		<div class="akeeba-form-group">
			<label for="adminpw"
				   rel="akeeba-sticky-tooltip"
				   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ADMINPW')"
				   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ADMINPW_TIP')">
				@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ADMINPW')
			</label>

			<input type="text" size="20" name="adminpw"
				   value="{{{ $this->wafconfig['adminpw'] }}}" />
		</div>

		@if($this->hasHtaccess)
			<div class="akeeba-form-group">
				<label for="admin_username"
					   rel="akeeba-sticky-tooltip"
					   data-original-title="@lang('COM_ADMINTOOLS_TITLE_ADMINPW')"
					   data-content="@lang('COM_ADMINTOOLS_QUICKSTART_ADMINISTRATORPASSORD_INFO')">
					@lang('COM_ADMINTOOLS_TITLE_ADMINPW')
				</label>

				<div>
					<input type="text" name="admin_username" id="admin_username"
						   value="{{{ $this->admin_username }}}" autocomplete="off"
						   placeholder="@lang('COM_ADMINTOOLS_LBL_ADMINPASSWORD_USERNAME')"
					/>
					<input type="text" name="admin_password" id="admin_password"
						   value="{{{ $this->admin_password }}}" autocomplete="off"
						   placeholder="@lang('COM_ADMINTOOLS_LBL_ADMINPASSWORD_PASSWORD')"
					/>
				</div>
			</div>
		@endif

		<div class="akeeba-form-group">
			<label for="emailonadminlogin"
				   rel="akeeba-sticky-tooltip"
				   data-original-title="@lang('COM_ADMINTOOLS_QUICKSTART_ADMINLOGINEMAIL_LBL')"
				   data-content="@lang('COM_ADMINTOOLS_QUICKSTART_ADMINLOGINEMAIL_DESC')">
				@lang('COM_ADMINTOOLS_QUICKSTART_ADMINLOGINEMAIL_LBL')
			</label>

			<input type="text" size="20" name="emailonadminlogin" id="emailonadminlogin"
				   value="{{{ $this->wafconfig['emailonadminlogin'] }}}">
		</div>

		<div class="akeeba-form-group">
			<label for="ipwl"
				   rel="akeeba-sticky-tooltip"
				   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_IPWL')"
				   data-content="@sprintf('COM_ADMINTOOLS_QUICKSTART_WHITELIST_DESC', $this->escape($this->myIp))"
			>
				@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_IPWL')
			</label>

			@jhtml('FEFHelp.select.booleanswitch', 'ipwl', $this->wafconfig['ipwl'])
		</div>

		<div class="akeeba-form-group">
			<label for="nonewadmins"
				   rel="akeeba-sticky-tooltip"
				   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_NONEWADMINS')"
				   data-content="@lang('COM_ADMINTOOLS_QUICKSTART_NONEWADMINS_DESC')">
				@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_NONEWADMINS')
			</label>

			@jhtml('FEFHelp.select.booleanswitch', 'nonewadmins', $this->wafconfig['nonewadmins'])
		</div>

		<div class="akeeba-form-group">
			<label for="nofesalogin"
				   rel="akeeba-sticky-tooltip"
				   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_NOFESALOGIN')"
				   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_NOFESALOGIN_TIP')">
				@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_NOFESALOGIN')
			</label>

			@jhtml('FEFHelp.select.booleanswitch', 'nofesalogin', $this->wafconfig['nofesalogin'])
		</div>
	</div>

	<div class="akeeba-panel--primary">
		<header class="akeeba-block-header">
			<h3>@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPTGROUP_BASIC')</h3>
		</header>

		<div class="akeeba-form-group">
			<label for="enablewaf"
				   rel="akeeba-sticky-tooltip"
				   data-original-title="@lang('COM_ADMINTOOLS_QUICKSTART_ENABLEWAF_LBL')"
				   data-content="@lang('COM_ADMINTOOLS_QUICKSTART_ENABLEWAF_DESC')"
			>
				@lang('COM_ADMINTOOLS_QUICKSTART_ENABLEWAF_LBL')
			</label>

			@jhtml('FEFHelp.select.booleanswitch', 'enablewaf', 1)
		</div>

		<div class="akeeba-form-group">
			<label for="ipworkarounds"
				   rel="akeeba-sticky-tooltip"
				   data-original-title="@lang('COM_ADMINTOOLS_CONFIGUREWAF_OPT_IPWORKAROUNDS')"
				   data-content="@lang('COM_ADMINTOOLS_CONFIGUREWAF_IPWORKAROUNDS_TIP')"
			>
				@lang('COM_ADMINTOOLS_CONFIGUREWAF_OPT_IPWORKAROUNDS')
			</label>

			@jhtml('FEFHelp.select.booleanswitch', 'ipworkarounds', 1)
		</div>

		<div class="akeeba-form-group">
			<label for="autoban"
				   rel="akeeba-sticky-tooltip"
				   data-original-title="@lang('COM_ADMINTOOLS_QUICKSTART_AUTOBAN_LBL')"
				   data-content="@lang('COM_ADMINTOOLS_QUICKSTART_AUTOBAN_DESC')"
			>
				@lang('COM_ADMINTOOLS_QUICKSTART_AUTOBAN_LBL')
			</label>

			@jhtml('FEFHelp.select.booleanswitch', 'autoban', 1)
		</div>

		<div class="akeeba-form-group">
			<label for="autoblacklist"
				   rel="akeeba-sticky-tooltip"
				   data-original-title="@lang('COM_ADMINTOOLS_QUICKSTART_AUTOBLACKLIST_LBL')"
				   data-content="@lang('COM_ADMINTOOLS_QUICKSTART_AUTOBLACKLIST_DESC')"
			>
				@lang('COM_ADMINTOOLS_QUICKSTART_AUTOBLACKLIST_LBL')
			</label>

			@jhtml('FEFHelp.select.booleanswitch', 'autoblacklist', 1)
		</div>

		<div class="akeeba-form-group">
			<label for="emailbreaches"
				   rel="akeeba-sticky-tooltip"
				   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILBREACHES')"
				   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILBREACHES_TIP')">
				@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILBREACHES')
			</label>

			<input type="text" size="20" name="emailbreaches"
				   value="{{{ $this->wafconfig['emailbreaches'] }}}">
		</div>

		<div class="akeeba-form-group">
			<label for="tmpl"
				   rel="akeeba-sticky-tooltip"
				   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ALLOWED_DOMAINS')"
				   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ALLOWED_DOMAINS_TIP')">
				@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_ALLOWED_DOMAINS')
			</label>

			<input type="text" size="45" name="allowed_domains" id="allowed_domains"
				   value="{{{ $this->wafconfig['allowed_domains'] }}}" />
		</div>
	</div>

	<div class="akeeba-panel--primary">
		<header class="akeeba-block-header">
			<h3>@lang('COM_ADMINTOOLS_QUICKSTART_HEAD_ADVANCEDSECURITY')</h3>
		</header>

		<div class="akeeba-form-group">
			<label for="bbhttpblkey"
				   rel="akeeba-sticky-tooltip"
				   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_BBHTTPBLKEY')"
				   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_BBHTTPBLKEY_TIP')">
				@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_BBHTTPBLKEY')
			</label>

			<input type="text" size="45" name="bbhttpblkey"
				   value="{{{ $this->wafconfig['bbhttpblkey'] }}}" />
		</div>

		@if($this->hasHtaccess)
			<div class="akeeba-form-group">
				<label for="htmaker"
					   rel="akeeba-sticky-tooltip"
					   data-original-title="@lang('COM_ADMINTOOLS_QUICKSTART_HTMAKER_LBL')"
					   data-content="@lang('COM_ADMINTOOLS_QUICKSTART_HTMAKER_DESC')"
				>
					@lang('COM_ADMINTOOLS_QUICKSTART_HTMAKER_LBL')
				</label>

				@jhtml('FEFHelp.select.booleanswitch', 'htmaker', 1)
			</div>
		@endif
	</div>

	<div class="akeeba-panel--primary">
		<header class="akeeba-block-header">
			<h3>@lang('COM_ADMINTOOLS_QUICKSTART_HEAD_ALMOSTTHERE')</h3>
		</header>

		<p>
			@lang('COM_ADMINTOOLS_QUICKSTART_ALMOSTTHERE_INTRO')
		</p>
		<ul>
			<li>
				<a href="http://akee.ba/lockedout">http://akee.ba/lockedout</a>
			</li>
			<li>
				<a href="http://akee.ba/500htaccess">http://akee.ba/500htaccess</a>
			</li>
			<li>
				<a href="http://akee.ba/adminpassword">http://akee.ba/adminpassword</a>
			</li>
			<li>
				<a href="http://akee.ba/lockedout">http://akee.ba/403edituser</a>
			</li>
		</ul>
		<p>
			@lang('COM_ADMINTOOLS_QUICKSTART_ALMOSTTHERE_OUTRO')
		</p>
	</div>

	<div class="akeeba-block--failure" style="{{{ $warningStyle }}}">
		<h1>
			@lang('COM_ADMINTOOLS_QUICKSTART_ALREADYCONFIGURED_NOSUPPORT_HEAD')
		</h1>
		<p>
			@lang('COM_ADMINTOOLS_QUICKSTART_ALREADYCONFIGURED_NOSUPPORT_BODY')
		</p>
	</div>

	<div class="form-actions" style="{{{ $formStyle }}}">
		<button type="submit" class="akeeba-btn--primary">
			@lang('JSAVE')
		</button>
	</div>

	<div style="{{{ $warningStyle }}}">
		<button type="submit" class="akeeba-btn--red">
			<span class="akion-alert-circled"></span>
			@lang('JSAVE')
		</button>

		<a href="index.php?option=com_admintools"
		   class="akeeba-btn--green--large">
			<span class="akion-ios-home"></span>
			<strong>
				@lang('COM_ADMINTOOLS_QUICKSTART_ALREADYCONFIGURED_BTN_NO')
			</strong>
		</a>
	</div>

	<input type="hidden" name="option" value="com_admintools" />
	<input type="hidden" name="view" value="QuickStart" />
	<input type="hidden" name="task" value="commit" />
	<input type="hidden" name="@token(true)" value="1" />
	<input type="hidden" name="detectedip" id="detectedip" value="" />
</form>
