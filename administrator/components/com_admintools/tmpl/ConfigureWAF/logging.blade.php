<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var Akeeba\AdminTools\Admin\View\ConfigureWAF\Html $this */
?>
<div class="akeeba-form-group">
	<label
			for="emailphpexceptions"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILPHPEXCEPTIONS')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILPHPEXCEPTIONS_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILPHPEXCEPTIONS')
	</label>

	<input type="text" size="20" name="emailphpexceptions" id="emailphpexceptions"
		   value="{{{ $this->wafconfig['emailphpexceptions'] }}}">
</div>

<div class="akeeba-form-group">
	<label
			for="saveusersignupip"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_SAVEUSERSIGNUPIP')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_SAVEUSERSIGNUPIP_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_SAVEUSERSIGNUPIP')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'saveusersignupip', $this->wafconfig['saveusersignupip'])
</div>

<div class="akeeba-form-group">
	<label for="logbreaches"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LOGBREACHES')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LOGBREACHES_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_LOGBREACHES')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'logbreaches', $this->wafconfig['logbreaches'])
</div>

<div class="akeeba-form-group">
	<label for="logfile"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_CONFIGUREWAF_OPT_LOGFILE')"
		   data-content="@lang('COM_ADMINTOOLS_CONFIGUREWAF_OPT_LOGFILE_TIP')"
	>
		@lang('COM_ADMINTOOLS_CONFIGUREWAF_OPT_LOGFILE')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'logfile', $this->wafconfig['logfile'])
</div>

<div class="akeeba-form-group">
	<label for="iplookup"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_IPLOOKUP_LABEL')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_IPLOOKUP_DESC')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_IPLOOKUP_LABEL')
	</label>

	<div>
		{{ \Akeeba\AdminTools\Admin\Helper\Select::httpschemes('iplookupscheme', ['class' => 'input-small'], $this->wafconfig['iplookupscheme']) }}

		<input type="text" size="50" name="iplookup" value="{{{ $this->wafconfig['iplookup'] }}}"
			   title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_IPLOOKUP_DESC')" />
	</div>
</div>

<div class="akeeba-form-group">
	<label for="emailbreaches"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILBREACHES')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILBREACHES_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILBREACHES')
	</label>

	<input type="text" size="20" name="emailbreaches" id="emailbreaches"
		   value="{{{ $this->wafconfig['emailbreaches'] }}}">
</div>

<div class="akeeba-form-group">
	<label for="emailonadminlogin"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILADMINLOGIN')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILADMINLOGIN_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILADMINLOGIN')
	</label>

	<input type="text" size="20" name="emailonadminlogin" id="emailonadminlogin"
		   value="{{{ $this->wafconfig['emailonadminlogin'] }}}">
</div>

<div class="akeeba-form-group">
	<label for="emailonfailedadminlogin"
		   rel="akeeba-sticky-tooltip"
		   data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILADMINFAILEDLOGIN')"
		   data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILADMINFAILEDLOGIN_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILADMINFAILEDLOGIN')
	</label>

	<input type="text" size="20" name="emailonfailedadminlogin" id="emailonfailedadminlogin"
		   value="{{{ $this->wafconfig['emailonfailedadminlogin'] }}}">
</div>

<div class="akeeba-form-group">
	<label
			for="reasons_nolog"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_REASONS_NOLOG')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_REASONS_NOLOG_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_REASONS_NOLOG')
	</label>

	{{ \Akeeba\AdminTools\Admin\Helper\Select::reasons('reasons_nolog[]', $this->wafconfig['reasons_nolog'], [
			'class'     => 'advancedSelect input-large',
			'multiple'  => 'multiple',
			'size'      => 5,
			'hideEmpty' => true,
		]) }}
</div>

<div class="akeeba-form-group">
	<label
			for="reasons_noemail"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_REASONS_NOEMAIL')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_REASONS_NOEMAIL_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_REASONS_NOEMAIL')
	</label>

	{{ \Akeeba\AdminTools\Admin\Helper\Select::reasons('reasons_noemail[]', $this->wafconfig['reasons_noemail'], [
			'class'     => 'advancedSelect input-large',
			'multiple'  => 'multiple',
			'size'      => 5,
			'hideEmpty' => true,
		]) }}
</div>

<div class="akeeba-form-group">
	<label
			for="email_throttle"
			rel="akeeba-sticky-tooltip"
			data-original-title="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILTHROTTLE')"
			data-content="@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILTHROTTLE_TIP')">
		@lang('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_EMAILTHROTTLE')
	</label>

	@jhtml('FEFHelp.select.booleanswitch', 'email_throttle', $this->wafconfig['email_throttle'])
</div>
